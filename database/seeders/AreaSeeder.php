<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Department;
use App\Models\WorkCenter;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el departamento (asumiendo que es el mismo para todas las áreas)
        $department = Department::where('name', 'Pintura')->first();

        if (!$department) {
            $this->command->error('Departamento "Pintura" no encontrado');
            return;
        }

        $areas = [
            [
                'name' => 'PAINT',
                'workcenters' => ['141010', '141060', '200500']
            ],
            [
                'name' => 'Pedal',
                'workcenters' => ['137010', '138650']
            ],
            [
                'name' => 'Front Lower',
                'workcenters' => ['138480', '138880', '139360']
            ],
            [
                'name' => 'Front Cross',
                'workcenters' => ['139150']
            ],
            [
                'name' => 'Torsion Beam',
                'workcenters' => ['138040', '138210', '138470']
            ],
            [
                'name' => 'Diff Mount',
                'workcenters' => ['139830']
            ],
            [
                'name' => 'Seal',
                'workcenters' => ['122830', '124160']
            ],
            [
                'name' => 'Oil Pan',
                'workcenters' => ['137030', '138490']
            ],
            [
                'name' => 'Bumper',
                'workcenters' => ['141090', '141200']
            ],
            [
                'name' => 'Extension',
                'workcenters' => ['141140', '141240']
            ]
        ];

        foreach ($areas as $areaData) {
            $area = Area::firstOrCreate(
                [
                    'name' => $areaData['name'],
                    'department_id' => $department->id
                ],
                [
                    'description' => $areaData['name']
                ]
            );

            $associatedCount = 0;
            foreach ($areaData['workcenters'] as $number) {
                $workCenter = WorkCenter::where('number', $number)->first();

                if ($workCenter) {
                    // Solo actualizar si el área es diferente
                    if ($workCenter->area_id !== $area->id) {
                        $workCenter->update(['area_id' => $area->id]);
                        $associatedCount++;
                    }
                } else {
                    $this->command->warn("WorkCenter {$number} no encontrado para el área {$areaData['name']}");
                }
            }
        }
    }
}
