<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapRecord extends Model
{
    protected $fillable = [
        'part_number_id',
        'scrap_reason_id',
        'quantity'
    ];

    /**
     * Get the part number that owns the scrap record
     */
    public function partNumber(): BelongsTo
    {
        return $this->belongsTo(PartNumber::class, 'part_number_id');
    }

    /**
     * Get the scrap reason that owns the scrap record
     */
    public function scrapReason(): BelongsTo
    {
        return $this->belongsTo(ScrapReason::class, 'scrap_reason_id');
    }
}
