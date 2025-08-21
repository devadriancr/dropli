<?php

namespace App\Livewire\ProductionRecord;

use App\Models\ProductionRecord;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class PaintingProcessExit extends Component
{
    public $recentExitRecords;

    public function mount()
    {
        $this->loadRecentExits();
    }

    #[On('echo:material-exit-registered,ProductionRecord\MaterialExitRegistered')]
    public function refreshRecentExits()
    {
        $this->loadRecentExits();
    }

    public function loadRecentExits()
    {
        $twoAndAHalfHoursAgo = Carbon::now()->subHours(2)->subMinutes(30);

        $this->recentExitRecords = ProductionRecord::with(['partNumber'])
            ->where('record_type', 'exit')
            ->where('created_at', '>=', $twoAndAHalfHoursAgo)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.production-record.painting-process-exit');
    }
}
