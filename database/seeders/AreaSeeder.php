<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Department;
use App\Models\WorkCenter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'name' => 'Pintura',
            'department_id' => $department->id,
        ]);

        $workCenter = WorkCenter::where('number', '141010')->first();

        $workCenter->update(['area_id' => $area->id]);
    }
}
