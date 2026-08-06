<?php

namespace App\Jobs;

use App\Models\ProductionPlan;
use App\Models\Shift;
use App\Jobs\ProcessProductionRecords;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncProductionRecords implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $now = now();
            $mondayThisWeek = $now->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

            $endPlannedDate = Shift::getPreviousPlannedDate($now);
            $previousShift = Shift::getPreviousShift($now);

            if ($now->isMonday() && $endPlannedDate < $mondayThisWeek) {
                $startPlannedDate = $endPlannedDate;
            } else {
                $startPlannedDate = $mondayThisWeek;
            }

            if ($startPlannedDate > $endPlannedDate) {
                Log::info("No hay registros para sincronizar. Rango inválido: Inicio $startPlannedDate - Fin $endPlannedDate");
                return;
            }

            $allShifts = Shift::orderBy('start_time')->pluck('id')->toArray();
            $previousShiftIndex = array_search($previousShift->id, $allShifts);
            $allowedShiftIdsForEndDate = array_slice($allShifts, 0, $previousShiftIndex + 1);

            $productionPlans = ProductionPlan::with(['partNumber.workCenter', 'shift'])
                ->where(function ($q) {
                    $q->where('synced_to_infor', false)
                        ->orWhereNull('synced_to_infor');
                })
                ->whereNull('synced_at')
                ->whereHas('status', function ($q) {
                    $q->where('key', 'in_progress');
                })
                ->whereDoesntHave('partNumber.projects', function ($q) {
                    $q->where('model', '3Y');
                })
                ->where('planned_date', '>=', $startPlannedDate)
                ->where(function ($query) use ($endPlannedDate, $allowedShiftIdsForEndDate) {
                    $query->where('planned_date', '<', $endPlannedDate)
                        ->orWhere(function ($q) use ($endPlannedDate, $allowedShiftIdsForEndDate) {
                            $q->where('planned_date', '=', $endPlannedDate)
                                ->whereIn('shift_id', $allowedShiftIdsForEndDate);
                        });
                })
                ->get();

            if ($productionPlans->isEmpty()) {
                Log::info('No hay registros para sincronizar.');
                return;
            }

            $ids = $productionPlans->pluck('id')->toArray();

            if (!empty($ids)) {
                ProcessProductionRecords::dispatch($ids);
                Log::info('ProcessProductionRecords job dispatched for ' . count($ids) . ' plans.');
            }
        } catch (\Exception $e) {
            Log::error("Error general en SyncProductionRecords: " . $e->getMessage());
        }
    }
}
