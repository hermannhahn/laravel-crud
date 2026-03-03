<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskArea;
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
        $query = Task::query();

        // Admin sees everything
        if ($user->isAdmin()) {
            $query->with(['company', 'professional', 'area']);
        } elseif ($user->isCompany()) {
            $query->where('company_id', $user->id)->with(['professional', 'area']);
        } else {
            // Professional: see assigned tasks OR unassigned tasks from linked companies IN THEIR AUTHORIZED AREAS
            $userAreas = $user->companyAreas()->get();
            
            $query->where(function($q) use ($user, $userAreas) {
                // Already assigned to me
                $q->where('professional_id', $user->id)
                      // OR Unassigned but matches an authorized area for that specific company
                      ->orWhere(function($sub) use ($userAreas) {
                          foreach ($userAreas as $area) {
                              $sub->orWhere(function($inner) use ($area) {
                                  $inner->where('company_id', $area->pivot->company_id)
                                        ->where('task_area_id', $area->id)
                                        ->whereNull('professional_id');
                              });
                          }
                      });
            })->with(['company', 'area']);
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

        if ($request->filled('area_id')) {
            $query->where('task_area_id', $request->area_id);
        }

        // --- SORTING ---
        $sort = $request->input('sort', 'latest');
        match($sort) {
            'due_date' => $query->orderByRaw('due_date IS NULL, due_date ASC'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        $tasks = $query->paginate(10)->withQueryString();

        // Load areas for the filter dropdown (relevant to the current user context)
        $filterAreas = [];
        if ($user->isCompany()) {
            $filterAreas = $user->taskAreas()->get(['id', 'name']);
        } elseif ($user->isProfessional()) {
            $filterAreas = $user->companyAreas()->get(['task_areas.id', 'task_areas.name'])->unique('id');
        } elseif ($user->isAdmin()) {
            $filterAreas = TaskArea::all(['id', 'name']);
        }

        return Inertia::render('Tasks/Index', [
            'tasks' => TaskResource::collection($tasks),
            'filters' => $request->only(['search', 'status', 'area_id', 'sort']),
            'availableAreas' => $filterAreas,
            'can' => [
                'create' => $user->isCompany() || $user->isAdmin(),
                'manage_team' => $user->isCompany(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isCompany() && !$user->isAdmin()) {
            abort(403, 'Only companies can create tasks.');
        }

        if ($user->hasReachedMonthlyLimit('tasks')) {
            return redirect()->route('tasks.index')
                ->with('error', 'Monthly task limit reached.');
        }

        // If admin, they can select a company. If company, it's themselves.
        $targetCompanyId = $user->isAdmin() ? $request->company_id : $user->id;

        // Get professionals linked to the target company
        $professionals = [];
        $areas = [];

        if ($targetCompanyId) {
            $targetCompany = User::find($targetCompanyId);
            if ($targetCompany) {
                $professionals = $targetCompany->professionals()->get(['users.id', 'name']);
                $areas = $targetCompany->taskAreas()->get(['id', 'name']);
            }
        } elseif (!$user->isAdmin()) {
            // This case shouldn't happen for companies but for safety:
            $professionals = $user->professionals()->get(['users.id', 'name']);
            $areas = $user->taskAreas()->get(['id', 'name']);
        }

        $companies = $user->isAdmin()
            ? User::where('user_type', 'company')->get(['id', 'name'])
            : [];

        return Inertia::render('Tasks/Create', [
            'professionals' => $professionals,
            'areas' => $areas,
            'companies' => $companies,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isCompany() && !$user->isAdmin()) {
            abort(403, 'Only companies can create tasks.');
        }

        if ($user->hasReachedMonthlyLimit('tasks')) {
            return redirect()->route('tasks.index')
                ->with('error', 'Monthly task limit reached.');
        }

        $validated = $request->validated();
        
        if ($user->isAdmin()) {
            $validated['company_id'] = $request->company_id ?? $user->id;
            $validated['task_area_id'] = $request->task_area_id;
        } else {
            $validated['company_id'] = $user->id; // Owner is the company
        }

        Task::create($validated);

        return redirect()->route('tasks.index')
            ->with('message', 'Task created successfully and assigned.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): Response
    {
        $user = Auth::user();
        
        // Authorization: Professional assigned to task, or Company owner, or Admin
        // OR a linked professional if task is unassigned
        $isLinkedProfessional = $user->isProfessional() && 
                               !$task->professional_id && 
                               $user->companies()->where('company_id', $task->company_id)->exists();

        if (!$user->isAdmin() && $task->company_id !== $user->id && $task->professional_id !== $user->id && !$isLinkedProfessional) {
            abort(403);
        }

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

        // If admin reloads with a different company_id, use that. Otherwise use task's company.
        $targetCompanyId = ($user->isAdmin() && $request->company_id) ? $request->company_id : $task->company_id;
        $targetCompany = User::find($targetCompanyId);

        $professionals = [];
        $areas = [];

        if ($targetCompany) {
            $professionals = $targetCompany->professionals()->get(['users.id', 'name']);
            $areas = $targetCompany->taskAreas()->get(['id', 'name']);
        }

        $companies = $user->isAdmin()
            ? User::where('user_type', 'company')->get(['id', 'name'])
            : [];

        return Inertia::render('Tasks/Edit', [
            'task' => new TaskResource($task),
            'professionals' => $professionals,
            'areas' => $areas,
            'companies' => $companies,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && $task->company_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validated();
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

        return redirect()->route('tasks.index')
            ->with('message', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAdmin() && $task->company_id !== $user->id) {
            abort(403);
        }

        $task->delete();

        return redirect()->route('tasks.index')
            ->with('message', 'Task deleted successfully.');
    }

    public function respond(Request $request, Task $task): RedirectResponse
    {
        $user = $request->user();

        // Only assigned professional or admin can respond
        if (!$user->isAdmin() && $task->professional_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $task->responses()->create([
            'user_id' => $user->id,
            'message' => $validated['message'],
        ]);

        return redirect()->back()->with('message', 'Response sent successfully.');
    }

    public function accept(Task $task): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isProfessional()) {
            abort(403, 'Only professionals can accept tasks.');
        }

        if ($task->professional_id) {
            return redirect()->back()->with('error', 'Task is already assigned to someone else.');
        }

        // Check if linked to company
        if (!$user->companies()->where('company_id', $task->company_id)->exists()) {
            abort(403, 'You are not linked to this company.');
        }

        $task->update(['professional_id' => $user->id]);

        return redirect()->back()->with('message', 'Task accepted successfully.');
    }

    public function release(Task $task): RedirectResponse
    {
        $user = Auth::user();

        if ($task->professional_id !== $user->id) {
            abort(403, 'You can only release tasks assigned to you.');
        }

        $task->update(['professional_id' => null]);

        return redirect()->back()->with('message', 'Task released and returned to the pool.');
    }

    public function unassign(Task $task): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAdmin() && $task->company_id !== $user->id) {
            abort(403, 'Only the company owner can unassign professionals.');
        }

        $task->update(['professional_id' => null]);

        return redirect()->back()->with('message', 'Professional unassigned from task.');
    }
}
