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
        ['Ejecutando CustomerSeeder', 'db:seed', ['--class' => 'CustomerSeeder']],
        ['Ejecutando ShiftSeeder', 'db:seed', ['--class' => 'ShiftSeeder']],
        ['Ejecutando StatusesSeeder', 'db:seed', ['--class' => 'StatusesSeeder']],
        ['Obteniendo información del proyecto', 'info:project', []],
        ['Ejecutando ProjectPrefixSeeder', 'db:seed', ['--class' => 'ProjectPrefixSeeder']],
        ['Obteniendo información del item class', 'infor:item-class', []],
        ['Obteniendo información del standard pack', 'infor:standard-pack', []],
        ['Ejecutando DepartmentSeeder', 'db:seed', ['--class' => 'DepartmentSeeder']],
        ['Obteniendo información del work center', 'infor:work-center', []],
        ['Obteniendo información del part number', 'infor:part-number', []],
        ['Obteniendo información del next process', 'infor:next-process', []],
    ];

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $total = count($this->steps);
        $this->info("🔄 Iniciando setup de Dropli: {$total} pasos");

        // Inicia barra de progreso
        $this->output->progressStart($total);

        foreach ($this->steps as [$description, $command, $arguments]) {
            // Ajuste para migraciones fresh
            if ($command === 'migrate' && $this->option('fresh')) {
                $command = 'migrate:fresh';
            }

            $this->line("👉 {$description}...");
            $this->call($command, $arguments);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->newLine();
        $this->info('✅ ¡Configuración de Dropli completada con éxito!');
    }
}
