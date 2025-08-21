<?php

namespace App\Livewire\ProductionRecord;

use App\Models\ProductionRecord;
use Livewire\Attributes\On;
use Livewire\Component;
use Carbon\Carbon;

class PaintingProcessEntry extends Component
{
    public $recentEntryRecords;

    public function mount()
    {
        $this->loadRecentEntries();
    }

    #[On('echo:material-entry-registered,ProductionRecord\MaterialEntryRegistered')]
    public function refreshRecentEntries()
    {
        $this->loadRecentEntries();
    }

    protected function loadRecentEntries()
    {
        $twoAndAHalfHoursAgo = Carbon::now()->subHours(2)->subMinutes(30);

        $this->recentEntryRecords = ProductionRecord::with('partNumber')
            ->where('record_type', 'entry')
            ->where('created_at', '>=', $twoAndAHalfHoursAgo)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.production-record.painting-process-entry');
    }
}
