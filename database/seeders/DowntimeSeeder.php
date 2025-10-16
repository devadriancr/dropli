<?php

namespace Database\Seeders;

use App\Models\DowntimeReason;
use App\Models\DowntimeRecord;
use App\Models\DowntimeType;
use App\Models\WorkCenter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
                'description' => ucfirst($type) . ' downtime type',
            ]);
        }

        // 2. Razones de paro (solo tipo "Anormal" por ahora)
        $anormalReasons = [
            'Tensión del drive B',
            'Tensión del drive A',
            'Troll dañado (carga)',
            'Gancho NG',
            'Pieza fuera de posición en carga',
            'Charola sanitaria dañada',
            'Paro en la entrada del horno',
            'Bajas temperaturas en el horno de curado',
            'Falla de rectificador',
            'Falla de bomba (fosfato)',
            'Falla de bomba (pintura)',
            'Falla de bomba (desengrase)',
        ];

        foreach ($anormalReasons as $reasonName) {
            DowntimeReason::create([
                // 'code' => Str::slug($reasonName, '_'),
                'name' => $reasonName,
                'description' => 'Razón de paro anormal: ' . $reasonName,
                'downtime_type_id' => $downtimeTypes['Anormal']->id,
            ]);
        }

        // 3. Registro de paro ficticio (opcional)
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
