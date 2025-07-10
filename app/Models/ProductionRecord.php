<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRecord extends Model
{
    protected $fillable = [
        'part_number_id',
        'production_plan_id',
        'quantity',
        'sequence',
        'status_id',
        'user_id',
    ];

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class, 'production_plan_id');
    }

    public function partNumber(): BelongsTo
    {
        return $this->belongsTo(PartNumber::class, 'part_number_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
