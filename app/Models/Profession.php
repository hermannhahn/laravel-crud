<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profession extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'company_id'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'profession_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'profession_id');
    }
}
