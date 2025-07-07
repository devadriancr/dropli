<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Project;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class StoreProjectJob implements ShouldQueue
{
    use Queueable;

    private $code;
    private $model;
    private $customer;

    /**
     * Create a new job instance.
     */
    public function __construct($code, $model, $customer)
    {
        $this->code = $code;
        $this->model = $model;
        $this->customer = $customer;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $customer = Customer::query()->where('code', $this->customer)->first();

        if ($customer !== null) {
            Project::updateOrCreate([
                'code' => $this->code,
            ],[
                'model' => $this->model,
                'customer_id' => $customer->id,
            ]);
        }
    }
}
