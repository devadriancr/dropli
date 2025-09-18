<?php

namespace App\Console\Commands;

use App\Jobs\GetPartNumberNextProcessJob;
use Illuminate\Console\Command;

class GetPartNumberNextProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
     protected $signature = 'infor:next-process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The item numbers of the following process are retrieved from Infor and stored in the system\'s database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info("Process GetPartNumberNextProcessJob is running at ". now());

        GetPartNumberNextProcessJob::dispatch();

        info("Process GetPartNumberNextProcessJob completed at ". now());
    }
}
