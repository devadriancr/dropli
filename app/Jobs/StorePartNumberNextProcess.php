<?php

namespace App\Jobs;

use App\Models\PartNumber;
use App\Models\PartNumberSequence;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StorePartNumberNextProcess implements ShouldQueue
{
    use Queueable;

    protected $partNumberSorted;
    protected $syncTimestamp;

    public function __construct($partNumberSorted)
    {
        $this->partNumberSorted = $partNumberSorted;
        $this->syncTimestamp = Carbon::now();
    }

    public function handle(): void
    {
        if ($this->partNumberSorted->isEmpty()) {
            return;
        }

        // Agrupar por número de parte hijo (current_part_number)
        $groupedByChild = $this->partNumberSorted->groupBy(function ($part) {
            return isset($part->child_part) ? trim($part->child_part) : trim($part->CHILD_PART ?? '');
        });

        foreach ($groupedByChild as $childNumber => $relations) {
            $this->syncPartNumberRelations($childNumber, $relations);
        }
    }

    /**
     * Sincroniza las relaciones de un número de parte específico
     * Estrategia: Upsert + Marcado de obsoletos (mantiene histórico completo)
     */
    protected function syncPartNumberRelations(string $childNumber, $relations): void
    {
        $currentPart = PartNumber::query()->where('number', $childNumber)->first();

        if (!$currentPart) {
            logger()->info("MBM Sync: Part number no encontrado en sistema local: {$childNumber}");
            return;
        }

        try {
            DB::transaction(function () use ($currentPart, $relations, $childNumber) {

                // Preparar datos para upsert masivo
                $upsertData = [];
                $activeNextPartIds = [];

                foreach ($relations as $index => $part) {
                    $parentNumber = isset($part->parent_part)
                        ? trim($part->parent_part)
                        : trim($part->PARENT_PART ?? '');

                    if (!$parentNumber) {
                        continue;
                    }

                    $nextPart = PartNumber::query()->where('number', $parentNumber)->first();

                    if (!$nextPart) {
                        logger()->debug("MBM Sync: Next part no encontrado: {$parentNumber} para {$childNumber}");
                        continue;
                    }

                    $activeNextPartIds[] = $nextPart->id;

                    $upsertData[] = [
                        'current_part_number_id' => $currentPart->id,
                        'next_part_number_id' => $nextPart->id,
                        'sequence_order' => $index + 1,
                        'lead_time_hours' => null,
                        'is_active' => true,
                        'last_synced_at' => $this->syncTimestamp,
                        'updated_at' => $this->syncTimestamp,
                    ];
                }

                if (empty($upsertData)) {
                    logger()->info("MBM Sync: No hay relaciones válidas para sincronizar: {$childNumber}");

                    // Marcar todas las relaciones existentes como inactivas (el part ya no tiene relaciones en DB2)
                    PartNumberSequence::where('current_part_number_id', $currentPart->id)
                        ->where('is_active', true)
                        ->update([
                            'is_active' => false,
                            'updated_at' => $this->syncTimestamp
                        ]);

                    return;
                }

                // UPSERT MASIVO: Crear o actualizar registros existentes
                PartNumberSequence::upsert(
                    $upsertData,
                    ['current_part_number_id', 'next_part_number_id'], // Unique keys
                    ['sequence_order', 'lead_time_hours', 'is_active', 'last_synced_at', 'updated_at'] // Campos a actualizar
                );

                // Marcar como INACTIVOS los registros que ya no existen en DB2
                // (los que no fueron tocados en este sync)
                $deactivatedCount = PartNumberSequence::where('current_part_number_id', $currentPart->id)
                    ->where('is_active', true)
                    ->where(function ($query) use ($activeNextPartIds) {
                        $query->whereNotIn('next_part_number_id', $activeNextPartIds)
                            ->orWhere('last_synced_at', '<', $this->syncTimestamp)
                            ->orWhereNull('last_synced_at');
                    })
                    ->update([
                        'is_active' => false,
                        'updated_at' => $this->syncTimestamp
                    ]);

                $stats = [
                    'part_number' => $childNumber,
                    'relaciones_sincronizadas' => count($upsertData),
                    'relaciones_desactivadas' => $deactivatedCount,
                ];

                logger()->info("MBM Sync completado", $stats);
            });
        } catch (\Exception $e) {
            logger()->error("MBM Sync: Error sincronizando {$childNumber}: " . $e->getMessage(), [
                'exception' => $e,
                'part_number' => $childNumber
            ]);
            throw $e;
        }
    }
}
