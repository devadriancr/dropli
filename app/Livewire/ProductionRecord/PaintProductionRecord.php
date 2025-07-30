<?php

namespace App\Livewire\ProductionRecord;

use App\Models\ProductionPlan;
use App\Models\ProductionRecord;
use App\Models\Shift;
use Carbon\Carbon;
use Livewire\Component;

class PaintProductionRecord extends Component
{
    public $productionRecord = [];


    public function mount($productionRecord = null)
    {
        $this->productionRecord = $productionRecord ?? [];
        $this->fetchTable();
    }

    public function fetchTable()
    {
        $shift = Shift::getShift();
        $plannedDate = Shift::getPlannedDate();

        $this->productionRecord = ProductionRecord::query()
            ->with([
                'partNumber',
                'productionPlan.shift',
                'productionPlan.status',
                'productionPlan.partNumber',
                'productionPlan.partNumber.workCenter',
                'productionPlan.partNumber.standardPack',
                'productionPlan.partNumber.projects'
            ])
            ->whereHas('productionPlan', function ($q) use ($shift, $plannedDate) {
                $q->where('shift_id', $shift->id)
                    ->whereDate('planned_date', $plannedDate);
            })
            ->get();

        foreach ($this->productionRecord as $record) {
            // Datos de Columnas: Número de Parte, Paquete Estándar, Candidad de Paquete Estándar, Modelo
            dd(
                $record->productionPlan->partNumber->number,
                $record->productionPlan->partNumber->standardPack->name,
                $record->productionPlan->partNumber->standard_pack_quantity,
                $record->productionPlan->partNumber->projects->pluck('model')->implode(';'),
            );

            // Cantidad de Entrada
            dd(
                $record->quantity
            );

            // Cantidad de Salida
            $number = $record->partNumber->nextProcesses->first();
            $exit = ProductionRecord::query()
                ->with(['partNumber'])
                ->whereHas('partNumber', function ($q) use ($number) {
                    $q->where('number', $number->number);
                })
                ->first();
            dd(
                $exit->quantity
            );
        };
    }

    public function render()
    {
        return view('livewire.production-record.paint-production-record');
    }
}
