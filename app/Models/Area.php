<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = [
        'name',
        'color',
        'description',
        'department_id'
    ];

    /**
     *
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     *
     */
    public function workCenters(): HasMany
    {
        return $this->hasMany(WorkCenter::class, 'area_id');
    }
}
