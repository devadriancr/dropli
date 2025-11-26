<?php

namespace App\Jobs;

use App\Models\PartNumber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
            ->select('number')
            ->where('is_obsolete', false) // Solo partes no obsoletos
            ->orderBy('number', 'asc')
            ->chunk(100, function ($partNumbers) use (&$totalParts) {
                foreach ($partNumbers as $partNumber) {
                    FetchPartNumberNextProcess::dispatch($partNumber->number);
                    $totalParts++;
                }
            });

        $duration = now()->diffInSeconds($startTime);

        logger()->info("MBM Sync: Sincronización masiva completada", [
            'total_parts_dispatched' => $totalParts,
            'duracion_segundos' => $duration
        ]);
    }
}
