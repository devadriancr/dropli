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
        'is_active'
    ];
}
