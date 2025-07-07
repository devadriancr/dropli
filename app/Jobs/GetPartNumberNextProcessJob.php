<?php

namespace App\Jobs;

use App\Models\PartNumber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GetPartNumberNextProcessJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $partNumbers = PartNumber::query()->select('number')->orderBy('number', 'asc')->get();

        foreach ($partNumbers as $partNumber) {
            FetchPartNumberNextProcess::dispatch(
                $partNumber->number
            );
        }
    }
}
