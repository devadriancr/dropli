<?php

namespace App\Livewire\ProductionRecord;

use App\Models\ProductionRecord;
use Livewire\Attributes\On;
use Livewire\Component;

class PaintingProcessEntry extends Component
{
    public $productionEntry;

    public function mount()
    {
        $this->productionEntry = ProductionRecord::with('partNumber')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();
    }

    #[On('echo:production-record-created,ProductionRecordCreated')]
    public function refreshTable()
    {
        $this->productionEntry = ProductionRecord::with('partNumber')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();
    }

    public function render()
    {
        return view('livewire.production-record.painting-process-entry');
    }
}
