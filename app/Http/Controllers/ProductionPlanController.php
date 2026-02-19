<?php

namespace App\Http\Controllers;

use App\Models\ProductionPlan;
use App\Models\ScrapRecord;
use App\Models\Shift;
use App\Models\Status;
use App\Models\YF013;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductionPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $now = now();
        $startOfWeek = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $endOfWeek = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $productionPlans = ProductionPlan::with([
            'partNumber.standardPack',
            'status',
            'shift'
        ])
            ->whereBetween('planned_date', [$startOfWeek, $endOfWeek])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('shop_order_number', 'like', "%$search%")
                        ->orWhere('planned_date', 'like', "%$search%")
                        ->orWhere('planned_quantity', 'like', "%$search%")
                        ->orWhereHas('partNumber', function ($q2) use ($search) {
                            $q2->where('number', 'like', "%$search%");
                        })
                        ->orWhereHas('shift', function ($q3) use ($search) {
                            $q3->where('abbreviation', 'like', "%$search%");
                        })
                        ->orWhereHas('status', function ($q4) use ($search) {
                            $q4->where('label', 'like', "%$search%");
                        });
                });
            })
            ->orderBy('planned_date', 'asc')
            ->orderBy(
                Shift::select('abbreviation')
                    ->whereColumn('shifts.id', 'production_plans.shift_id')
                    ->limit(1)
            )
            ->paginate(10)
            ->withQueryString();

        return view('production-plans.index', [
            'productionPlans' => $productionPlans,
            'search' => $search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:production_plans,id'
        ]);

        $productionPlan = ProductionPlan::with(['partNumber.workCenter', 'shift'])->find($request->id);
        if (!$productionPlan) {
            throw new Exception('Plan de producción no encontrado');
        }

        // Obtener solo el scrap NO sincronizado
        $unsyncedScrap = ScrapRecord::getUnsyncedScrap($productionPlan->part_number_id);
        $accumulatedScrap = $unsyncedScrap->sum('quantity');

        $infor = YF013::sendToInfor($productionPlan, $accumulatedScrap);

        if ($infor) {
            $status = Status::where('key', 'completed')->first();

            $productionPlan->update([
                'synced_to_infor' => true,
                'synced_at' => now(),
                'status_id' => $status->id ?? null
            ]);

            // Marcar el scrap como sincronizado
            if ($unsyncedScrap->isNotEmpty()) {
                ScrapRecord::markAsSynced($unsyncedScrap);
            }
        }

        return redirect()->back()->with('success', 'Datos sincronizados correctamente con Infor');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Sync all eligible production plans to Infor
     */
    public function syncAll(Request $request)
    {
        try {
            $now = now(); // Simulación de fecha y hora actual para pruebas

            // Fecha de inicio: Lunes de la semana actual a las 00:00:00
            $startPlannedDate = $now->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

            // Obtenemos el turno anterior y la fecha a la que pertenece ese turno anterior
            $previousShift = Shift::getPreviousShift($now);
            $endPlannedDate = Shift::getPreviousPlannedDate($now);

            // Lógica de agrupamiento de turnos:
            // Ordenamos los turnos cronológicamente (Diurno primero, Nocturno después)
            $allShifts = Shift::orderBy('start_time')->pluck('id')->toArray();

            // Buscamos qué posición ocupa el turno anterior en la lista
            $previousShiftIndex = array_search($previousShift->id, $allShifts);

            // Obtenemos los IDs de los turnos permitidos para el último día de la consulta
            $allowedShiftIdsForEndDate = array_slice($allShifts, 0, $previousShiftIndex + 1);

            // Consulta de ProductionPlans
            $productionPlans = ProductionPlan::with(['partNumber.workCenter', 'shift'])
                ->where(function ($q) {
                    $q->where('synced_to_infor', false)
                        ->orWhereNull('synced_to_infor');
                })
                ->whereNull('synced_at')
                ->whereHas('status', function ($q) {
                    $q->where('key', 'in_progress');
                })
                // Rango de fecha y turno:
                ->where('planned_date', '>=', $startPlannedDate)
                ->where(function ($query) use ($endPlannedDate, $allowedShiftIdsForEndDate) {
                    // Opción A: Es de un día estrictamente anterior al día final
                    $query->where('planned_date', '<', $endPlannedDate)
                        // Opción B: Si es el mismo día final, el turno debe ser menor o igual al turno anterior
                        ->orWhere(function ($q) use ($endPlannedDate, $allowedShiftIdsForEndDate) {
                            $q->where('planned_date', '=', $endPlannedDate)
                                ->whereIn('shift_id', $allowedShiftIdsForEndDate);
                        });
                })
                ->get();

            if ($productionPlans->isEmpty()) {
                return redirect()->back()->with('info', 'No hay registros para sincronizar.');
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($productionPlans as $productionPlan) {
                try {
                    // Validar si tiene cantidad producida
                    if ($productionPlan->produced_quantity <= 0) {
                        // Cambiar estado sin enviar a Infor
                        $status = Status::where('key', 'completed')->first();
                        $productionPlan->update([
                            'synced_to_infor' => true,
                            'synced_at' => now(),
                            'status_id' => $status->id ?? null
                        ]);
                        $successCount++;
                        continue;
                    }

                    // Obtener solo el scrap NO sincronizado para este número de parte
                    $unsyncedScrap = ScrapRecord::getUnsyncedScrap($productionPlan->part_number_id);
                    $accumulatedScrap = $unsyncedScrap->sum('quantity');

                    // Si no hay scrap para sincronizar, usar 0
                    if ($accumulatedScrap == 0 && $unsyncedScrap->isEmpty()) {
                        $accumulatedScrap = 0;
                    }

                    // Enviar a Infor
                    $infor = YF013::sendToInfor($productionPlan, $accumulatedScrap);

                    if ($infor) {
                        $status = Status::where('key', 'completed')->first();

                        // Actualizar el plan de producción
                        $productionPlan->update([
                            'synced_to_infor' => true,
                            'synced_at' => now(),
                            'status_id' => $status->id ?? null
                        ]);

                        // Marcar los registros de scrap como sincronizados
                        if ($unsyncedScrap->isNotEmpty()) {
                            ScrapRecord::markAsSynced($unsyncedScrap);
                        }

                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "Error al sincronizar orden: {$productionPlan->shop_order_number}";
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Error con orden {$productionPlan->shop_order_number}: " . $e->getMessage();
                   Log::error("Error sincronizando plan de producción {$productionPlan->id}: " . $e->getMessage());
                }
            }

            $message = "Se sincronizaron {$successCount} registros correctamente.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} registros tuvieron errores.";
                foreach ($errors as $error) {
                   Log::error("Error en syncAll: " . $error);
                }
            }

            YF013::executeInforProcess();

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
           Log::error("Error general en syncAll: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al sincronizar los registros: ' . $e->getMessage());
        }
    }
}
