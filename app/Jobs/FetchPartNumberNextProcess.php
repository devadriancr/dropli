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

    public $tries = 3;
    public $timeout = 300; // 5 minutos
    public $backoff = 60; // Reintentar después de 1 minuto

    protected $partNumber;

    public function __construct(string $partNumber)
    {
        $this->partNumber = trim(Str::ascii($partNumber));
    }

    public function handle(): void
    {
        try {
            // 1) Búsqueda exacta por BPROD o BCHLD
            $sqlExact = <<<SQL
            SELECT DISTINCT
                TRIM(BCHLD) AS child_part,
                TRIM(BPROD) AS parent_part
            FROM LX834F01.MBM
            WHERE TRIM(BPROD) = ? OR TRIM(BCHLD) = ?
            SQL;

            $results = DB::connection('infor-live')->select($sqlExact, [
                $this->partNumber,
                $this->partNumber,
            ]);

            // 2) Si no hay resultados, búsqueda por prefijo
            if (empty($results)) {
                $base = preg_replace('/[A-Za-z]+$/', '', $this->partNumber);

                if (!empty($base) && $base !== $this->partNumber) {
                    $likeParam = $base . '%';

                    $sqlLike = <<<SQL
                    SELECT DISTINCT
                        TRIM(BCHLD) AS child_part,
                        TRIM(BPROD) AS parent_part
                    FROM LX834F01.MBM
                    WHERE TRIM(BPROD) LIKE ? OR TRIM(BCHLD) LIKE ?
                    SQL;

                    $results = DB::connection('infor-live')->select($sqlLike, [
                        $likeParam,
                        $likeParam,
                    ]);
                }
            }

            // Procesar y ordenar resultados
            $partNumberSorted = collect($results)
                ->unique(function ($item) {
                    $child = $item->child_part ?? $item->CHILD_PART ?? null;
                    $parent = $item->parent_part ?? $item->PARENT_PART ?? null;
                    return trim((string)$child) . '::' . trim((string)$parent);
                })
                ->sortBy(function ($item) {
                    return $item->parent_part ?? $item->PARENT_PART ?? null;
                })
                ->values();

            if ($partNumberSorted->isNotEmpty()) {
                StorePartNumberNextProcess::dispatch($partNumberSorted);
            } else {
                logger()->debug("MBM Fetch: Sin relaciones encontradas para: {$this->partNumber}");
            }
        } catch (Throwable $e) {
            $this->handleQueryError($e);
        }
    }

    protected function handleQueryError(Throwable $e): void
    {
        // Errores conocidos de DB2 que no son críticos
        if (
            strpos($e->getMessage(), 'SQL0802') !== false ||
            strpos($e->getMessage(), 'SQL0204') !== false
        ) {
            logger()->debug("MBM Fetch: Part sin proceso siguiente válido: {$this->partNumber}");
            return;
        }

        logger()->error("MBM Fetch: Error para {$this->partNumber}", [
            'error' => $e->getMessage(),
            'part_number' => $this->partNumber
        ]);

        // Reintentar con backoff
        $this->release($this->backoff);
    }

    public function failed(Throwable $exception): void
    {
        logger()->critical("MBM Fetch: Job fallido definitivamente para {$this->partNumber}", [
            'error' => $exception->getMessage(),
            'part_number' => $this->partNumber
        ]);
    }
}
