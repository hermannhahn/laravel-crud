<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
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
    public function index(): Response
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $tasks = Task::with(['company', 'professional'])->latest()->paginate(10);
        } elseif ($user->isCompany()) {
            $tasks = $user->tasks()->with('professional')->latest()->paginate(10);
        } else {
            // Professional: only see assigned tasks OR unassigned tasks from linked companies IN THEIR AREA
            $links = $user->companies()->get();
            
            $tasks = Task::where(function($query) use ($user, $links) {
                // Already assigned to me
                $query->where('professional_id', $user->id)
                      // OR Unassigned but in my area for that company
                      ->orWhere(function($q) use ($links) {
                          foreach ($links as $link) {
                              $q->orWhere(function($inner) use ($link) {
                                  $inner->where('company_id', $link->id)
                                        ->whereNull('professional_id')
                                        ->where('task_area_id', $link->pivot->task_area_id);
                              });
                          }
                      });
            })->with(['company', 'area'])->latest()->paginate(10);
        }

        return Inertia::render('Tasks/Index', [
            'tasks' => TaskResource::collection($tasks),
            'can' => [
                'create' => $user->isCompany() || $user->isAdmin(),
                'manage_team' => $user->isCompany(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response|RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isCompany() && !$user->isAdmin()) {
            abort(403, 'Only companies can create tasks.');
        }

        if ($user->hasReachedMonthlyLimit('tasks')) {
            return redirect()->route('tasks.index')
                ->with('error', 'Monthly task limit reached.');
        }

        // Get professionals linked to this company
        $professionals = $user->isCompany() 
            ? $user->professionals()->get(['users.id', 'name']) 
            : User::where('user_type', 'professional')->get(['id', 'name']);

        $areas = $user->isCompany()
            ? $user->taskAreas()->get(['id', 'name'])
            : [];

        return Inertia::render('Tasks/Create', [
            'professionals' => $professionals,
            'areas' => $areas,
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
        $validated['company_id'] = $user->id; // Owner is the company

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
    public function edit(Task $task): Response
    {
        $user = Auth::user();

        if (!$user->isAdmin() && $task->company_id !== $user->id) {
            abort(403, 'Only the company owner can edit this task.');
        }

        $professionals = $user->isCompany() 
            ? $user->professionals()->get(['users.id', 'name']) 
            : User::where('user_type', 'professional')->get(['id', 'name']);

        $areas = $user->isCompany()
            ? $user->taskAreas()->get(['id', 'name'])
            : [];

        return Inertia::render('Tasks/Edit', [
            'task' => new TaskResource($task),
            'professionals' => $professionals,
            'areas' => $areas,
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

        $task->update($request->validated());

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
