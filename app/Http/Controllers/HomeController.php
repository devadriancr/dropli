<?php

namespace App\Http\Controllers;

use App\Models\DowntimeRecord;
use App\Models\ProductionPlan;
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
        $effectiveProductionTime = $start->diffInMinutes($end);

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
            'effectiveProductionTime',
            'totalDowntimeMinutes',
            'totalDowntimeCount',
            'totalScrap'
        ));
    }
}
