<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserPermissionController extends Controller
{
    public function edit(User $user): Response
    {
        // For simplicity, we ensure 'tasks' module exists
        $tasksPermission = $user->permissions()->where('module', 'tasks')->first();
        
        if (!$tasksPermission) {
            $tasksPermission = [
                'module' => 'tasks',
                'can_view' => false,
                'can_create' => false,
                'can_update' => false,
                'can_delete' => false,
                'monthly_limit' => null,
            ];
        }

        return Inertia::render('Users/Permissions', [
            'user' => $user,
            'permissions' => [
                'tasks' => $tasksPermission,
            ],
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'module' => ['required', 'string', 'in:tasks'],
            'can_view' => ['required', 'boolean'],
            'can_create' => ['required', 'boolean'],
            'can_update' => ['required', 'boolean'],
            'can_delete' => ['required', 'boolean'],
            'monthly_limit' => ['nullable', 'integer', 'min:0'],
        ]);

        $user->permissions()->updateOrCreate(
            ['module' => $validated['module']],
            $validated
        );

        return redirect()->route('users.index')->with('message', 'Permissions updated successfully.');
    }
}
