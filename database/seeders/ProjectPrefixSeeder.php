<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectPrefix;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectPrefixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['J36W',  'BJS - BPN - BRB'],
            ['J36W',   'BAC - PED - BJV'],
            ['J03W', 'DA6 - DA7 - DA8'],
            ['J03W',   'P54 - DB1 - DD1'],
            ['J03W',   'DG7 - S51 - DGN'],
            ['J03G', 'DB7 - DD1 - DB7'],
            ['J59W', 'BDTS - BDTT'],
            ['J59W',   'BDW - BDTV - BEK'],
            ['J59W',   'BDWP - BDYS - BGV'],
            ['J59W',   'BHY - BJE - BJD - PX1 - PEP'],
            ['J59J', 'DGH - DGJ - DGK'],
            ['J59J',   'DGL - DGY - DRV - PYY'],
            ['J34A', 'VA40'],
            ['J34A',   'BDTS70234'],
            ['J34A',   'BDTS56A9X'],
            ['660B', '575 - 576'],
            ['660B',   '582 - 583'],
            ['J34H', 'VC67'],
            ['J34X', 'VC85'],
            ['920B', '573 - 520'],
            ['3Y',   '104 (FG)'],
            ['J03N', 'DNJ - DHM - DDD'],
            ['J03N', 'DA6 - PED'],
        ];

        foreach ($rows as [$projectCode, $prefixesString]) {
            $project = Project::where('model', $projectCode)->first();
            if (!$project) continue;

            $prefixes = array_map('trim', explode('-', $prefixesString));

            foreach ($prefixes as $prefix) {

                if ($prefix === '') continue;

                // Usar clave compuesta: prefix_code + project_id
                ProjectPrefix::firstOrCreate(
                    [
                        'prefix_code' => $prefix,
                        'project_id' => $project->id, // ¡Clave añadida!
                    ],
                    [
                        'description' => null, // Solo se establece al crear
                    ]
                );
            }
        }
    }
}
