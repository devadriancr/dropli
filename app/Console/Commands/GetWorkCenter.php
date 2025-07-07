<?php

namespace App\Console\Commands;

use App\Jobs\GetWorkCenterJob;
use Illuminate\Console\Command;

class GetWorkCenter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'infor:work-center';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The work centers are retrieved from Infor and stored in the system\'s database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info("Process GetWorkCenterJob is running at ". now());

        GetWorkCenterJob::dispatch();

        info("Process GetWorkCenterJob completed at ". now());
    }
}
