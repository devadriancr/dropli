<?php

namespace Database\Seeders;

use App\Models\DowntimeReason;
use App\Models\DowntimeRecord;
use App\Models\DowntimeType;
use App\Models\WorkCenter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DowntimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tipos de paro
        $types = [
            'Normal',
            'Anormal',
            'Planeado',
        ];

        $downtimeTypes = [];

        foreach ($types as $type) {
            $downtimeTypes[$type] = DowntimeType::create([
                'name' => $type,
                'description' => $type . ' downtime type',
            ]);
        }

        // 2. Razones de paro
        $reasons = [
            [
                'code' => 'TURNCHANGE',
                'name' => 'Cambio de Turno',
                'description' => 'Línea detenida por cambio de turno de operadores',
                'type' => 'Normal'
            ],
            [
                'code' => 'MATERIAL',
                'name' => 'Falta de Material',
                'description' => 'Línea detenida por falta de material',
                'type' => 'Anormal'
            ],
            [
                'code' => 'MAINT',
                'name' => 'Mantenimiento',
                'description' => 'Línea detenida por mantenimiento programado o no programado',
                'type' => 'Planeado'
            ],
        ];

        foreach ($reasons as $reason) {
            DowntimeReason::create([
                'code' => $reason['code'],
                'name' => $reason['name'],
                'description' => $reason['description'],
                'downtime_type_id' => $downtimeTypes[$reason['type']]->id,
            ]);
        }

        // 3. (Opcional) Insertar un registro de paro ficticio
        if (WorkCenter::exists()) {
            DowntimeRecord::create([
                'downtime_reason_id' => DowntimeReason::first()->id,
                'work_center_id' => WorkCenter::first()->id,
                'minutes' => 15,
                'start_time' => now()->subMinutes(15),
                'end_time' => now(),
            ]);
        }
    }
}
