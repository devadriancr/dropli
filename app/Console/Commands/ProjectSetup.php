<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProjectSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dropli:setup {--fresh : Ejecuta migrate:fresh en lugar de migrate}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configuración completa del proyecto Dropli: migraciones, seeders y comprobaciones del sistema';

    protected $steps = [
        ['Migraciones', 'migrate', []],
        ['Ejecutando DatabaseSeeder', 'db:seed', ['--class' => 'DatabaseSeeder']],
        ['Ejecutando CustomerSeeder', 'db:seed', ['--class' => 'CustomerSeeder']],
        ['Ejecutando ShiftSeeder', 'db:seed', ['--class' => 'ShiftSeeder']],
        ['Ejecutando StatusesSeeder', 'db:seed', ['--class' => 'StatusesSeeder']],
        ['Obteniendo información del proyecto', 'infor:project', []],
        ['Ejecutando ProjectPrefixSeeder', 'db:seed', ['--class' => 'ProjectPrefixSeeder']],
        ['Obteniendo información del item class', 'infor:item-class', []],
        ['Obteniendo información del standard pack', 'infor:standard-pack', []],
        ['Ejecutando DepartmentSeeder', 'db:seed', ['--class' => 'DepartmentSeeder']],
        ['Obteniendo información del work center', 'infor:work-center', []],
        ['Ejecutando AreaSeeder', 'db:seed', ['--class' => 'AreaSeeder']],
        ['Obteniendo información del part number', 'infor:part-number', []],
        ['Obteniendo información del next process', 'infor:next-process', []],
        ['Ejecutando DowntimeSeeder', 'db:seed', ['--class' => 'DowntimeSeeder']],
        ['Ejecutando ScrapSeeder', 'db:seed', ['--class' => 'ScrapSeeder']],
        ['Ejecutando PartNumberAttributesSeeder', 'db:seed', ['--class' => 'PartNumberAttributesSeeder']],
        ['Ejecutando RolePermissionSeeder', 'db:seed', ['--class' => 'RolePermissionSeeder']],
    ];

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $total = count($this->steps);

        $this->info("=========================================================");
        $this->info("       🚀 INICIANDO CONFIGURACIÓN DE PROYECTO DROPLI      ");
        $this->info("=========================================================");
        $this->newLine();
        $this->comment("Total de pasos a ejecutar: {$total}");

        // Inicia barra de progreso
        $this->output->progressStart($total);

        foreach ($this->steps as [$description, $command, $arguments]) {
            // 1. Ajuste para migraciones fresh
            if ($command === 'migrate' && $this->option('fresh')) {
                $command = 'migrate:fresh';
            }

            // 2. Determinar el color y prefijo de la sección
            $prefix = '';
            $colorTag = 'fg=white;bg=blue'; // Default/Otros

            if (str_starts_with($command, 'migrate')) {
                $colorTag = 'fg=white;bg=cyan';
                $prefix = '🔨 DB Migraciones: ';
            } elseif (str_starts_with($command, 'db:seed')) {
                $colorTag = 'fg=black;bg=yellow';
                $prefix = '🌱 DB Seeder: ';
            } elseif (str_starts_with($command, 'infor:')) {
                $colorTag = 'fg=white;bg=green';
                $prefix = '📡 Sincronización API: ';
            }

            // 3. Mostrar el paso actual con color de fondo (El "Overlay")
            $this->line(""); // Espacio de separación
            $this->line("<{$colorTag}> " . $prefix . $description . " </>");
            $this->line("");

            // 4. Ejecutar el comando
            $this->call($command, $arguments);

            // 5. Mostrar confirmación de éxito
            $this->line("    <info> [OK] </info> Comando ejecutado exitosamente: <comment>{$command}</comment>");

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->newLine();
        $this->info('✅ ¡Configuración de Dropli completada con éxito!');
    }
}
