<?php

namespace App\Console\Commands;

use App\Jobs\GetProductionPlanJob;
use Illuminate\Console\Command;

class GetProductionPlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'infor:production-plan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The production plans are retrieved from Infor and stored in the system\'s database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info("Process GetProductionPlanJob is running at ". now());

        GetProductionPlanJob::dispatch();

        info("Process GetProductionPlanJob completed at ". now());
    }
}
