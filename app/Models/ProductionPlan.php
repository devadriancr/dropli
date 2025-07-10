<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionPlan extends Model
{
    protected $fillable = [
        'shop_order_number',
        'part_number_id',
        'planned_quantity',
        'produced_quantity',
        'scrap_quantity',
        'planned_date',
        'shift_id',
        'status_id',
        'synced_to_infor',
        'synced_at'
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function partNumber(): BelongsTo
    {
        return $this->belongsTo(PartNumber::class, 'part_number_id');
    }

    public function productionRecords(): HasMany
    {
        return $this->hasMany(ProductionRecord::class, 'production_plan_id');
    }

    public static function store(
        $shopOrderNumber = null,
        $partNumberId,
        $plannedQuantity,
        $plannedDate,
        $shiftId = null,
    ) {
        $status = Status::where('label', 'LIKE', 'Pendiente')->first();

        $productionPlan = ProductionPlan::query()->where([['part_number_id', $partNumberId], ['planned_quantity', $plannedQuantity], ['planned_date', $plannedDate], ['shift_id', $shiftId]])->first();

        if ($productionPlan === null) {
            return ProductionPlan::create([
                'shop_order_number' => $shopOrderNumber,
                'part_number_id' => $partNumberId,
                'planned_quantity' => $plannedQuantity,
                'planned_date' => $plannedDate,
                'shift_id' => $shiftId,
                'status_id' => $status->id,
                'synced_to_infor' => false,
            ]);
        }
    }
}
