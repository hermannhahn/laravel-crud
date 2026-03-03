<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'task_area_id',
        'title',
        'description',
        'price',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(TaskArea::class, 'task_area_id');
    }
}
