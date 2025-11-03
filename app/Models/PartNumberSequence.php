<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PartNumberSequence extends Pivot
{
    protected $table = 'part_number_sequences';

    protected $fillable = [
        'current_part_number_id',
        'next_part_number_id',
        'sequence_order',
        'lead_time_hours',
        'is_active',
        'last_synced_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
        'lead_time_hours' => 'integer',
    ];
}
