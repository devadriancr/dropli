<?php

namespace App\Models;

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
        $shifts = self::all();

        foreach ($shifts as $shift) {
            $start = $shift->start_time;
            $end = $shift->end_time;

            // Si el turno NO cruza medianoche (ejemplo: 08:00 - 20:00)
            if ($start < $end) {
                if ($currentTime >= $start && $currentTime < $end) {
                    return $shift;
                }
            }
            // Si el turno SÍ cruza medianoche (ejemplo: 20:00 - 08:00)
            else {
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
        $shift = self::getShift($now);

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
}
