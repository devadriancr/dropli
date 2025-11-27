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

    public $columnsState = [];

    public $start;
    public $end;

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
        $this->date = Shift::getPlannedDate();

        if (!$this->shift) {
            return;
        }

        $this->start = Carbon::parse("{$this->date} {$this->shift->start_time}");
        $this->end = Carbon::parse("{$this->date} {$this->shift->end_time}");

        if ($this->start->gt($this->end)) {
            $this->end->addDay();
        }

        $this->generateTimeHeaders();
        $this->computeColumnStates();

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
            ->whereHas('partNumber.workCenter.area', function ($query) {
                $query->where('name', 'PAINT');
            })
            ->orderBy('part_number_id', 'asc')
            ->get();

        // Procesar los registros para la vista
        $this->processPlans($productionPlans);
    }

    protected function generateTimeHeaders()
    {
        $this->timeHeaders = [];
        $current = $this->start->copy();

        while ($current->lt($this->end)) {
            $this->timeHeaders[] = $current->format('H:00');
            $current->addHour();
        }
    }

    protected function computeColumnStates()
    {
        $headerDateTimes = [];
        $current = $this->start->copy();
        foreach ($this->timeHeaders as $header) {
            $headerDateTimes[$header] = $current->copy();
            $current->addHour();
        }

        $now = Carbon::now();
        $yellowStart = $now->copy()->subMinutes(210);

        $this->columnsState = [];
        foreach ($headerDateTimes as $header => $dt) {
            if ($dt->lte($now) && $dt->gte($yellowStart)) {
                $this->columnsState[$header] = 'yellow';
            } elseif ($dt->lt($yellowStart)) {
                $this->columnsState[$header] = 'green';
            } else {
                $this->columnsState[$header] = 'future';
            }
        }
    }

    protected function processPlans($productionPlans)
    {
        $groupedPlans = [];

        foreach ($productionPlans as $plan) {
            $partNumber = $plan->partNumber->number;

            // Obtener el nombre de la línea de manera segura
            $lineName = '-';
            if ($plan->partNumber->previousProcesses && $plan->partNumber->previousProcesses->isNotEmpty()) {
                $firstPreviousProcess = $plan->partNumber->previousProcesses->first();
                if ($firstPreviousProcess->workCenter && $firstPreviousProcess->workCenter->area) {
                    $lineName = $firstPreviousProcess->workCenter->area->name;
                }
            }

            if (!isset($groupedPlans[$partNumber])) {
                $groupedPlans[$partNumber] = [
                    'line_name' => $lineName,
                    'part_number' => $partNumber,
                    'standard_pack' => $plan->partNumber->standardPack->name ?? '-',
                    'standard_pack_quantity' => $plan->partNumber->standard_pack_quantity ?? null,
                    'model' => $plan->partNumber->projects->pluck('model')->implode(';'),
                    'planned_quantity' => $plan->planned_quantity,
                    'produced_quantity' => $plan->produced_quantity,
                    'entries' => array_fill_keys($this->timeHeaders, null),
                    'exits' => array_fill_keys($this->timeHeaders, null),
                    'total_exits' => 0,
                ];
            }

            // Procesar registros de entrada (entries)
            $previousProcesses = $plan->partNumber->previousProcesses;

            if ($previousProcesses) {
                $records = [];
                foreach ($previousProcesses as $previousProcess) {
                    $data = ProductionRecord::query()
                        ->where('part_number_id', $previousProcess->id)
                        ->where('record_type', 'entry')
                        ->whereBetween('created_at', [$this->start, $this->end])
                        ->get();

                    $records = array_merge($records, $data->all());
                }

                foreach ($records as $record) {
                    $createdAt = Carbon::parse($record->created_at);
                    $hourKey = $createdAt->format('H:00');

                    if (in_array($hourKey, $this->timeHeaders)) {
                        $groupedPlans[$partNumber]['entries'][$hourKey] += $record->quantity;
                    }
                }
            }

            // Procesar registros de salida (exits)
            $exitRecords = ProductionRecord::query()
                ->where('part_number_id', $plan->part_number_id)
                ->where('record_type', 'exit')
                ->whereBetween('created_at', [$this->start, $this->end])
                ->get();

            foreach ($exitRecords as $record) {
                $createdAt = Carbon::parse($record->created_at);
                $hourKey = $createdAt->format('H:00');

                if (in_array($hourKey, $this->timeHeaders)) {
                    $groupedPlans[$partNumber]['exits'][$hourKey] += $record->quantity;
                    $groupedPlans[$partNumber]['total_exits'] += $record->quantity;
                }
            }
        }

        // TAMBIÉN AGREGAR REGISTROS SIN PRODUCTION_PLAN (igual que en el PDF)
        $additionalProductionRecords = ProductionRecord::with(['partNumber'])
            ->where('record_type', 'entry')
            ->whereBetween('created_at', [$this->start, $this->end])
            ->whereDoesntHave('productionPlan')
            ->get();

        foreach ($additionalProductionRecords as $record) {
            $partNumber = $record->partNumber->number;

            if (!isset($groupedPlans[$partNumber])) {
                $groupedPlans[$partNumber] = [
                    'line_name' => $record->partNumber->workCenter->area->name ?? '-',
                    'part_number' => $partNumber,
                    'standard_pack' => $record->partNumber->standardPack->name ?? '-',
                    'standard_pack_quantity' => $record->partNumber->standard_pack_quantity ?? null,
                    'model' => $record->partNumber->projects->pluck('model')->implode(';'),
                    'planned_quantity' => 0,
                    'produced_quantity' => 0,
                    'entries' => array_fill_keys($this->timeHeaders, 0),
                    'exits' => array_fill_keys($this->timeHeaders, 0),
                    'total_exits' => 0,
                ];
            }

            $createdAt = Carbon::parse($record->created_at);
            $hourKey = $createdAt->format('H:00');
            if (in_array($hourKey, $this->timeHeaders)) {
                $groupedPlans[$partNumber]['entries'][$hourKey] += $record->quantity;
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
