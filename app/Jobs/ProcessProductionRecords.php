<?php

namespace App\Jobs;

use App\Models\ProductionPlan;
use App\Models\Status;
use App\Models\YF013;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProductionSyncSummary;

class ProcessProductionRecords implements ShouldQueue
{
    use Queueable;

    protected array $productionPlanIds;

    /**
     * Create a new job instance.
     */
    public function __construct(array $productionPlanIds)
    {
        $this->productionPlanIds = $productionPlanIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $syncedDataForReport = [];

        $productionPlans = ProductionPlan::with(['partNumber.workCenter', 'shift'])
            ->whereIn('id', $this->productionPlanIds)
            ->get();

        foreach ($productionPlans as $productionPlan) {
            try {
                $shouldSync = false;
                if ($productionPlan->produced_quantity <= 0) {
                    $shouldSync = true;
                } else {
                    $infor = YF013::sendToInfor($productionPlan, null);
                    if ($infor) $shouldSync = true;
                }

                if ($shouldSync) {
                    $status = Status::where('key', 'completed')->first();
                    $productionPlan->update([
                        'synced_to_infor' => true,
                        'synced_at' => now(),
                        'status_id' => $status->id ?? null
                    ]);

                    $syncedDataForReport[] = [
                        'order_number' => $productionPlan->shop_order_number ?? '',
                        'work_center'  => $productionPlan->partNumber->workCenter->name ?? 'N/A',
                        'part_number'  => $productionPlan->partNumber->number ?? 'N/A',
                        'date'         => $productionPlan->planned_date ?? '',
                        'shift'        => $productionPlan->shift->abbreviation ?? 'N/A',
                        'planned_qty'  => $productionPlan->planned_quantity ?? 0,
                        'produced_qty' => $productionPlan->produced_quantity ?? 0,
                    ];

                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Error al sincronizar orden: {$productionPlan->shop_order_number}";
                }
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Error con orden {$productionPlan->shop_order_number}: " . $e->getMessage();
                Log::error("Error procesando plan {$productionPlan->id}: " . $e->getMessage());
            }
        }

        YF013::executeInforProcess();

        if (!empty($syncedDataForReport)) {
            Notification::route('mail', [
                'paint-notifications@ykm.com',
                'jesus.camacho@ykm.com.mx',
            ])->notify(new ProductionSyncSummary($syncedDataForReport));
        }

        Log::info("Sincronización terminada. Éxitos: $successCount, Errores: $errorCount");
    }
}
