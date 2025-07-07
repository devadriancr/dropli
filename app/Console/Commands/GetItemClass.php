<?php

namespace App\Console\Commands;

use App\Jobs\GetItemClassJob;
use Illuminate\Console\Command;

class GetItemClass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'infor:item-class';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The item classes are retrieved from Infor and stored in the system\'s database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info("Process GetItemClassJob is running at ". now());

        GetItemClassJob::dispatch();

        info("Process GetItemClassJob completed at ". now());
    }
}
