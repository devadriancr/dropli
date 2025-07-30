<?php

namespace App\Livewire\ProductionRecord;

use App\Models\ProductionPlan;
use App\Models\ProductionRecord;
use App\Models\Shift;
use Carbon\Carbon;
use Livewire\Component;

class PaintProductionRecord extends Component
{
    public $records = [];
    public $timeHeaders = [];
    public $shift;

    public function mount()
    {
        $this->fetchData();
    }

    public function fetchData()
    {
        // Obtener el turno actual y fecha planeada
        $this->shift = Shift::getShift();
        $plannedDate = Shift::getPlannedDate();

        if (!$this->shift) {
            return;
        }

        // Generar los encabezados de hora basados en el turno
        $this->generateTimeHeaders();

        // Obtener los registros de producción
        $productionRecords = ProductionRecord::query()
            ->with([
                'partNumber',
                'productionPlan.shift',
                'productionPlan.partNumber.standardPack',
                'productionPlan.partNumber.projects'
            ])
            ->whereHas('productionPlan', function ($q) use ($plannedDate) {
                $q->where('shift_id', $this->shift->id)
                    ->whereDate('planned_date', $plannedDate);
            })
            ->get();

        // Procesar los registros para la vista
        $this->processRecords($productionRecords);
    }

    protected function generateTimeHeaders()
    {
        $start = Carbon::parse($this->shift->start_time);
        $end = Carbon::parse($this->shift->end_time);

        // Generar las horas del turno (de inicio a fin-1 hora)
        while ($start->lt($end)) {
            $this->timeHeaders[] = $start->format('H:i');
            $start->addHour();
        }
    }

    protected function processRecords($productionRecords)
    {
        $groupedRecords = [];

        foreach ($productionRecords as $record) {
            $partNumber = $record->productionPlan->partNumber->number;
            $createdAt = Carbon::parse($record->created_at);
            $hourKey = $createdAt->format('H:00'); // Agrupar por hora

            if (!isset($groupedRecords[$partNumber])) {
                $groupedRecords[$partNumber] = [
                    'part_number' => $partNumber,
                    'standard_pack' => $record->productionPlan->partNumber->standardPack->name,
                    'standard_pack_quantity' => $record->productionPlan->partNumber->standard_pack_quantity,
                    'model' => $record->productionPlan->partNumber->projects->pluck('model')->implode(';'),
                    'entries' => array_fill_keys($this->timeHeaders, null),
                    'exits' => array_fill_keys($this->timeHeaders, null),
                ];
            }

            // Procesar entrada (quantity)
            $groupedRecords[$partNumber]['entries'][$hourKey] += $record->quantity;

            // Procesar salida (next process)
            $nextPartNumber = $record->partNumber->nextProcesses->first();
            if ($nextPartNumber) {
                $exitRecord = ProductionRecord::query()
                    ->whereHas('partNumber', function ($q) use ($nextPartNumber) {
                        $q->where('number', $nextPartNumber->number);
                    })
                    ->whereDate('created_at', $createdAt->toDateString())
                    ->first();

                if ($exitRecord) {
                    $exitHourKey = Carbon::parse($exitRecord->created_at)->format('H:00');
                    $groupedRecords[$partNumber]['exits'][$exitHourKey] += $exitRecord->quantity;
                }
            }
        }

        $this->records = array_values($groupedRecords);
    }

    public function render()
    {
        return view('livewire.production-record.paint-production-record');
    }
}
