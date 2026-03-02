<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
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
        if (!Auth::user()->hasModulePermission('tasks', 'view')) {
            abort(403, 'You do not have permission to view tasks.');
        }

        $tasks = Auth::user()->tasks()
            ->latest()
            ->paginate(10)
            ->onEachSide(1);

        return Inertia::render('Tasks/Index', [
            'tasks' => TaskResource::collection($tasks),
            'can' => [
                'create' => Auth::user()->hasModulePermission('tasks', 'create'),
                'update' => Auth::user()->hasModulePermission('tasks', 'update'),
                'delete' => Auth::user()->hasModulePermission('tasks', 'delete'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response|RedirectResponse
    {
        if (!Auth::user()->hasModulePermission('tasks', 'create')) {
            abort(403, 'You do not have permission to create tasks.');
        }

        if (Auth::user()->hasReachedMonthlyLimit('tasks')) {
            return redirect()->route('tasks.index')
                ->with('error', 'Monthly task limit reached.');
        }

        return Inertia::render('Tasks/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        if (!$request->user()->hasModulePermission('tasks', 'create')) {
            abort(403, 'You do not have permission to create tasks.');
        }

        if ($request->user()->hasReachedMonthlyLimit('tasks')) {
            return redirect()->route('tasks.index')
                ->with('error', 'Monthly task limit reached.');
        }

        $request->user()->tasks()->create($request->validated());

        return redirect()->route('tasks.index')
            ->with('message', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): Response
    {
        if (!Auth::user()->hasModulePermission('tasks', 'view')) {
            abort(403, 'You do not have permission to view tasks.');
        }

        $this->authorize('view', $task);

        return Inertia::render('Tasks/Show', [
            'task' => new TaskResource($task),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task): Response
    {
        if (!Auth::user()->hasModulePermission('tasks', 'update')) {
            abort(403, 'You do not have permission to update tasks.');
        }

        $this->authorize('update', $task);

        return Inertia::render('Tasks/Edit', [
            'task' => new TaskResource($task),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        if (!$request->user()->hasModulePermission('tasks', 'update')) {
            abort(403, 'You do not have permission to update tasks.');
        }

        $this->authorize('update', $task);

        $task->update($request->validated());

        return redirect()->route('tasks.index')
            ->with('message', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): RedirectResponse
    {
        if (!Auth::user()->hasModulePermission('tasks', 'delete')) {
            abort(403, 'You do not have permission to delete tasks.');
        }

        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('tasks.index')
            ->with('message', 'Task deleted successfully.');
    }
}
