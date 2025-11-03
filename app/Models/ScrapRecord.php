<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapRecord extends Model
{
    protected $fillable = [
        'part_number_id',
        'scrap_reason_id',
        'quantity',
        'production_plan_id',
        'synced_to_infor',
        'synced_at'
    ];

    protected $casts = [
        'synced_to_infor' => 'boolean',
        'synced_at' => 'datetime'
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

    /**
     * Get the production plan that owns the scrap record
     */
    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class, 'production_plan_id');
    }

    /**
     * Get unsynced scrap records for a part number
     */
    public static function getUnsyncedScrap($partNumberId)
    {
        return static::where('part_number_id', $partNumberId)
            ->where('synced_to_infor', false)
            ->whereNull('synced_at')
            ->get();
    }

    /**
     * Mark scrap records as synced
     */
    public static function markAsSynced($scrapRecords)
    {
        if ($scrapRecords->isNotEmpty()) {
            return static::whereIn('id', $scrapRecords->pluck('id'))
                ->update([
                    'synced_to_infor' => true,
                    'synced_at' => now()
                ]);
        }
        return false;
    }
}
