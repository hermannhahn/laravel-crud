<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'avatar_path',
        'is_active',
        'user_type',
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

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'company_id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'professional_id');
    }

    public function professions(): HasMany
    {
        return $this->hasMany(Profession::class, 'company_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'company_id');
    }

    public function professionals(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_professional', 'company_id', 'professional_id')
            ->withPivot(['can_view_tasks', 'can_respond_tasks', 'can_edit_tasks'])
            ->withTimestamps();
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_professional', 'professional_id', 'company_id')
            ->withPivot(['can_view_tasks', 'can_respond_tasks', 'can_edit_tasks'])
            ->withTimestamps();
    }

    public function companyProfessions(): BelongsToMany
    {
        return $this->belongsToMany(Profession::class, 'company_professional_profession', 'professional_id', 'profession_id')
            ->withPivot('company_id')
            ->withTimestamps();
    }

    public function hasReachedLimit(string $module): bool
    {
        $permission = $this->permissions()->where('module', $module)->first();
        if (!$permission) return false;

        $limit = $permission->monthly_limit;
        if (is_null($limit)) {
          return false;
        }

        $count = match($module) {
            'tasks' => $this->tasks()->whereMonth('created_at', now()->month)->count(),
            default => 0,
        };

        return $count >= $limit;
    }
}
