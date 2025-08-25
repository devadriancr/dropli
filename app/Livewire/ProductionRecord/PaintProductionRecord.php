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
    public $date;
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
        $this->shift = Shift::getShift()->first();
        $this->date = Shift::getPlannedDate();

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
                'productionRecords'
            ])
            ->where('shift_id', $this->shift->id)
            ->whereDate('planned_date', $this->date)
            ->orderBy('part_number_id', 'asc')
            ->get();

        // Procesar los registros para la vista
        $this->processPlans($productionPlans);
    }

    protected function generateTimeHeaders()
    {
        $start = Carbon::parse($this->shift->start_time);
        $end = Carbon::parse($this->shift->end_time);

        $end = $start->gt($end) ? $end->copy()->addDay() : $end;

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

                $planDistribution = [];
                if ($hoursCount > 0) {
                    $standardPackQuantity = $plan->partNumber->standard_pack_quantity;
                    $plannedQuantity = $plan->planned_quantity;

                    if ($plannedQuantity > 0 && $standardPackQuantity > 0) {
                        $baseQuantity = floor($plannedQuantity / $hoursCount);

                        $adjustedBase = floor($baseQuantity / $standardPackQuantity) * $standardPackQuantity;

                        $distributedTotal = $adjustedBase * $hoursCount;
                        $remainder = $plannedQuantity - $distributedTotal;

                        $planDistribution = array_fill_keys($this->timeHeaders, $adjustedBase);

                        $keys = array_keys($planDistribution);
                        $remainingPacks = ceil($remainder / $standardPackQuantity);

                        for ($i = 0; $i < $remainingPacks && $i < $hoursCount; $i++) {
                            $toAdd = min($standardPackQuantity, $remainder);
                            $planDistribution[$keys[$i]] += $toAdd;
                            $remainder -= $toAdd;

                            if ($remainder <= 0) break;
                        }
                    } else {
                        $planDistribution = array_fill_keys($this->timeHeaders, null);
                    }
                } else {
                    $planDistribution = array_fill_keys($this->timeHeaders, null);
                }

                $groupedPlans[$partNumber] = [
                    'line_name' => $plan->partNumber->previousProcesses->first()->workCenter->area->name ?? '-',
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

            $previousProcess = $plan->partNumber->previousProcesses->first();

            if ($previousProcess) {
                $entryRecords = ProductionRecord::query()
                    ->where('part_number_id', $previousProcess->id)
                    ->where('record_type', 'entry')
                    ->whereDate('created_at', $this->date)
                    ->get();

                foreach ($entryRecords as $record) {
                    $createdAt = Carbon::parse($record->created_at);
                    $hourKey = $createdAt->format('H:00');

                    if (in_array($hourKey, $this->timeHeaders)) {
                        $groupedPlans[$partNumber]['entries'][$hourKey] += $record->quantity;
                    }
                }
            }

            $exitRecords = ProductionRecord::query()
                ->where('part_number_id', $plan->part_number_id)
                ->where('record_type', 'exit')
                ->whereDate('created_at', $this->date)
                ->get();

            foreach ($exitRecords as $record) {
                $createdAt = Carbon::parse($record->created_at);
                $hourKey = $createdAt->format('H:00');

                if (in_array($hourKey, $this->timeHeaders)) {
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

        $this->records = array_values($groupedPlans);
    }

    public function render()
    {
        return view('livewire.production-record.paint-production-record');
    }
}
