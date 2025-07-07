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

    protected $partNumber;

    /**
     * Create a new job instance.
     */
    public function __construct(String $partNumber)
    {
        $this->partNumber = trim(Str::ascii($partNumber));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $sql = <<<SQL
            WITH NextProcess AS (
                SELECT DISTINCT
                    CAST(F.RPROD AS VARCHAR(50)) AS ChildPart,
                    CAST(F.RWRKC AS VARCHAR(20)) AS ChildWorkCenter,
                    CAST(F.ROPDS2 AS VARCHAR(20)) AS NextWorkCenter
                FROM LX834F01.FRT AS F
                INNER JOIN LX834F01.IIM AS I ON CAST(F.RPROD AS VARCHAR(50)) = CAST(I.IPROD AS VARCHAR(50))
                WHERE TRIM(CAST(I.IPROD AS VARCHAR(50))) = ?
            ),
            ParentParts AS (
                SELECT DISTINCT
                    CAST(Y.MCFPRO AS VARCHAR(50)) AS ParentPart,
                    CAST(F.RWRKC AS VARCHAR(20)) AS ParentWorkCenter,
                    CAST(F.ROPDS2 AS VARCHAR(20)) AS ParentNextProcess
                FROM LX834FU01.YMCOM AS Y
                INNER JOIN LX834F01.FRT AS F ON CAST(Y.MCFPRO AS VARCHAR(50)) = CAST(F.RPROD AS VARCHAR(50))
                INNER JOIN LX834F01.IIM AS I ON CAST(F.RPROD AS VARCHAR(50)) = CAST(I.IPROD AS VARCHAR(50))
                WHERE TRIM(CAST(I.IMPLC AS VARCHAR(10))) NOT LIKE 'OBSOLETE'
                    AND TRIM(CAST(Y.MCCPRO AS VARCHAR(50))) = ?
                    AND TRIM(CAST(Y.MCFPRO AS VARCHAR(50))) <> ?
            )
            SELECT DISTINCT
                TRIM(CAST(N.ChildPart AS VARCHAR(50))) AS child_part,
                TRIM(CAST(N.ChildWorkCenter AS VARCHAR(20))) AS child_work_center,
                TRIM(CAST(P.ParentPart AS VARCHAR(50))) AS parent_part,
                TRIM(CAST(P.ParentWorkCenter AS VARCHAR(20))) AS parent_work_center
            FROM ParentParts AS P
            INNER JOIN NextProcess AS N
                ON CAST(P.ParentWorkCenter AS VARCHAR(20)) = CAST(N.NextWorkCenter AS VARCHAR(20))
                OR CAST(P.ParentNextProcess AS VARCHAR(20)) = CAST(N.NextWorkCenter AS VARCHAR(20))
            SQL;

            $results = DB::connection('infor-live')->select($sql, [
                $this->partNumber,
                $this->partNumber,
                $this->partNumber
            ]);

            $partNumberSorted = collect($results)->sortByDesc('parent_part');

            // Solo despachar si hay resultados
            if ($partNumberSorted->isNotEmpty()) {
                StorePartNumberNextProcess::dispatch($partNumberSorted);
            } else {
                // logger()->warning("No se encontraron procesos siguientes para: {$this->partNumber}");
            }
        } catch (Throwable $e) {
            $this->handleQueryError($e);
        }
    }

    protected function handleQueryError(Throwable $e): void
    {
        // Verificar si es el error específico de falta de procesos
        if (
            strpos($e->getMessage(), 'SQL0802') !== false ||
            strpos($e->getMessage(), 'SQL0204') !== false
        ) {

            logger()->warning("El número de parte {$this->partNumber} no tiene proceso siguiente válido");
            return;
        }

        // Registrar otros errores y reintentar
        logger()->error("Error al obtener proceso siguiente para {$this->partNumber}: " . $e->getMessage());

        // Reintentar después de 5 minutos (opcional)
        $this->release(300);
    }

    public function failed(Throwable $exception): void
    {
        // Lógica para manejar fallos permanentes
        logger()->critical("Job fallido para {$this->partNumber}: " . $exception->getMessage());
    }
}
