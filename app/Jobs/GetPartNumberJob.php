<?php

namespace App\Jobs;

use App\Models\IIM;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GetPartNumberJob implements ShouldQueue
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
        $itemMaster = IIM::query()
            ->select('IPROD AS partNumber', 'IDESC AS partName', 'ICLAS AS itemClass', 'IREF04 AS project', 'IMPLC AS isObsolete', 'IMSPKT AS standardPack')
            // ->where('IMPLC', 'LIKE', 'OBSOLETE  ')
            ->get();

        foreach ($itemMaster as $key => $item) {
            StorePartNumberJob::dispatch(
                preg_replace('/[^a-zA-Z0-9\/\-\s]/', '', trim($item->partNumber)),
                preg_replace('/[^a-zA-Z0-9\/\-\s]/', '', trim($item->partName)),
                trim($item->itemClass),
                trim($item->project),
                trim($item->isObsolete),
                trim($item->standardPack)
            );
        }
    }
}
