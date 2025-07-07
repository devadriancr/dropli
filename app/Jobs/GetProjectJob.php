<?php

namespace App\Jobs;

use App\Models\ZCC;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GetProjectJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $projects = ZCC::query()
            ->select(['CCCODE', 'CCDESC', 'CCNOT1'])
            ->where('CCTABL', 'SIRF4')
            ->get();

        foreach ($projects as $project) {
            StoreProjectJob::dispatch(
                trim($project->CCCODE),
                trim($project->CCDESC),
                trim($project->CCNOT1),
            );
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
