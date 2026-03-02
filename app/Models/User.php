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
        ];
    }

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function permissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Permission::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasModulePermission(string $module, string $action): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

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
