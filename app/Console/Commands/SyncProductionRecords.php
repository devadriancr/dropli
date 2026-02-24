<?php

namespace App\Console\Commands;

use App\Jobs\SyncProductionRecords as SyncProductionRecordsJob;
use Illuminate\Console\Command;

class SyncProductionRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:production-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command dispatches a job that registers production records in Infor.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info("Process SyncProductionRecords is running at ". now());

        SyncProductionRecordsJob::dispatch();

        info("Process SyncProductionRecords completed at ". now());
    }
}
