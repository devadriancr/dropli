<?php

namespace App\Livewire\ProductionRecord;

use App\Models\ProductionPlan;
use App\Models\ProductionRecord;
use App\Models\Shift;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;

class PaintProductionRecord extends Component
{
    public $records = [];
    public $timeHeaders = [];
    public $shift;
    public bool $realTime = false;

    public function mount($realTime = false)
    {
        $this->realTime = $realTime;
        $this->fetchData();
    }

    #[On('refresh-production-records')]
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

        // Obtener los planes de producción
        $productionPlans = ProductionPlan::query()
            ->with([
                'partNumber.workCenter',
                'partNumber.standardPack',
                'partNumber.projects',
                'partNumber.nextProcesses',
                'productionRecords' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->where('shift_id', $this->shift->id)
            ->whereDate('planned_date', $plannedDate)
            ->get();

        // Procesar los registros para la vista
        $this->processPlans($productionPlans);
    }

    protected function generateTimeHeaders()
    {
        $start = Carbon::parse($this->shift->start_time);
        $end = Carbon::parse($this->shift->end_time);

        // Limpiar headers existentes
        $this->timeHeaders = [];

        // Generar las horas del turno (de inicio a fin-1 hora)
        while ($start->lt($end)) {
            $this->timeHeaders[] = $start->format('H:i');
            $start->addHour();
        }
    }

    protected function processPlans($productionPlans)
    {
        $groupedPlans = [];

        foreach ($productionPlans as $plan) {
            $partNumber = $plan->partNumber->number;

            if (!isset($groupedPlans[$partNumber])) {
                $hoursCount = count($this->timeHeaders);

                // Calcular la distribución del plan por horas
                $planDistribution = [];
                if ($hoursCount > 0) {
                    $baseValue = floor($plan->planned_quantity / $hoursCount);
                    $remainder = $plan->planned_quantity % $hoursCount;

                    // Llenar todas las horas con el valor base
                    $planDistribution = array_fill_keys($this->timeHeaders, $baseValue);

                    // Distribuir el resto en las primeras horas
                    $keys = array_keys($planDistribution);
                    for ($i = 0; $i < $remainder; $i++) {
                        $planDistribution[$keys[$i]]++;
                    }
                } else {
                    $planDistribution = array_fill_keys($this->timeHeaders, null);
                }

                $groupedPlans[$partNumber] = [
                    'work_center' => $plan->partNumber->workCenter->name,
                    'part_number' => $partNumber,
                    'standard_pack' => $plan->partNumber->standardPack->name,
                    'standard_pack_quantity' => $plan->partNumber->standard_pack_quantity,
                    'model' => $plan->partNumber->projects->pluck('model')->implode(';'),
                    'planned_quantity' => $plan->planned_quantity,
                    'produced_quantity' => $plan->produced_quantity,
                    'plan' => $planDistribution,
                    'entries' => array_fill_keys($this->timeHeaders, null),
                    'exits' => array_fill_keys($this->timeHeaders, null),
                ];
            }

            // Procesar los registros de producción asociados a este plan
            foreach ($plan->productionRecords as $record) {
                $createdAt = Carbon::parse($record->created_at);
                $hourKey = $createdAt->format('H:00');

                // Sumar a las entradas
                if (!isset($groupedPlans[$partNumber]['entries'][$hourKey])) {
                    $groupedPlans[$partNumber]['entries'][$hourKey] = 0;
                }
                $groupedPlans[$partNumber]['entries'][$hourKey] += $record->quantity;

                // Procesar salidas (next process)
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
                        if (!isset($groupedPlans[$partNumber]['exits'][$exitHourKey])) {
                            $groupedPlans[$partNumber]['exits'][$exitHourKey] = 0;
                        }
                        $groupedPlans[$partNumber]['exits'][$exitHourKey] += $exitRecord->quantity;
                    }
                }
            }
        }

        $this->records = array_values($groupedPlans);
    }

    public function render()
    {
        return view('livewire.production-record.paint-production-record');
    }
}
