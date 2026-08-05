<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class FetchPartNumberNextProcess implements ShouldQueue
{
    use Queueable;

    public $tries   = 3;
    public $timeout = 300;
    public $backoff = 60;

    protected array $partNumbers;

    public function __construct(array $partNumbers)
    {
        $this->partNumbers = array_values(
            array_unique(
                array_map(fn($p) => trim(Str::ascii($p)), $partNumbers)
            )
        );
    }

    public function handle(): void
    {
        if (empty($this->partNumbers)) {
            return;
        }

        try {
            $placeholders = implode(',', array_fill(0, count($this->partNumbers), '?'));

            // Relaciones donde alguno del lote es PADRE u HIJO (una sola consulta, un solo escaneo)
            $sql = <<<SQL
            SELECT DISTINCT
                TRIM(BPROD) AS parent_part,
                TRIM(BCHLD) AS child_part
            FROM LX834F01.MBM
            WHERE TRIM(BPROD) IN ($placeholders)
               OR TRIM(BCHLD) IN ($placeholders)
            SQL;

            $params = array_merge($this->partNumbers, $this->partNumbers);
            $results = DB::connection('infor-live')->select($sql, $params);

            $allResults = collect($results)
                ->filter(function ($item) {
                    $parent = trim((string) ($item->parent_part ?? ''));
                    $child  = trim((string) ($item->child_part  ?? ''));
                    return $parent !== '' && $child !== '';
                })
                ->unique(fn($item) => $item->parent_part . '::' . $item->child_part)
                ->values();

            if ($allResults->isEmpty()) {
                return;
            }

            StorePartNumberNextProcess::dispatch($allResults);

        } catch (Throwable $e) {
            $this->handleQueryError($e);
        }
    }

    protected function handleQueryError(Throwable $e): void
    {
        if (
            str_contains($e->getMessage(), 'SQL0802') ||
            str_contains($e->getMessage(), 'SQL0204')
        ) {
            logger()->debug('Error SQL esperado al sincronizar relaciones de partes en lote', ['error' => $e->getMessage()]);
            return;
        }

        logger()->error('Error al sincronizar relaciones de partes en lote', [
            'error' => $e->getMessage(),
            'batch_size' => count($this->partNumbers),
        ]);

        $this->release($this->backoff);
    }

    public function failed(Throwable $exception): void
    {
        logger()->critical('Job de sincronización de relaciones de partes falló definitivamente', [
            'error'      => $exception->getMessage(),
            'batch_size' => count($this->partNumbers),
        ]);
    }
}
