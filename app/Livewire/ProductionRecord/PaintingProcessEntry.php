<?php

namespace App\Livewire\ProductionRecord;

use App\Models\ProductionRecord;
use Livewire\Attributes\On;
use Livewire\Component;
use Carbon\Carbon;

class PaintingProcessEntry extends Component
{
    public $productionEntry;

    public function mount()
    {
        $this->loadRecords();
    }

    #[On('echo:production-record-created,ProductionRecordCreated')]
    public function refreshTable()
    {
        $this->loadRecords();
    }

    protected function loadRecords()
    {
        $oneHourAgo = Carbon::now()->subHours(2);

        $this->productionEntry = ProductionRecord::with('partNumber')
            ->where('created_at', '>=', $oneHourAgo)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.production-record.painting-process-entry');
    }
}
