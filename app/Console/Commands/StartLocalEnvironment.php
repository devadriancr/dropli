<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class StartLocalEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'local:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inicia el Entorno Local';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando entorno local...');

        // Define los procesos
        $processes = [
            'Laravel Server' => new Process(['php', 'artisan', 'serve']),
            'Reverb' => new Process(['php', 'artisan', 'reverb:start']),
            'Npm' => new Process(['npm', 'run', 'dev']),
        ];

        foreach ($processes as $name => $process) {
            $process->setTimeout(null);
            $process->start();
            $this->info("✅ $name iniciado.");
        }

        $this->info('💻 Todos los servicios locales están corriendo.');

        while (true) {
            sleep(1);
        }

        return 0;
    }
}
