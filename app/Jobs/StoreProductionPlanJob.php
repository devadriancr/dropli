<?php

namespace App\Jobs;

use App\Models\PartNumber;
use App\Models\ProductionPlan;
use App\Models\Shift;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class
StoreProductionPlanJob implements ShouldQueue
{
    use Queueable;

    protected $shop_order_number;
    protected $part_number;
    protected $planned_quantity;
    protected $planned_date;
    protected $planned_shift;

    /**
     * Create a new job instance.
     */
    public function __construct($shop_order_number, $part_number, $planned_quantity, $planned_date, $planned_shift)
    {
        $this->shop_order_number = $shop_order_number;
        $this->part_number = $part_number;
        $this->planned_quantity = $planned_quantity;
        $this->planned_date = $planned_date;
        $this->planned_shift = $planned_shift;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $partNumber = PartNumber::query()->where('number', $this->part_number)->first();

        if (!$partNumber) {
            Log::error("StoreProductionPlanJob: Part number not found: " . $this->part_number);
            return;
        }

        $shift = Shift::query()->where('abbreviation', $this->planned_shift)->first();

        if (!$shift) {
            Log::error("StoreProductionPlanJob: Shift not found: " . $this->planned_shift);
            return;
        }

        $existingRecord = ProductionPlan::where([
            'shop_order_number' => $this->shop_order_number,
            'part_number_id' => $partNumber->id,
            'planned_date' => $this->planned_date,
            'shift_id' => $shift->id,
        ])->first();

        if ($existingRecord) {
            return;
        }

        ProductionPlan::store($this->shop_order_number, $partNumber->id, intval($this->planned_quantity), $this->planned_date, $shift->id);
    }
}
