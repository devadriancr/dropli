<?php

namespace App\Jobs;

use App\Models\PartNumber;
use App\Models\PartNumberSequence;
use App\Models\WorkCenter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class StorePartNumberNextProcess implements ShouldQueue
{
    use Queueable;

    protected $partNumberSorted;

    /**
     * Create a new job instance.
     */
    public function __construct($partNumberSorted)
    {
        $this->partNumberSorted = $partNumberSorted;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->partNumberSorted as $part) {
            $currentPart = PartNumber::query()->where('number', $part->CHILD_PART)->first();
            $currentWorkCenter = WorkCenter::query()->where('number', $part->CHILD_WORK_CENTER)->first();
            $nextPart =  PartNumber::query()->where('number', $part->PARENT_PART)->first();
            $nextWorkCenter =  WorkCenter::query()->where('number', $part->PARENT_WORK_CENTER)->first();

            if (!$currentPart || !$nextPart || !$currentWorkCenter || !$nextWorkCenter) {
                continue; // Skip if any part or work center is not found
            }

            PartNumberSequence::updateOrCreate(
                [
                    'current_part_number_id' => $currentPart->id,
                    'next_part_number_id' => $nextPart->id
                ],
                [
                    'sequence_order' => 1,
                    'lead_time_hours' => null,
                    'is_active' => true
                ]
            );
        }
    }
}
