<?php

namespace App\Http\Controllers;

use App\Events\ProductionRecordCreated;
use App\Models\FSO;
use App\Models\PartNumber;
use App\Models\ProductionPlan;
use App\Models\ProductionRecord;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionRecordController extends Controller
{
    /**
     * Show the form for scanning a label.
     */
    public function scanLabel(Request $request)
    {
        return view('production-records.scan-label');
    }

    /**
     * Store the scanned label data.
     */
    public function storeLabel(Request $request)
    {
        $validated = $request->validate([
            'scanInput' => 'required|string|max:20',
        ]);

        $orderNumber = substr($request->input('scanInput'), 0, 8);
        $sequence = substr($request->input('scanInput'), 8, 6);
        $standardPack = substr($request->input('scanInput'), 14, 6);
        // $partNumber = FSO::query()->selectRaw('TRIM(SPROD) AS part_number')->where('SORD', $orderNumber)->value('PART_NUMBER');

        $partNumber = PartNumber::where('number', FSO::query()->selectRaw('TRIM(SPROD) AS part_number')->where('SORD', $orderNumber)->value('PART_NUMBER'))->firstOrFail();
        $nextPartNumber = $partNumber->nextProcesses->first();

        if (!$nextPartNumber) {
            return redirect()->back()->withErrors(['error' => 'No hay procesos siguientes para este número de parte.']);
        }

        $productionPlan = ProductionPlan::query()
            ->where('part_number_id', $nextPartNumber->id)
            ->where('planned_date', Carbon::parse('2025-07-23')->format('Y-m-d'))
            ->firstOrFail();

        $productionRecord = ProductionRecord::create([
            'production_plan_id' => $productionPlan->id,
            'order_number' => $orderNumber,
            'part_number_id' => $partNumber->id,
            'sequence' => $sequence,
            'quantity' => $standardPack,
        ]);

        event(new ProductionRecordCreated());

        if ($productionPlan->produced_quantity === 0) {
            $status = Status::where('key', 'LIKE', 'in_progress')->first();
            $productionPlan->update(['produced_quantity' => $standardPack, 'status_id' => $status->id]);
        } else {
            $totalQuantity = $productionRecord->quantity + $standardPack;
            $productionPlan->update(['produced_quantity' => $totalQuantity]);
        }

        return redirect()->route('production-records.scan-label')->with('success', 'Etiqueta escaneada y almacenada correctamente.');
    }
}
