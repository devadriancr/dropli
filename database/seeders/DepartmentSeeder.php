<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::create(['code' => '20', 'name' => 'Mazda']);
        Department::create(['code' => '11', 'name' => 'Estampado']);
        Department::create(['code' => '12', 'name' => 'Carrocería']);
        Department::create(['code' => '13', 'name' => 'Chasis']);
        Department::create(['code' => '14', 'name' => 'Pintura']);
        Department::create(['code' => '40', 'name' => 'Proveedor']);
    }
}
