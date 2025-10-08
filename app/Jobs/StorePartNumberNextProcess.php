<?php

namespace App\Jobs;

use App\Models\PartNumber;
use App\Models\PartNumberSequence;
use App\Models\WorkCenter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class StorePartNumberNextProcess implements ShouldQueue
{
    use Queueable;

    protected $partNumberSorted;

    /**
     * Create a new job instance.
     */
    public function __construct($partNumberSorted)
    {
        $this->partNumberSorted = $partNumberSorted;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->partNumberSorted as $part) {
            // Soporte para alias devueltos por la consulta (mayúsc/minúsc)
            $childNumber = isset($part->child_part) ? $part->child_part : ($part->CHILD_PART ?? null);
            $parentNumber = isset($part->parent_part) ? $part->parent_part : ($part->PARENT_PART ?? null);

            if (!$childNumber || !$parentNumber) {
                continue;
            }

            $childNumber = trim($childNumber);
            $parentNumber = trim($parentNumber);

            $currentPart = PartNumber::query()->where('number', $childNumber)->first();
            $nextPart = PartNumber::query()->where('number', $parentNumber)->first();

            // Si no existen en tu base local los números de parte, saltamos.
            if (!$currentPart || !$nextPart) {
                // puedes loggear para revisión si quieres
                logger()->info("MBM: Omitido porque no existe el part local - child: {$childNumber}, parent: {$parentNumber}");
                continue;
            }

            PartNumberSequence::updateOrCreate(
                [
                    'current_part_number_id' => $currentPart->id,
                    'next_part_number_id' => $nextPart->id
                ],
                [
                    'sequence_order' => 1,
                    'lead_time_hours' => null,
                    'is_active' => true
                ]
            );
        }
    }
}
