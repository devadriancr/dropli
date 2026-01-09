<?php

namespace App\Http\Controllers;

use App\Models\DowntimeRecord;
use App\Models\PartNumber;
use App\Models\ProductionPlan;
use App\Models\ProductionRecord;
use App\Models\ScrapRecord;
use App\Models\Shift;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Obtener todos los turnos para el select
        $shifts = Shift::all();

        // Obtener fecha y turno de la request o usar valores por defecto
        $selectedDate = $request->input('date', Shift::getPlannedDate());
        $selectedShiftId = $request->input('shift_id', Shift::getShift()->id);

        // Validar que la fecha no sea futura
        $today = Carbon::today()->toDateString();
        if ($selectedDate > $today) {
            $selectedDate = $today;
        }

        // Obtener el turno seleccionado
        $shift = Shift::find($selectedShiftId);
        if (!$shift) {
            $shift = Shift::getShift();
            $selectedShiftId = $shift->id;
        }

        $date = $selectedDate;

        $workCenter = Auth::user()->workCenters->pluck('id')->toArray();

        $start = Carbon::parse("{$date} {$shift->start_time}");
        $end = Carbon::parse("{$date} {$shift->end_time}");

        if ($start->gt($end)) {
            $end->addDay();
        }

        // Paros de Línea
        $downtimeRecords = DowntimeRecord::with(['workCenter'])
            ->whereIn('work_center_id', $workCenter)
            ->whereBetween('start_time', [$start, $end])
            ->get();

        $totalDowntimeMinutes = DowntimeRecord::whereIn('work_center_id', $workCenter)
            ->whereBetween('start_time', [$start, $end])
            ->whereHas('downtimeReason.downtimeType', function ($query) {
                $query->where('name', 'Planeado');
            })
            ->sum('minutes');

        $totalDowntimeCount = $downtimeRecords->count();

        // Tiempo Efectivo de Producción
        $effectiveProductionTimePerShift = $start->diffInMinutes($end) - $totalDowntimeMinutes;

        // Scrap
        $scrapRecords = ScrapRecord::whereBetween('created_at', [$start, $end])
            ->get();

        $totalScrap = $scrapRecords->sum('quantity');

        // Ganchos
        $productionRecords = ProductionRecord::with(['partNumber'])
            ->where('record_type', 'entry')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $quantityByPartNumber = [];
        $hooksByPartNumber = [];

        foreach ($productionRecords as $record) {
            $partNumberId = $record->part_number_id;

            if (!isset($quantityByPartNumber[$partNumberId])) {
                $quantityByPartNumber[$partNumberId] = 0;
            }

            $quantityByPartNumber[$partNumberId] += $record->quantity;
        }

        foreach ($quantityByPartNumber as $partNumberId => $totalQuantity) {
            $partNumber = PartNumber::with(['nextProcesses' => function ($query) {
                $query->where('is_obsolete', false);
            }])->find($partNumberId);

            foreach ($partNumber->nextProcesses as $nextPart) {
                $productionPlan = ProductionPlan::getProductionPlan($nextPart->id, $today, $shift->id);

                if ($productionPlan) {
                    $piecesPerHook = $nextPart->getCustomAttributeValue('pieces_per_hook');

                    if ($piecesPerHook && $piecesPerHook > 0) {
                        $hooksByPartNumber[$partNumberId] = [
                            'part_number' => $nextPart->number,
                            'total_quantity' => $totalQuantity,
                            'pieces_per_hook' => $piecesPerHook,
                            'total_hooks' => ceil($totalQuantity / $piecesPerHook),
                        ];
                        break; // Salir del bucle una vez encontrado un plan de producción válido
                    }
                }
            }
        }

        // $totalHooksUsedPerShift = array_sum($quantityByPartNumber);
        $totalHooksUsedPerShift = array_sum(array_column($hooksByPartNumber, 'total_hooks'));
        // $totalPaintedParts = round($totalPaintedParts, 2);

        // Tasa de Ganchos por Turno
        $cycleTimeSeconds = 16;
        $hangingRatePerShift = ($effectiveProductionTimePerShift > 0 && $totalHooksUsedPerShift !== null)
            ? round(((($cycleTimeSeconds / 60) * $totalHooksUsedPerShift) / $effectiveProductionTimePerShift) * 100, 2)
            : 0;

        // JPH Promedio por Turno
        $averageJphPerShift = ($totalHooksUsedPerShift != 0 && $totalHooksUsedPerShift != 0)
            ? round($totalHooksUsedPerShift / ($effectiveProductionTimePerShift / 60))
            : 0;

        // Man-hours per piece per shift - $manHoursPerPiecePerShift

        // Grafica
        $productionPlanChart = ProductionPlan::with(['partNumber'])
            ->where('planned_date', $date)
            ->where('shift_id', $shift->id)
            ->get();

        $chartData = $productionPlanChart->groupBy('partNumber.number')->map(function ($plans) {
            return [
                'part_number' => $plans->first()->partNumber->number,
                'part_name' => $plans->first()->partNumber->name,
                'planned_quantity' => $plans->sum('planned_quantity'),
                'produced_quantity' => $plans->sum('produced_quantity'),
            ];
        })->values();

        $labels = $chartData->pluck('part_number')->toArray();
        $data = [
            'planned' => $chartData->pluck('planned_quantity')->toArray(),
            'produced' => $chartData->pluck('produced_quantity')->toArray()
        ];

        return view('home.home', compact(
            'labels',
            'data',
            'shift',
            'date',
            'effectiveProductionTimePerShift',
            'totalDowntimeMinutes',
            'totalDowntimeCount',
            'totalScrap',
            'totalHooksUsedPerShift',
            'hangingRatePerShift',
            'averageJphPerShift',
            'shifts',
            'selectedDate',
            'selectedShiftId'
        ));
    }

    public function productionRecordsPdf(Request $request)
    {
        $selectedDate = $request->input('date', Shift::getPlannedDate());
        $selectedShiftId = $request->input('shift_id', Shift::getShift()->id);

        $today = Carbon::today()->toDateString();
        if ($selectedDate > $today) {
            $selectedDate = $today;
        }

        $shift = Shift::find($selectedShiftId);
        if (!$shift) {
            $shift = Shift::getShift();
            $selectedShiftId = $shift->id;
        }

        $date = $selectedDate;
        $workCenter = Auth::user()->workCenters->pluck('id')->toArray();

        $start = Carbon::parse("{$date} {$shift->start_time}");
        $end = Carbon::parse("{$date} {$shift->end_time}");

        if ($start->gt($end)) {
            $end->addDay();
        }

        // ===== MÉTRICAS DE PRODUCCIÓN =====
        // Paros de Línea
        $downtimeRecords = DowntimeRecord::with(['workCenter'])
            ->whereIn('work_center_id', $workCenter)
            ->whereBetween('start_time', [$start, $end])
            ->get();

        $totalDowntimeMinutes = DowntimeRecord::whereIn('work_center_id', $workCenter)
            ->whereBetween('start_time', [$start, $end])
            ->whereHas('downtimeReason.downtimeType', function ($query) {
                $query->where('name', 'Planeado');
            })
            ->sum('minutes');

        $totalDowntimeCount = $downtimeRecords->count();

        // Tiempo Efectivo de Producción
        $effectiveProductionTimePerShift = $start->diffInMinutes($end) - $totalDowntimeMinutes;

        // Scrap
        $scrapRecords = ScrapRecord::whereBetween('created_at', [$start, $end])->get();
        $totalScrap = $scrapRecords->sum('quantity');

        // Ganchos
        $productionRecords = ProductionRecord::with(['partNumber'])
            ->where('record_type', 'entry')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $quantityByPartNumber = [];
        $hooksByPartNumber = [];

        foreach ($productionRecords as $record) {
            $partNumberId = $record->part_number_id;
            if (!isset($quantityByPartNumber[$partNumberId])) {
                $quantityByPartNumber[$partNumberId] = 0;
            }
            $quantityByPartNumber[$partNumberId] += $record->quantity;
        }

        foreach ($quantityByPartNumber as $partNumberId => $totalQuantity) {
            $partNumber = PartNumber::with(['nextProcesses' => function ($query) {
                $query->where('is_obsolete', false);
            }])->find($partNumberId);

            foreach ($partNumber->nextProcesses as $nextPart) {
                $productionPlan = ProductionPlan::getProductionPlan($nextPart->id, $today, $shift->id);

                if ($productionPlan) {
                    $piecesPerHook = $nextPart->getCustomAttributeValue('pieces_per_hook');

                    if ($piecesPerHook && $piecesPerHook > 0) {
                        $hooksByPartNumber[$partNumberId] = [
                            'part_number' => $nextPart->number,
                            'total_quantity' => $totalQuantity,
                            'pieces_per_hook' => $piecesPerHook,
                            'total_hooks' => ceil($totalQuantity / $piecesPerHook)
                        ];
                        break;
                    }
                }
            }
        }

        // Total de ganchos usado por turno
        $totalHooksUsedPerShift = array_sum(array_column($hooksByPartNumber, 'total_hooks'));

        // Tasa de Ganchos por Turno
        $cycleTimeSeconds = 16;
        $hangingRatePerShift = ($effectiveProductionTimePerShift > 0 && $totalHooksUsedPerShift !== null)
            ? round(((($cycleTimeSeconds / 60) * $totalHooksUsedPerShift) / $effectiveProductionTimePerShift) * 100, 2)
            : 0;

        // JPH Promedio por Turno
        $averageJphPerShift = ($totalHooksUsedPerShift != 0 && $effectiveProductionTimePerShift > 0)
            ? round($totalHooksUsedPerShift / ($effectiveProductionTimePerShift / 60))
            : 0;

        // ===== TABLA DE PRODUCCIÓN =====
        $productionPlans = ProductionPlan::query()
            ->with([
                'partNumber.workCenter',
                'partNumber.standardPack',
                'partNumber.projects',
                'partNumber.nextProcesses',
                'partNumber.previousProcesses',
                'productionRecords'
            ])
            ->where('shift_id', $shift->id)
            ->whereDate('planned_date', $date)
            ->whereHas('partNumber.workCenter.area', function ($query) {
                $query->where('name', 'PAINT');
            })
            ->orderBy('part_number_id', 'asc')
            ->get();

        // Generar timeHeaders
        $timeHeaders = [];
        $current = $start->copy();
        while ($current->lt($end)) {
            $timeHeaders[] = $current->format('H:00');
            $current->addHour();
        }

        // Procesar registros para la tabla
        $groupedPlans = [];

        foreach ($productionPlans as $plan) {
            $partNumber = $plan->partNumber->number;

            if (!isset($groupedPlans[$partNumber])) {
                $groupedPlans[$partNumber] = [
                    'order_number' => $plan->shop_order_number ?? '-',
                    'line_name' => $plan->partNumber->previousProcesses->first()->workCenter->area->name ?? '-',
                    'part_number' => $partNumber,
                    'standard_pack' => $plan->partNumber->standardPack->name ?? '-',
                    'standard_pack_quantity' => $plan->partNumber->standard_pack_quantity ?? null,
                    'model' => $plan->partNumber->projects->pluck('model')->implode(';'),
                    'planned_quantity' => $plan->planned_quantity,
                    'produced_quantity' => $plan->produced_quantity,
                    'entries' => array_fill_keys($timeHeaders, 0),
                    'exits' => array_fill_keys($timeHeaders, 0),
                    'total_exits' => 0,
                ];
            }

            // Procesar entradas
            $previousProcesses = $plan->partNumber->previousProcesses;
            if ($previousProcesses) {
                foreach ($previousProcesses as $previousProcess) {
                    $data = ProductionRecord::query()
                        ->where('part_number_id', $previousProcess->id)
                        ->where('record_type', 'entry')
                        ->whereBetween('created_at', [$start, $end])
                        ->get();

                    foreach ($data as $record) {
                        $createdAt = Carbon::parse($record->created_at);
                        $hourKey = $createdAt->format('H:00');
                        if (in_array($hourKey, $timeHeaders)) {
                            $groupedPlans[$partNumber]['entries'][$hourKey] += $record->quantity;
                        }
                    }
                }
            }

            // Procesar salidas
            $exitRecords = ProductionRecord::query()
                ->where('part_number_id', $plan->part_number_id)
                ->where('record_type', 'exit')
                ->whereBetween('created_at', [$start, $end])
                ->get();

            foreach ($exitRecords as $record) {
                $createdAt = Carbon::parse($record->created_at);
                $hourKey = $createdAt->format('H:00');
                if (in_array($hourKey, $timeHeaders)) {
                    $groupedPlans[$partNumber]['exits'][$hourKey] += $record->quantity;
                    $groupedPlans[$partNumber]['total_exits'] += $record->quantity;
                }
            }
        }

        // También necesitamos incluir registros que no tengan production_plan
        $additionalProductionRecords = ProductionRecord::with(['partNumber'])
            ->where('record_type', 'entry')
            ->whereBetween('created_at', [$start, $end])
            ->whereDoesntHave('productionPlan')
            ->get();

        foreach ($additionalProductionRecords as $record) {
            $partNumber = $record->partNumber->number;

            if (!isset($groupedPlans[$partNumber])) {
                $groupedPlans[$partNumber] = [
                    'order_number' => $record->order_number ?? '-',
                    'line_name' => $record->partNumber->workCenter->area->name ?? '-',
                    'part_number' => $partNumber,
                    'standard_pack' => $record->partNumber->standardPack->name ?? '-',
                    'standard_pack_quantity' => $record->partNumber->standard_pack_quantity ?? null,
                    'model' => $record->partNumber->projects->pluck('model')->implode(';'),
                    'planned_quantity' => 0,
                    'produced_quantity' => 0,
                    'entries' => array_fill_keys($timeHeaders, 0),
                    'exits' => array_fill_keys($timeHeaders, 0),
                    'total_exits' => 0,
                ];
            }

            $createdAt = Carbon::parse($record->created_at);
            $hourKey = $createdAt->format('H:00');
            if (in_array($hourKey, $timeHeaders)) {
                $groupedPlans[$partNumber]['entries'][$hourKey] += $record->quantity;
            }
        }

        // ===== CALCULAR TOTALES POR HORA Y GENERALES =====
        $totalEntriesByHour = [];
        $totalExitsByHour = [];
        $grandTotalEntries = 0;
        $grandTotalExits = 0;

        // Inicializar arrays para totales por hora
        foreach ($timeHeaders as $header) {
            $totalEntriesByHour[$header] = 0;
            $totalExitsByHour[$header] = 0;
        }

        // Calcular totales por hora y generales
        foreach ($groupedPlans as &$record) {
            // Calcular totales por hora para esta parte
            $recordTotalEntries = 0;
            $recordTotalExits = 0;

            foreach ($timeHeaders as $header) {
                $entry = $record['entries'][$header] ?? 0;
                $exit = $record['exits'][$header] ?? 0;

                $recordTotalEntries += $entry;
                $recordTotalExits += $exit;

                $totalEntriesByHour[$header] += $entry;
                $totalExitsByHour[$header] += $exit;
            }

            // Agregar totales de esta parte al registro
            $record['total_entries'] = $recordTotalEntries;
            $record['total_exits_hourly'] = $recordTotalExits;

            // Sumar a totales generales
            $grandTotalEntries += $recordTotalEntries;
            $grandTotalExits += $record['total_exits'];
        }

        // Ordenar registros
        usort($groupedPlans, function ($a, $b) {
            $lineCompare = strcmp($a['line_name'], $b['line_name']);
            if ($lineCompare !== 0) {
                return $lineCompare;
            }
            return strcmp($a['part_number'], $b['part_number']);
        });

        $records = array_values($groupedPlans);

        // ===== DATOS PARA LA VISTA =====
        $data = [
            'date' => $date,
            'shift' => $shift,
            'effectiveProductionTimePerShift' => $effectiveProductionTimePerShift,
            'totalDowntimeMinutes' => $totalDowntimeMinutes,
            'totalDowntimeCount' => $totalDowntimeCount,
            'totalScrap' => $totalScrap,
            'totalHooksUsedPerShift' => $totalHooksUsedPerShift,
            'hangingRatePerShift' => $hangingRatePerShift,
            'averageJphPerShift' => $averageJphPerShift,
            'records' => $records,
            'timeHeaders' => $timeHeaders,
            'downtimeRecords' => $downtimeRecords,
            'scrapRecords' => $scrapRecords,
            'totalEntriesByHour' => $totalEntriesByHour,
            'totalExitsByHour' => $totalExitsByHour,
            'grandTotalEntries' => $grandTotalEntries,
            'grandTotalExits' => $grandTotalExits,
        ];

        $pdf = PDF::loadView('pdf.production-report', $data);
        $pdf->setPaper('letter', 'landscape');

        return $pdf->download('FOR-PIN-01_' . Carbon::parse($date)->format('Ymd') . $shift->abbreviation . '_' . Carbon::now()->format('YmdHis') . '.pdf');
    }
}
