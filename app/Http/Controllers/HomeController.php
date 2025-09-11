<?php

namespace App\Http\Controllers;

use App\Models\DowntimeRecord;
use App\Models\PartNumber;
use App\Models\ProductionPlan;
use App\Models\ProductionRecord;
use App\Models\ScrapRecord;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $shift = Shift::getShift();
        $date = Shift::getPlannedDate();

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
            $partNumber = PartNumber::find($partNumberId);

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
            ? round($totalHooksUsedPerShift / $effectiveProductionTimePerShift,2)
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
            'averageJphPerShift'
        ));
    }
}
