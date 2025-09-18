<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScrapReason extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'scrap_category_id'
    ];

    /**
     * Get the category that owns the scrap reason
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ScrapCategory::class, 'scrap_category_id');
    }

    /**
     * Get all scrap records for this reason
     */
    public function scrapRecords(): HasMany
    {
        return $this->hasMany(ScrapRecord::class, 'scrap_reason_id');
    }
}
