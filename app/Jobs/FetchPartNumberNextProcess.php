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
    public $timeout = 300;
    public $backoff = 60;

    protected $partNumber;

    public function __construct(string $partNumber)
    {
        $this->partNumber = trim(Str::ascii($partNumber));
    }

    public function handle(): void
    {
        try {
            // Buscar relaciones donde el parte actual es PADRE (BPROD)
            $sqlAsParent = <<<SQL
            SELECT DISTINCT
                TRIM(BPROD) AS parent_part,
                TRIM(BCHLD) AS child_part
            FROM LX834F01.MBM
            WHERE TRIM(BPROD) = ?
            SQL;

            $parentResults = DB::connection('infor-live')->select($sqlAsParent, [$this->partNumber]);

            // Buscar relaciones donde el parte actual es HIJO (BCHLD)
            $sqlAsChild = <<<SQL
            SELECT DISTINCT
                TRIM(BPROD) AS parent_part,
                TRIM(BCHLD) AS child_part
            FROM LX834F01.MBM
            WHERE TRIM(BCHLD) = ?
            SQL;

            $childResults = DB::connection('infor-live')->select($sqlAsChild, [$this->partNumber]);

            // Combinar resultados
            $allResults = array_merge($parentResults, $childResults);

            // Si no hay resultados, intentar búsqueda flexible
            if (empty($allResults)) {
                $allResults = $this->flexibleSearch();
            }

            // Procesar y limpiar resultados
            $processedResults = collect($allResults)
                ->filter(function ($item) {
                    $parent = $item->parent_part ?? $item->PARENT_PART ?? null;
                    $child = $item->child_part ?? $item->CHILD_PART ?? null;

                    return !empty(trim((string)$parent)) && !empty(trim((string)$child));
                })
                ->unique(function ($item) {
                    $parent = $item->parent_part ?? $item->PARENT_PART ?? null;
                    $child = $item->child_part ?? $item->CHILD_PART ?? null;
                    return trim((string)$parent) . '::' . trim((string)$child);
                })
                ->values();

            if ($processedResults->isNotEmpty()) {
                logger()->info("MBM Fetch: Relaciones encontradas para {$this->partNumber}", [
                    'count' => $processedResults->count(),
                    'relations' => $processedResults->toArray()
                ]);

                StorePartNumberNextProcess::dispatch($processedResults, $this->partNumber);
            } else {
                logger()->warning("MBM Fetch: Sin relaciones encontradas para: {$this->partNumber}");
            }
        } catch (Throwable $e) {
            $this->handleQueryError($e);
        }
    }

    protected function flexibleSearch()
    {
        $results = [];

        // Búsqueda por coincidencia parcial
        $likeParam = $this->partNumber . '%';

        $sqlLike = <<<SQL
        SELECT DISTINCT
            TRIM(BPROD) AS parent_part,
            TRIM(BCHLD) AS child_part
        FROM LX834F01.MBM
        WHERE TRIM(BPROD) LIKE ? OR TRIM(BCHLD) LIKE ?
        SQL;

        $likeResults = DB::connection('infor-live')->select($sqlLike, [$likeParam, $likeParam]);
        $results = array_merge($results, $likeResults);

        // Búsqueda sin espacios ni guiones
        $cleanPartNumber = str_replace([' ', '-'], '', $this->partNumber);
        if ($cleanPartNumber !== $this->partNumber) {
            $sqlClean = <<<SQL
            SELECT DISTINCT
                TRIM(BPROD) AS parent_part,
                TRIM(BCHLD) AS child_part
            FROM LX834F01.MBM
            WHERE REPLACE(REPLACE(BPROD, ' ', ''), '-', '') = ?
               OR REPLACE(REPLACE(BCHLD, ' ', ''), '-', '') = ?
            SQL;

            $cleanResults = DB::connection('infor-live')->select($sqlClean, [$cleanPartNumber, $cleanPartNumber]);
            $results = array_merge($results, $cleanResults);
        }

        return $results;
    }

    protected function handleQueryError(Throwable $e): void
    {
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
