<?php

namespace App\Livewire\ProductionRecord;

use App\Models\ProductionRecord;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class PaintingProcessExit extends Component
{
    public $productionExit = [];
    public bool $realTime = true;

    public function mount($productionExit = null)
    {
        $this->productionExit = $productionExit ?? [];
        $this->fetchTable();
    }

    #[On('refresh-table')]
    public function refreshTable()
    {
        $this->fetchTable();
    }

    public function fetchTable()
    {
        $oneMinuteAgo = Carbon::now()->subMinute();

        $this->productionExit = ProductionRecord::with([
            'partNumber',
            'productionPlan.partNumber',
            'status',
            'user'
        ])
            ->where('created_at', '<=', $oneMinuteAgo)
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

    }

    public function render()
    {
        return view('livewire.production-record.painting-process-exit');
    }
}
