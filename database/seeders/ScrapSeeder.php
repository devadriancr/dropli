<?php

namespace Database\Seeders;

use App\Models\ScrapCategory;
use App\Models\ScrapReason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScrapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear la categoría
        $category = ScrapCategory::create([
            'name' => 'Defectos en la pintura',
            'description' => 'Categoría que contiene defectos relacionados con la pintura'
        ]);

        // Lista de razones con código y nombre
        $reasons = [
            ['code' => '32', 'name' => 'Pintura negra (bajo espesor,mala adherencia)'],
            ['code' => '33', 'name' => 'Contaminación'],
            ['code' => '34', 'name' => 'Pinhole'],
            ['code' => '35', 'name' => 'Grumo'],
            ['code' => '36', 'name' => 'Crater'],
            ['code' => '37', 'name' => 'Mal retrabajo'],
            ['code' => '38', 'name' => 'Mala apariencia (incusión de material en la pintura, blanqueamiento, manchas)'],
            ['code' => '39', 'name' => 'Falso contacto en pieza (falta de pintura)'],
        ];

        // Crear las razones asociadas a la categoría
        foreach ($reasons as $reason) {
            ScrapReason::create([
                'code' => $reason['code'],
                'name' => $reason['name'],
                'scrap_category_id' => $category->id,
                'description' => null, // Puedes agregar descripción si quieres
            ]);
        }
    }
}
