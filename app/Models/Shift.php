<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'abbreviation',
        'name',
        'start_time',
        'end_time',
        'description'
    ];

    public function productionPlan(): HasMany
    {
        return $this->hasMany(ProductionPlan::class, 'shift_id');
    }

    public static function getShift($dateTime = null)
    {
        $currentTime = $dateTime ? $dateTime->format('H:i:s') : now()->format('H:i:s');
        $shifts = Shift::all();

        foreach ($shifts as $shift) {
            $start = $shift->start_time;
            $end = $shift->end_time;

            if ($start < $end) {
                if ($currentTime >= $start && $currentTime < $end) {
                    return $shift;
                }
            } else {
                if ($currentTime >= $start || $currentTime < $end) {
                    return $shift;
                }
            }
        }

        return null;
    }

    public static function getPlannedDate($dateTime = null)
    {
        $now = $dateTime ?: now();
        $shift = Shift::getShift($now);

        if (!$shift) {
            return $now->toDateString();
        }

        $currentTime = $now->format('H:i:s');
        $start = $shift->start_time;
        $end = $shift->end_time;

        if ($start > $end && $currentTime < $end) {
            return $now->copy()->subDay()->toDateString();
        }

        return $now->toDateString();
    }

    public static function getPreviousShift($dateTime = null)
    {
        $currentShift = Shift::getShift($dateTime);

        if (!$currentShift) {
            return null;
        }

        $shifts = Shift::orderBy('start_time')->get();
        $index = $shifts->search(function ($shift) use ($currentShift) {
            return $shift->id == $currentShift->id;
        });

        if ($index === false) {
            return null;
        }

        $previousIndex = $index - 1;
        if ($previousIndex < 0) {
            $previousIndex = $shifts->count() - 1;
        }

        return $shifts[$previousIndex];
    }

    public static function getPreviousPlannedDate($dateTime = null)
    {
        $now = $dateTime ?: now();

        // Obtenemos la fecha planificada del momento actual
        $currentPlannedDate = Carbon::parse(Shift::getPlannedDate($now));

        $currentShift = Shift::getShift($now);

        if (!$currentShift) {
            return $currentPlannedDate->toDateString();
        }

        $shifts = Shift::orderBy('start_time')->get();

        // Si el turno actual es el primero del día (ej. Diurno),
        // el turno anterior pertenece al día de ayer.
        if ($currentShift->id == $shifts->first()->id) {
            return $currentPlannedDate->subDay()->toDateString();
        }

        // Si es cualquier otro turno (ej. Nocturno),
        // el turno anterior pertenece a la misma fecha de producción actual.
        return $currentPlannedDate->toDateString();
    }
}
