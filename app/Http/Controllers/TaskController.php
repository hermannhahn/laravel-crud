<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Models\Profession;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $query = Task::with(['company:id,name', 'professional:id,name', 'profession:id,name', 'service:id,title,payout']);

        // --- SCOPING BY ROLE ---
        if ($user->isAdmin()) {
            // Admin sees everything
        } elseif ($user->isCompany()) {
            $query->where('company_id', $user->id);
        } elseif ($user->isProfessional()) {
            // Marketplace logic: see unassigned tasks in authorized professions OR assigned to self
            $query->where(function($q) use ($user) {
                $q->where('professional_id', $user->id)
                  ->orWhere(function($sub) use ($user) {
                      $sub->whereNull('professional_id')
                          ->whereIn('profession_id', $user->companyProfessions()->pluck('profession_id'));
                  });
            });
        }

        // --- FILTERS ---
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('professional', function($sub) use ($request) {
                      $sub->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('company', function($sub) use ($request) {
                      $sub->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('profession_id')) {
            $query->where('profession_id', $request->profession_id);
        }

        // --- SORTING ---
        $sort = $request->input('sort', 'latest');
        match($sort) {
            'oldest' => $query->oldest(),
            'due_date' => $query->orderBy('due_date', 'asc'),
            default => $query->latest(),
        };

        $tasks = $query->paginate(10)->withQueryString();

        // Get professions for filter dropdown (relevant to the current user)
        if ($user->isAdmin()) {
            $filterProfessions = Profession::all(['id', 'name']);
        } elseif ($user->isCompany()) {
            $filterProfessions = $user->professions()->get(['id', 'name']);
        } else {
            $filterProfessions = Profession::whereIn('id', $user->companyProfessions()->pluck('profession_id'))->get(['id', 'name']);
        }

        return Inertia::render('Tasks/Index', [
            'tasks' => TaskResource::collection($tasks),
            'filters' => $request->only(['search', 'status', 'profession_id', 'sort']),
            'availableProfessions' => $filterProfessions,
            'can' => [
                'create' => $user->can('create', Task::class),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isCompany()) {
            abort(403, 'Only companies can create tasks.');
        }

        $targetCompanyId = $user->isAdmin() ? $request->company_id : $user->id;

        $professions = [];
        $professionals = [];
        if ($targetCompanyId) {
            $company = User::findOrFail($targetCompanyId);
            $professions = $company->professions()->get(['professions.id', 'professions.name']);
            $professionals = $company->professionals()->get(['users.id', 'users.name']);
        }

        $companies = $user->isAdmin()
            ? User::where('user_type', 'company')->get(['id', 'name'])
            : [];

        $services = [];
        if ($targetCompanyId) {
            $services = Service::where('company_id', $targetCompanyId)->get(['id', 'title', 'profession_id', 'payout']);
        }

        return Inertia::render('Tasks/Create', [
            'professionals' => $professionals,
            'professions' => $professions,
            'services' => $services,
            'companies' => $companies,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $user = Auth::user();
        
        $validated = $request->validated();
        
        // Auto-populate title and payout from service
        if (!empty($validated['service_id'])) {
            $service = Service::find($validated['service_id']);
            $validated['title'] = $service?->title;
            $validated['payout'] = $service?->payout;
        }

        if ($user->isAdmin()) {
            $validated['company_id'] = $request->company_id ?? $user->id;
            $validated['profession_id'] = $request->profession_id;
        } else {
            $validated['company_id'] = $user->id; // Owner is the company
            
            // Limit check
            if ($user->hasReachedLimit('tasks')) {
                return redirect()->back()
                    ->with('error', 'Monthly task limit reached.');
            }
        }

        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        $validated['user_id'] = $user->id; // Creator

        Task::create($validated);

        return redirect()->route('tasks.index')->with('message', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): Response
    {
        $user = Auth::user();
        
        // Authorization logic...
        $isLinkedProfessional = $user->isProfessional() && 
                               !$task->professional_id && 
                               $user->companies()->where('company_id', $task->company_id)->exists();

        if (!$user->isAdmin() && $task->company_id !== $user->id && $task->professional_id !== $user->id && !$isLinkedProfessional) {
            abort(403);
        }

        $task->load(['company', 'professional', 'profession', 'service']);

        return Inertia::render('Tasks/Show', [
            'task' => array_merge((new TaskResource($task))->resolve(), [
                'responses' => $task->responses()->with('user:id,name,avatar_path')->latest()->get(),
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Task $task): Response
    {
        $user = Auth::user();

        if (!$user->isAdmin() && $task->company_id !== $user->id) {
            abort(403, 'Only the company owner can edit this task.');
        }

        $targetCompanyId = $user->isAdmin() ? ($request->company_id ?? $task->company_id) : $task->company_id;
        $company = User::findOrFail($targetCompanyId);

        $professions = $company->professions()->get(['professions.id', 'professions.name']);
        $professionals = $company->professionals()->get(['users.id', 'users.name']);

        $companies = $user->isAdmin()
            ? User::where('user_type', 'company')->get(['id', 'name'])
            : [];

        $services = Service::where('company_id', $targetCompanyId)->get(['id', 'title', 'profession_id', 'payout']);

        return Inertia::render('Tasks/Edit', [
            'task' => new TaskResource($task),
            'professionals' => $professionals,
            'professions' => $professions,
            'services' => $services,
            'companies' => $companies,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAdmin() && $task->company_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validated();

        // Security: If task is completed, only admin can change status or edit details
        if ($task->status === 'completed' && !$user->isAdmin()) {
            return redirect()->back()->with('error', 'Completed tasks cannot be modified by users. Please contact an Administrator.');
        }

        // Auto-populate title and payout from service
        if (!empty($validated['service_id'])) {
            $service = Service::find($validated['service_id']);
            $validated['title'] = $service?->title;
            
            // Only update payout if not set or service changed
            if (!$task->payout || $task->service_id != $validated['service_id']) {
                $validated['payout'] = $service?->payout;
            }
        }

        if ($user->isAdmin() && $request->has('company_id')) {
            $validated['company_id'] = $request->company_id;
        }

        if (isset($validated['status'])) {
            if ($validated['status'] === 'completed' && $task->status !== 'completed') {
                $validated['completed_at'] = now();
            } elseif ($validated['status'] !== 'completed') {
                $validated['completed_at'] = null;
            }
        }

        $task->update($validated);

        return redirect()->route('tasks.index')->with('message', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $user = Auth::user();
        
        if ($user->cannot('delete', $task)) {
            abort(403, 'You do not have permission to delete this task. Companies cannot delete accepted or completed tasks.');
        }

        $task->delete();

        return redirect()->route('tasks.index')->with('message', 'Task deleted successfully.');
    }

    /**
     * Professional accepts a task.
     */
    public function accept(Task $task): RedirectResponse
    {
        $user = Auth::user();
        
        if (!$user->isProfessional()) {
            abort(403, 'Only professionals can accept tasks.');
        }

        // Check if professional is authorized for this company and profession
        $isAuthorized = $user->companyProfessions()
            ->where('company_professional_profession.company_id', $task->company_id)
            ->where('company_professional_profession.profession_id', $task->profession_id)
            ->exists();

        if (!$isAuthorized) {
            abort(403, 'You are not authorized for this company and profession.');
        }

        if ($task->professional_id) {
            abort(400, 'Task already assigned to a professional.');
        }

        $task->update([
            'professional_id' => $user->id,
            'status' => 'in_progress'
        ]);

        return redirect()->back()->with('message', 'Task accepted successfully.');
    }

    /**
     * Professional releases a task back to the marketplace.
     */
    public function release(Task $task): RedirectResponse
    {
        $user = Auth::user();
        
        if ($task->professional_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'You are not assigned to this task.');
        }

        $task->update([
            'professional_id' => null,
            'status' => 'pending'
        ]);

        return redirect()->back()->with('message', 'Task released back to marketplace.');
    }

    /**
     * Professional posts a response/update to a task.
     */
    public function respond(Request $request, Task $task): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        // Authorization...
        if (!$user->isAdmin() && $task->company_id !== $user->id && $task->professional_id !== $user->id) {
            abort(403);
        }

        $task->responses()->create([
            'user_id' => $user->id,
            'message' => $validated['message'],
        ]);

        return redirect()->back()->with('message', 'Update posted.');
    }

    /**
     * Company owner unassigns a professional.
     */
    public function unassign(Task $task): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $task->company_id !== $user->id) {
           abort(403, 'Only the company owner can unassign professionals.');
        }

        $task->update(['professional_id' => null, 'status' => 'pending']);

        return redirect()->back()->with('message', 'Professional unassigned from task.');
    }
}
