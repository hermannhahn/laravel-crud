<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'user_type',
        'avatar_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Tasks owned by this user (if company)
    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class, 'company_id');
    }

    // Tasks assigned to this user (if professional)
    public function assignedTasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class, 'professional_id');
    }

    // Areas defined by this company
    public function taskAreas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaskArea::class, 'company_id');
    }

    // Link between Companies and Professionals
    public function professionals(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_professional', 'company_id', 'professional_id')
            ->withPivot(['can_view_tasks', 'can_respond_tasks', 'can_edit_tasks'])
            ->withTimestamps();
    }

    public function companies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_professional', 'professional_id', 'company_id')
            ->withPivot(['can_view_tasks', 'can_respond_tasks', 'can_edit_tasks'])
            ->withTimestamps();
    }

    // New: Link to areas in the context of company-professional partnership
    public function companyAreas(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TaskArea::class, 'company_professional_area', 'professional_id', 'task_area_id')
            ->withPivot('company_id')
            ->withTimestamps();
    }

    public function permissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Permission::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCompany(): bool
    {
        return $this->user_type === 'company';
    }

    public function isProfessional(): bool
    {
        return $this->user_type === 'professional';
    }

    public function hasModulePermission(string $module, string $action): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Global admin-defined permissions
        $permission = $this->permissions()->where('module', $module)->first();

        if (!$permission) {
            return false;
        }

        return (bool) $permission->{"can_{$action}"};
    }

    public function getModuleMonthlyLimit(string $module): ?int
    {
        $permission = $this->permissions()->where('module', $module)->first();
        return $permission ? $permission->monthly_limit : null;
    }

    public function hasReachedMonthlyLimit(string $module): bool
    {
        $limit = $this->getModuleMonthlyLimit($module);
        
        if ($limit === null) {
            return false;
        }

        $count = match($module) {
            'tasks' => $this->tasks()->whereMonth('created_at', now()->month)->count(),
            default => 0,
        };

        return $count >= $limit;
    }
}
