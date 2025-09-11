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
        $department = Department::where('name', 'Pintura')->first();

        $area = Area::create([
            'name' => 'PAINT',
            'department_id' => $department->id,
        ]);

        $workCenterNumbers = ['141010', '141060', '200500'];

        foreach ($workCenterNumbers as $number) {
            $workCenter = WorkCenter::where('number', $number)->first();

            if ($workCenter) {
                $workCenter->update(['area_id' => $area->id]);
            }
        }
    }
}
