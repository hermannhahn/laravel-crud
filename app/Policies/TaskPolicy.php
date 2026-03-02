<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Admin, Company owner, or Assigned professional
        return $user->isAdmin() || 
               ($user->isCompany() && $task->company_id === $user->id) || 
               ($user->isProfessional() && $task->professional_id === $user->id) ||
               // Pool visibility: professional linked to company and area
               ($user->isProfessional() && !$task->professional_id && $user->companyAreas()->where('task_areas.id', $task->task_area_id)->wherePivot('company_id', $task->company_id)->exists());
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isCompany() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->isAdmin() || ($user->isCompany() && $task->company_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->isAdmin() || ($user->isCompany() && $task->company_id === $user->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->isAdmin() || ($user->isCompany() && $task->company_id === $user->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->isAdmin() || ($user->isCompany() && $task->company_id === $user->id);
    }
}
