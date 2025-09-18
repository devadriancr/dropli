<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $list = [
            ['key' => 'pending', 'label' => 'Pendiente', 'color' => '#E7000B'],
            ['key' => 'in_progress', 'label' => 'En proceso', 'color' => '#F0B100'],
            ['key' => 'completed', 'label' => 'Terminado', 'color' => '#00A63E'],
        ];

        foreach ($list as $row) {
            Status::updateOrCreate(
                [
                    'key' => $row['key']
                ],
                $row
            );
        }
    }
}
