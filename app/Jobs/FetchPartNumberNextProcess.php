<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Support\Arr;

class FetchPartNumberNextProcess implements ShouldQueue
{
    use Queueable;

    protected $partNumber;

    /**
     * Create a new job instance.
     */
    public function __construct(string $partNumber)
    {
        $this->partNumber = trim(Str::ascii($partNumber));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // 1) Intentamos búsqueda exacta por BPROD o BCHLD
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

            // 2) Si no encontramos resultados, intentamos buscar por "prefijo"
            // quitando sufijos alfabéticos (ej. ABC123A -> ABC123) y haciendo LIKE 'ABC123%'
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

            // Convertir a colección y ordenar (similar al flujo anterior)
            $partNumberSorted = collect($results)->unique(function ($item) {
                // Unicidad por child+parent
                $child = isset($item->child_part) ? $item->child_part : ($item->CHILD_PART ?? null);
                $parent = isset($item->parent_part) ? $item->parent_part : ($item->PARENT_PART ?? null);

                return trim((string)$child) . '::' . trim((string)$parent);
            })->sortByDesc(function ($item) {
                // ordenar por parent para mantener comportamiento previo
                return isset($item->parent_part) ? $item->parent_part : ($item->PARENT_PART ?? null);
            })->values();

            if ($partNumberSorted->isNotEmpty()) {
                // Despachar al job que lo guarda (usa la misma estructura que tenías)
                StorePartNumberNextProcess::dispatch($partNumberSorted);
            } else {
                logger()->warning("No se encontraron relaciones MBM para: {$this->partNumber}");
            }
        } catch (Throwable $e) {
            $this->handleQueryError($e);
        }
    }

    protected function handleQueryError(Throwable $e): void
    {
        // Manejo similar al anterior
        if (
            strpos($e->getMessage(), 'SQL0802') !== false ||
            strpos($e->getMessage(), 'SQL0204') !== false
        ) {
            logger()->warning("El número de parte {$this->partNumber} no tiene proceso siguiente válido");
            return;
        }

        logger()->error("Error al obtener procesos desde MBM para {$this->partNumber}: " . $e->getMessage());
        $this->release(300); // reintentar en 5 minutos
    }

    public function failed(Throwable $exception): void
    {
        logger()->critical("Job fallido para {$this->partNumber}: " . $exception->getMessage());
    }
}
