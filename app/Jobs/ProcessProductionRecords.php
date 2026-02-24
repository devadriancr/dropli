<?php

namespace App\Jobs;

use App\Models\ProductionPlan;
use App\Models\Status;
use App\Models\YF013;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

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

        $productionPlans = ProductionPlan::with(['partNumber.workCenter', 'shift'])
            ->whereIn('id', $this->productionPlanIds)
            ->get();

        foreach ($productionPlans as $productionPlan) {
            try {
                if ($productionPlan->produced_quantity <= 0) {
                    $status = Status::where('key', 'completed')->first();
                    $productionPlan->update([
                        'synced_to_infor' => true,
                        'synced_at' => now(),
                        'status_id' => $status->id ?? null
                    ]);
                    $successCount++;
                    continue;
                }

                $infor = YF013::sendToInfor($productionPlan, null);

                if ($infor) {
                    $status = Status::where('key', 'completed')->first();
                    $productionPlan->update([
                        'synced_to_infor' => true,
                        'synced_at' => now(),
                        'status_id' => $status->id ?? null
                    ]);
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Error al sincronizar orden: {$productionPlan->shop_order_number}";
                }
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Error con orden {$productionPlan->shop_order_number}: " . $e->getMessage();
                Log::error("Error procesando plan de producción {$productionPlan->id}: " . $e->getMessage());
            }
        }

        $message = "ProcessProductionRecords: Se sincronizaron {$successCount} registros correctamente.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} registros tuvieron errores.";
            foreach ($errors as $error) {
                Log::error("Error en ProcessProductionRecords: " . $error);
            }
        }

        Log::info($message);

        YF013::executeInforProcess();
    }
}
