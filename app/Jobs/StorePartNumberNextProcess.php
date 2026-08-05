<?php

namespace App\Jobs;

use App\Models\PartNumber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StorePartNumberNextProcess implements ShouldQueue
{
    use Queueable;

    protected $relations;

    public function __construct(Collection $relations)
    {
        $this->relations = $relations;
    }

    public function handle(): void
    {
        if ($this->relations->isEmpty()) {
            return;
        }

        $now = Carbon::now();

        // Recopilar todos los números de parte únicos del lote en una sola query
        $allNumbers = $this->relations
            ->flatMap(fn($r) => [
                trim((string) ($r->parent_part ?? '')),
                trim((string) ($r->child_part  ?? '')),
            ])
            ->filter(fn($n) => $n !== '')
            ->unique()
            ->values()
            ->all();

        $partMap = PartNumber::whereIn('number', $allNumbers)
            ->select('id', 'number')
            ->get()
            ->keyBy('number');

        $rows = [];

        foreach ($this->relations as $relation) {
            $parentNumber = trim((string) ($relation->parent_part ?? ''));
            $childNumber  = trim((string) ($relation->child_part  ?? ''));

            $currentPart = $partMap->get($childNumber);
            $nextPart    = $partMap->get($parentNumber);

            if (!$currentPart || !$nextPart) {
                continue;
            }

            $rows[] = [
                'current_part_number_id' => $currentPart->id,
                'next_part_number_id'    => $nextPart->id,
                'sequence_order'         => 1,
                'lead_time_hours'        => null,
                'is_active'              => true,
                'last_synced_at'         => $now,
                'created_at'             => $now,
                'updated_at'             => $now,
            ];
        }

        if (empty($rows)) {
            return;
        }

        // Un solo upsert para todo el lote - el índice unique_part_sequence maneja duplicados
        DB::table('part_number_sequences')->upsert(
            $rows,
            ['current_part_number_id', 'next_part_number_id'],
            ['sequence_order', 'is_active', 'last_synced_at', 'updated_at']
        );
    }
}
