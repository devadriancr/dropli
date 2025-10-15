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

        // Generar los encabezados de hora basados en el turno (anclados a la fecha planeada)
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
        // Anclar fechas a la fecha planeada para evitar confusiones con turnos que crucen medianoche
        $start = Carbon::parse("{$this->date} {$this->shift->start_time}");
        $end = Carbon::parse("{$this->date} {$this->shift->end_time}");

        // Si el turno cruza medianoche, sumar 1 día al end
        if ($start->gt($end)) {
            $end = $end->copy()->addDay();
        }

        $this->timeHeaders = [];
        $current = $start->copy();

        while ($current->lt($end)) {
            // Usamos formato H:00 para alinear con las keys usadas en el resto del código
            $this->timeHeaders[] = $current->format('H:00');
            $current->addHour();
        }
    }

    /**
     * Determina el estado de cada columna con la ventana amarilla de 2.5 horas (150 minutos).
     * - amarillo: dt <= now && dt >= now - 150min  (incluyente)
     * - verde: dt < now - 150min  (aunque sea 1 minuto antes)
     * - futuro: dt > now
     */
    protected function computeColumnStates()
    {
        // Reconstruir datetimes anclados a la fecha de turno
        $start = Carbon::parse("{$this->date} {$this->shift->start_time}");
        $end = Carbon::parse("{$this->date} {$this->shift->end_time}");
        if ($start->gt($end)) {
            $end = $end->copy()->addDay();
        }

        $headerDateTimes = [];
        $current = $start->copy();
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

            if (!isset($groupedPlans[$partNumber])) {
                $groupedPlans[$partNumber] = [
                    'line_name' => $plan->partNumber->previousProcesses->first()->workCenter->area->name ?? '-',
                    'part_number' => $partNumber,
                    'standard_pack' => $plan->partNumber->standardPack->name ?? '-',
                    'standard_pack_quantity' => $plan->partNumber->standard_pack_quantity ?? null,
                    'model' => $plan->partNumber->projects->pluck('model')->implode(';'),
                    'planned_quantity' => $plan->planned_quantity,
                    'produced_quantity' => $plan->produced_quantity,
                    'entries' => array_fill_keys($this->timeHeaders, null),
                    'exits' => array_fill_keys($this->timeHeaders, null),
                    'total_entries' => 0,
                ];
            }

            $previousProcesses = $plan->partNumber->previousProcesses;

            if ($previousProcesses) {
                $records = [];
                foreach ($previousProcesses as $previousProcess) {
                    $data = ProductionRecord::query()
                        ->where('part_number_id', $previousProcess->id)
                        ->where('record_type', 'entry')
                        ->whereDate('created_at', $this->date)
                        ->get();

                    if ($data->isNotEmpty()) {
                        $records = array_merge($records, $data->all());
                    }
                }

                foreach ($records as $record) {
                    $createdAt = Carbon::parse($record->created_at);
                    $hourKey = $createdAt->format('H:00');

                    if (in_array($hourKey, $this->timeHeaders)) {
                        // Sumar al acumulado por hora
                        $groupedPlans[$partNumber]['entries'][$hourKey] += $record->quantity;
                        // Sumar al total acumulado
                        $groupedPlans[$partNumber]['total_entries'] += $record->quantity;
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
