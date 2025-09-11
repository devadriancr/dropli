<?php

namespace Database\Seeders;

use App\Models\PartNumber;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PartNumberAttributesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributesData = [
            ['DGH943030B -AP', 'pieces_per_hook', 30],
            ['DGJ243030B -AP', 'pieces_per_hook', 30],
            ['DGJ643030 -AP', 'pieces_per_hook', 30],
            ['DGJ843030 -AP', 'pieces_per_hook', 30],
            ['BDWK43030 -AP', 'pieces_per_hook', 30],
            ['BDTS43030A -AP', 'pieces_per_hook', 30],
            ['BDTV43030A -AP', 'pieces_per_hook', 30],
            ['DA7R43030 -PA', 'pieces_per_hook', 30],
            ['DA7S43030A-PA', 'pieces_per_hook', 30],
            ['DGH934310A -AP', 'pieces_per_hook', 10],
            ['DGH934360A -AP', 'pieces_per_hook', 10],
            ['DGK934310A -AP', 'pieces_per_hook', 10],
            ['DGK934360A-AP', 'pieces_per_hook', 10],
            ['DA7H34310 -PA', 'pieces_per_hook', 10],
            ['DA7H34360 -PA', 'pieces_per_hook', 10],
            ['BDWK3480XC-AP', 'pieces_per_hook', 1],
            ['BDWP3480XB-AP', 'pieces_per_hook', 1],
            ['BDTS3480XC-AP', 'pieces_per_hook', 1],
            ['DA7H3480X', 'pieces_per_hook', 1],
            ['DGH928B00 -AP', 'pieces_per_hook', 1],
            ['DGJ428B00 -AP', 'pieces_per_hook', 1],
            ['DGK928B00 -AP', 'pieces_per_hook', 1],
            ['DGL428B00 -AP', 'pieces_per_hook', 1],
            ['BDTS28B00A -AP', 'pieces_per_hook', 1],
            ['BDWP28B00B -AP', 'pieces_per_hook', 1],
            ['DD1B28B00A-PA', 'pieces_per_hook', 1],
            ['DGJ428850 -AP', 'pieces_per_hook', 6],
            ['BDWP28850 -AP', 'pieces_per_hook', 6],
            ['BDTS56H1XA -AP', 'pieces_per_hook', 5],
            ['DA6A56H00 -PA', 'pieces_per_hook', 5],
            ['PEP81040X', 'pieces_per_hook', 8],
            ['PX131040X', 'pieces_per_hook', 8],
            ['P54G10400A-PA', 'pieces_per_hook', 8],
            ['PEDD10400B', 'pieces_per_hook', 8],
            ['DA6A5317Y', 'pieces_per_hook', 30],
            ['DA6A563AX', 'pieces_per_hook', 30],
            ['1044531-00-C -AP', 'pieces_per_hook', 4],
            ['1044581-00-D-AP', 'pieces_per_hook', 3],
            ['1044532-00-A-20 -AP', 'pieces_per_hook', 84],
            ['1044532-00-A-21 -AP', 'pieces_per_hook', 84],
            ['1044521-00-F-20 -AP', 'pieces_per_hook', 84],
            ['1044521-00-F-21 -AP', 'pieces_per_hook', 84],
            ['BDTS57540', 'pieces_per_hook', 46],
            ['BDTS57560A', 'pieces_per_hook', 80],
            ['BDTS57570A', 'pieces_per_hook', 80],
            ['DGH957540', 'pieces_per_hook', 44],
            ['DGH957560', 'pieces_per_hook', 80],
            ['DGH957570', 'pieces_per_hook', 80],
            ['DGH957510', 'pieces_per_hook', 80],
            ['DGH957520', 'pieces_per_hook', 80],
            ['DGH988265 -1', 'pieces_per_hook', 56],
            ['BDTS88265 -1', 'pieces_per_hook', 56],
            ['BDTS88285 -1', 'pieces_per_hook', 56],
            ['DNBE88265', 'pieces_per_hook', 56],
            ['BJDJ88265', 'pieces_per_hook', 56],
            ['BDTS56A9XY', 'pieces_per_hook', 20],
            ['A0243-22010', 'pieces_per_hook', 10],
            ['A0243-22011', 'pieces_per_hook', 10],
            ['A0243-22012', 'pieces_per_hook', 10],
            ['A0243-22013', 'pieces_per_hook', 10],
            ['5201104060D', 'pieces_per_hook', 8],
            ['5201204060D', 'pieces_per_hook', 8],
            ['5202504020A', 'pieces_per_hook', 24],
            ['5202604020A', 'pieces_per_hook', 24]
        ];

        $processed = 0;
        $errors = 0;

        foreach ($attributesData as $data) {
            try {
                $partNumberStr = trim($data[0]);
                $attributeKey = $data[1];
                $attributeValue = $data[2];

                // Buscar el número de parte
                $partNumber = PartNumber::where('number', $partNumberStr)->first();

                if (!$partNumber) {
                    Log::warning("PartNumber no encontrado: {$partNumberStr}");
                    $errors++;
                    continue;
                }

                // Asignar atributo al número de parte actual
                $partNumber->setCustomAttributeValue($attributeKey, $attributeValue);

                // Obtener procesos anteriores y asignar el mismo atributo
                $previousProcesses = $partNumber->previousProcesses()->get();

                foreach ($previousProcesses as $previousProcess) {
                    $previousProcess->setCustomAttributeValue($attributeKey, $attributeValue);
                }

                $processed++;
            } catch (\Exception $e) {
                Log::error("Error procesando {$data[0]}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->command->info("Seeder completado. Procesados: {$processed}, Errores: {$errors}");
    }
}
