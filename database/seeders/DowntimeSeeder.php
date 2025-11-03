<?php

namespace Database\Seeders;

use App\Models\DowntimeReason;
use App\Models\DowntimeType;
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
                'description' => ucfirst($type) . ' downtime type',
            ]);
        }

        // 2. Razones de paro para tipo "Anormal"
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
                'name' => $reasonName,
                'downtime_type_id' => $downtimeTypes['Anormal']->id,
            ]);
        }

        // 3. Razones de paro para tipo "Planeado"
        $plannedReasons = [
            'Chorei de inicio',
            'Break primero',
            'Break segundo',
            'Comedor',
            'Box lunch',
            'Simulacros',
            'Limpieza de equipos',
            'Mantenimiento preventivo',
            'Junta informativa mensual',
            'Faltante de material',
            'Cambio de turno'
        ];

        foreach ($plannedReasons as $reasonName) {
            DowntimeReason::create([
                'name' => $reasonName,
                'downtime_type_id' => $downtimeTypes['Planeado']->id,
            ]);
        }
    }
}
