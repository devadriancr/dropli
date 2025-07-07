<?php

namespace App\Jobs;

use App\Models\StandardPack;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class StoreStandardPackJob implements ShouldQueue
{
    use Queueable;

    private $name;

    /**
     * Create a new job instance.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        StandardPack::firstOrCreate(['name' => $this->name]);
    }
}
