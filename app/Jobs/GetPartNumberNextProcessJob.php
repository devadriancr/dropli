<?php

namespace App\Jobs;

use App\Models\PartNumber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB; // requerido para DB::raw en whereNotExists

class GetPartNumberNextProcessJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 3600;

    public function handle(): void
    {
        $startTime = now();
        logger()->info("MBM Sync: Iniciando sincronización masiva de relaciones");

        $totalParts = 0;

        PartNumber::query()
            ->select('part_numbers.number', 'part_numbers.id')
            ->where('is_obsolete', false)
            ->whereNotExists(function ($query) {
                // Excluir partes cuyas relaciones ya se sincronizaron en las últimas 20 horas
                $query->select(DB::raw(1))
                    ->from('part_number_sequences')
                    ->whereColumn('current_part_number_id', 'part_numbers.id')
                    ->where('last_synced_at', '>=', now()->subHours(20));
            })
            ->orderBy('part_numbers.number', 'asc')
            ->chunk(200, function ($partNumbers) use (&$totalParts) {
                $numbers = $partNumbers->pluck('number')->all();
                FetchPartNumberNextProcess::dispatch($numbers)->onQueue('infor-sync');
                $totalParts += count($numbers);
            });

        $duration = now()->diffInSeconds($startTime);

        logger()->info("MBM Sync: Sincronización masiva completada", [
            'total_parts_dispatched' => $totalParts,
            'duracion_segundos' => $duration
        ]);
    }
}
