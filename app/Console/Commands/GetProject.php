<?php

namespace App\Console\Commands;

use App\Jobs\GetProjectJob;
use Illuminate\Console\Command;

class GetProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'infor:project';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The projects are retrieved from Infor and stored in the system\'s database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info("Process GetProjectJob is running at ". now());

        GetProjectJob::dispatch();

        info("Process GetProjectJob completed at ". now());
    }
}
