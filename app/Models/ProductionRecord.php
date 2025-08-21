<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRecord extends Model
{
    protected $fillable = [
        'production_plan_id',
        'order_number',
        'part_number_id',
        'sequence',
        'quantity',
        'record_type',
    ];

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class, 'production_plan_id');
    }

    public function partNumber(): BelongsTo
    {
        return $this->belongsTo(PartNumber::class, 'part_number_id');
    }

    public static function productionPlanExists(int $productionPlanId, string $orderNumber, int $partNumberId, string $sequence, int $quantity, string $recordType): bool
    {
        return ProductionRecord::query()
            ->where('production_plan_id', $productionPlanId)
            ->where('order_number', $orderNumber)
            ->where('part_number_id', $partNumberId)
            ->where('sequence', $sequence)
            ->where('quantity', $quantity)
            ->where('record_type', $recordType)
            ->exists();
    }

    public static function store(int $productionPlanId, string $orderNumber, int $partNumberId, string $sequence, int $quantity, string $recordType): void
    {
        ProductionRecord::create([
            'production_plan_id' => $productionPlanId,
            'order_number' => $orderNumber,
            'part_number_id' => $partNumberId,
            'sequence' => $sequence,
            'quantity' => $quantity,
            'record_type' => $recordType,
        ]);
    }
}
