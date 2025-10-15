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

        // Tiempo Efectivo de Producción
        $effectiveProductionTimePerShift = $start->diffInMinutes($end);

        // Paros de Línea
        $downtimeRecords = DowntimeRecord::with(['workCenter'])
            ->whereIn('work_center_id', $workCenter)
            ->whereBetween('start_time', [$start, $end])
            ->get();

        $totalDowntimeMinutes = $downtimeRecords->sum('minutes');
        $totalDowntimeCount = $downtimeRecords->count();

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
            $partNumber = PartNumber::find($partNumberId)->nextProcesses->where('is_obsolete', false)->first();

            if ($partNumber) {
                $piecesPerHook = $partNumber->getCustomAttributeValue('pieces_per_hook');

                if ($piecesPerHook && $piecesPerHook > 0) {
                    $hooksByPartNumber[$partNumberId] = [
                        'part_number' => $partNumber->number,
                        'total_quantity' => $totalQuantity,
                        'pieces_per_hook' => $piecesPerHook,
                        'total_hooks' => $totalQuantity / $piecesPerHook
                    ];
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
            ? round($totalHooksUsedPerShift / $effectiveProductionTimePerShift, 2)
            : 0;

        // Man-hours per piece per shift - $manHoursPerPiecePerShift

        // Grafica
        $productionPlans = ProductionPlan::with(['partNumber'])
            ->where('planned_date', $date)
            ->where('shift_id', $shift->id)
            ->get();

        $chartData = $productionPlans->groupBy('partNumber.number')->map(function ($plans) {
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

        $effectiveProductionTimePerShift = $start->diffInMinutes($end);

        $downtimeRecords = DowntimeRecord::with(['workCenter'])
            ->whereIn('work_center_id', $workCenter)
            ->whereBetween('start_time', [$start, $end])
            ->get();

        $totalDowntimeMinutes = $downtimeRecords->sum('minutes');
        $totalDowntimeCount = $downtimeRecords->count();

        $scrapRecords = ScrapRecord::whereBetween('created_at', [$start, $end])
            ->get();

        $totalScrap = $scrapRecords->sum('quantity');

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
            $partNumber = PartNumber::find($partNumberId)->nextProcesses->where('is_obsolete', false)->first();
            if ($partNumber) {
                $piecesPerHook = $partNumber->getCustomAttributeValue('pieces_per_hook');
                if ($piecesPerHook && $piecesPerHook > 0) {
                    $hooksByPartNumber[$partNumberId] = [
                        'part_number' => $partNumber->number,
                        'total_quantity' => $totalQuantity,
                        'pieces_per_hook' => $piecesPerHook,
                        'total_hooks' => $totalQuantity / $piecesPerHook
                    ];
                }
            }
        }

        $totalHooksUsedPerShift = array_sum(array_column($hooksByPartNumber, 'total_hooks'));

        $cycleTimeSeconds = 16;
        $hangingRatePerShift = ($effectiveProductionTimePerShift > 0 && $totalHooksUsedPerShift !== null)
            ? round(((($cycleTimeSeconds / 60) * $totalHooksUsedPerShift) / $effectiveProductionTimePerShift) * 100, 2)
            : 0;

        $averageJphPerShift = ($totalHooksUsedPerShift != 0)
            ? round($totalHooksUsedPerShift / $effectiveProductionTimePerShift, 2)
            : 0;

        // Obtener datos para la tabla de producción
        $productionPlans = ProductionPlan::query()
            ->with([
                'partNumber.workCenter',
                'partNumber.standardPack',
                'partNumber.projects',
                'partNumber.nextProcesses',
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
        $start = Carbon::parse("{$date} {$shift->start_time}");
        $end = Carbon::parse("{$date} {$shift->end_time}");

        if ($start->gt($end)) {
            $end->addDay();
        }

        $current = $start->copy();
        while ($current->lt($end)) {
            $timeHeaders[] = $current->format('H:00');
            $current->addHour();
        }

        // Procesar registros para la tabla
        $records = [];
        $groupedPlans = [];

        foreach ($productionPlans as $plan) {
            $partNumber = $plan->partNumber->number;

            if (!isset($groupedPlans[$partNumber])) {
                $groupedPlans[$partNumber] = [
                    'line_name' => $plan->partNumber->previousProcesses->first()->workCenter->area->name ?? '-',
                    'part_number' => $partNumber,
                    'standard_pack' => $plan->partNumber->standardPack->name ?? '-',
                    'standard_pack_quantity' => $plan->partNumber->standard_pack_quantity ?? null,
                    'model' => $plan->partNumber->projects->pluck('model')->implode(';'),
                    'planned_quantity' => $plan->planned_quantity,
                    'produced_quantity' => $plan->produced_quantity,
                    'entries' => array_fill_keys($timeHeaders, 0),
                    'exits' => array_fill_keys($timeHeaders, 0),
                    'total_entries' => 0,
                ];
            }

            // Procesar entradas
            $previousProcesses = $plan->partNumber->previousProcesses;
            if ($previousProcesses) {
                foreach ($previousProcesses as $previousProcess) {
                    $data = ProductionRecord::query()
                        ->where('part_number_id', $previousProcess->id)
                        ->where('record_type', 'entry')
                        ->whereDate('created_at', $date)
                        ->get();

                    foreach ($data as $record) {
                        $createdAt = Carbon::parse($record->created_at);
                        $hourKey = $createdAt->format('H:00');
                        if (in_array($hourKey, $timeHeaders)) {
                            $groupedPlans[$partNumber]['entries'][$hourKey] += $record->quantity;
                            $groupedPlans[$partNumber]['total_entries'] += $record->quantity;
                        }
                    }
                }
            }

            // Procesar salidas
            $exitRecords = ProductionRecord::query()
                ->where('part_number_id', $plan->part_number_id)
                ->where('record_type', 'exit')
                ->whereDate('created_at', $date)
                ->get();

            foreach ($exitRecords as $record) {
                $createdAt = Carbon::parse($record->created_at);
                $hourKey = $createdAt->format('H:00');
                if (in_array($hourKey, $timeHeaders)) {
                    $groupedPlans[$partNumber]['exits'][$hourKey] += $record->quantity;
                }
            }
        }

        usort($groupedPlans, function ($a, $b) {
            $lineCompare = strcmp($a['line_name'], $b['line_name']);
            if ($lineCompare !== 0) {
                return $lineCompare;
            }
            return strcmp($a['part_number'], $b['part_number']);
        });

        $records = array_values($groupedPlans);

        $data = [
            'effectiveProductionTimePerShift' => $effectiveProductionTimePerShift,
            'totalDowntimeMinutes' => $totalDowntimeMinutes,
            'totalDowntimeCount' => $totalDowntimeCount,
            'totalScrap' => $totalScrap,
            'totalHooksUsedPerShift' => $totalHooksUsedPerShift,
            'hangingRatePerShift' => $hangingRatePerShift,
            'averageJphPerShift' => $averageJphPerShift,
            'shift' => $shift,
            'date' => $date,
            'records' => $records,
            'timeHeaders' => $timeHeaders,
        ];

        $pdf = PDF::loadView('pdf.production-report', $data);
        $pdf->setPaper('letter', 'landscape');

        return $pdf->download('registro-produccion-' . $date . '-' . $shift->name . '.pdf');
    }
}
