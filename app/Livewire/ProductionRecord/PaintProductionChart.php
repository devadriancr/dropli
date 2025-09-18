<?php

namespace App\Livewire\ProductionRecord;

use App\Models\ProductionPlan;
use App\Models\Shift;
use Illuminate\Support\Str;
use Livewire\Component;

class PaintProductionChart extends Component
{
    public string|null $chartId = null;
    public array $labels = [];
    public array $data = [];
    public array $chartData = [];

    public $date;
    public $shift;

    public function __construct()
    {
        $this->chartId = Str::ulid();
    }

    public function mount(): void
    {
        $this->refreshGraph();
    }

    public function refreshGraph()
    {
        $this->shift = Shift::getShift();
        $this->date = Shift::getPlannedDate();

        $this->fetchChartData();
    }

    public function fetchChartData(): void
    {
        $productionPlans = ProductionPlan::with(['partNumber'])
            ->where('planned_date', $this->date)
            ->where('shift_id', $this->shift->id)
            ->get();

        $groupedData = $productionPlans->groupBy('partNumber.number')->map(function ($plans) {
            return [
                'part_number' => $plans->first()->partNumber->number,
                'part_name' => $plans->first()->partNumber->name,
                'planned_quantity' => $plans->sum('planned_quantity'),
                'produced_quantity' => $plans->sum('produced_quantity'),
            ];
        })->values();

        $this->labels = $groupedData->pluck('part_number')->toArray();
        $this->data = [
            $groupedData->pluck('planned_quantity')->toArray(),
            $groupedData->pluck('produced_quantity')->toArray()
        ];
    }

    public function render()
    {
        return view('livewire.production-record.paint-production-chart');
    }
}
