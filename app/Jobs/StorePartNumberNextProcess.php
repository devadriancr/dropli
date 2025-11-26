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
    protected $originalPartNumber;
    protected $syncTimestamp;

    public function __construct($partNumberSorted, string $originalPartNumber = null)
    {
        $this->partNumberSorted = $partNumberSorted;
        $this->originalPartNumber = $originalPartNumber;
        $this->syncTimestamp = Carbon::now();
    }

    public function handle(): void
    {
        if ($this->partNumberSorted->isEmpty()) {
            logger()->info("MBM Store: No hay datos para procesar");
            return;
        }

        try {
            // Procesar cada relación individualmente
            foreach ($this->partNumberSorted as $relation) {
                $this->processSingleRelation($relation);
            }

            logger()->info("MBM Store: Procesamiento completado", [
                'total_relations' => $this->partNumberSorted->count(),
                'original_part' => $this->originalPartNumber
            ]);

        } catch (\Exception $e) {
            logger()->error("MBM Store: Error en procesamiento masivo", [
                'error' => $e->getMessage(),
                'original_part' => $this->originalPartNumber
            ]);
            throw $e;
        }
    }

    protected function processSingleRelation($relation): void
    {
        $childNumber = isset($relation->child_part)
            ? trim($relation->child_part)
            : trim($relation->CHILD_PART ?? '');

        $parentNumber = isset($relation->parent_part)
            ? trim($relation->parent_part)
            : trim($relation->PARENT_PART ?? '');

        if (empty($childNumber) || empty($parentNumber)) {
            logger()->debug("MBM Store: Relación inválida - Child: {$childNumber}, Parent: {$parentNumber}");
            return;
        }

        // Buscar los part numbers en la base de datos local
        $currentPart = PartNumber::where('number', $childNumber)->first();
        $nextPart = PartNumber::where('number', $parentNumber)->first();

        if (!$currentPart) {
            logger()->info("MBM Store: Part number hijo no encontrado: {$childNumber}");
            return;
        }

        if (!$nextPart) {
            logger()->info("MBM Store: Part number padre no encontrado: {$parentNumber}");
            return;
        }

        // Determinar el orden de secuencia (si el parte original es el padre o el hijo)
        $sequenceOrder = 1;
        if ($this->originalPartNumber && $childNumber === $this->originalPartNumber) {
            // El parte original es el hijo, esta es una relación "siguiente proceso"
            $sequenceOrder = 1;
        } elseif ($this->originalPartNumber && $parentNumber === $this->originalPartNumber) {
            // El parte original es el padre, esta es una relación "proceso anterior"
            $sequenceOrder = 1;
        }

        try {
            DB::transaction(function () use ($currentPart, $nextPart, $sequenceOrder, $childNumber, $parentNumber) {
                // Crear o actualizar la relación
                PartNumberSequence::updateOrCreate(
                    [
                        'current_part_number_id' => $currentPart->id,
                        'next_part_number_id' => $nextPart->id
                    ],
                    [
                        'sequence_order' => $sequenceOrder,
                        'lead_time_hours' => null,
                        'is_active' => true,
                        'last_synced_at' => $this->syncTimestamp,
                        'updated_at' => $this->syncTimestamp,
                    ]
                );

                logger()->debug("MBM Store: Relación establecida", [
                    'child' => $childNumber,
                    'parent' => $parentNumber,
                    'sequence_order' => $sequenceOrder
                ]);
            });

        } catch (\Exception $e) {
            logger()->error("MBM Store: Error estableciendo relación", [
                'error' => $e->getMessage(),
                'child' => $childNumber,
                'parent' => $parentNumber
            ]);
        }
    }
}
