<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PartNumber extends Model
{
    protected $fillable = [
        'number',
        'name',
        'production_rate',
        'efficiency',
        'item_class_id',
        'work_center_id',
        'is_obsolete',
    ];

    /**
     *
     */
    public function itemClass(): BelongsTo
    {
        return $this->belongsTo(ItemClass::class);
    }

    /**
     *
     */
    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class, 'work_center_id');
    }

    /**
     *
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'part_number_project', 'part_number_id', 'project_id');
    }

    public function nextProcesses()
    {
        return $this->belongsToMany(
            PartNumber::class,
            'part_number_sequences',
            'current_part_number_id',
            'next_part_number_id'
        )
            ->using(PartNumberSequence::class)
            ->withPivot('sequence_order', 'lead_time_hours', 'is_active')
            ->wherePivot('is_active', true)
            ->orderBy('pivot_sequence_order');
    }

    public function previousProcesses()
    {
        return $this->belongsToMany(
            PartNumber::class,
            'part_number_sequences',
            'next_part_number_id',
            'current_part_number_id'
        )
            ->using(PartNumberSequence::class)
            ->withPivot('sequence_order', 'lead_time_hours', 'is_active')
            ->wherePivot('is_active', true);
    }
}
