<?php

namespace App\Livewire\ProductionRecord;

use App\Models\ProductionRecord;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class PaintingProcessExit extends Component
{
    public $paintingExitRecords;

    public function mount()
    {
        $this->loadPaintingExitRecords();
    }

    public function refreshPaintingExitTable()
    {
        $this->loadPaintingExitRecords();
    }

    public function loadPaintingExitRecords()
    {
        $twoAndAHalfHours = Carbon::now()->addHours(2)->addMinutes(30);

        $this->paintingExitRecords = ProductionRecord::with([
            'partNumber',
        ])
            ->where('record_type', 'exit')
            ->where('created_at', '<=', $twoAndAHalfHours)
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.production-record.painting-process-exit');
    }
}
