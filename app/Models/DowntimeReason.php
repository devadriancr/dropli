<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DowntimeReason extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'downtime_type_id'
    ];

    public function downtimeType(): BelongsTo
    {
        return $this->belongsTo(DowntimeType::class, 'downtime_type_id');
    }

    public function downtimeRecords(): HasMany
    {
        return $this->hasMany(DowntimeRecord::class, 'downtime_reason_id');
    }
}
