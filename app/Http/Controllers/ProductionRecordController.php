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
        ], [
            'scanInput.required' => 'Debe escanear una etiqueta',
            'scanInput.max' => 'El código escaneado es demasiado largo'
        ]);

        $scanData = $request->input('scanInput');

        if (strlen($scanData) < 14) {
            return redirect()->back()->with('error', 'Código de etiqueta inválido. Verifique que sea correcto.');
        }

        $orderNumber = substr($request->input('scanInput'), 0, 8);
        $sequence = substr($request->input('scanInput'), 8, 6);
        $standardPack = substr($request->input('scanInput'), 14, 6);

        $partNumberCode = FSO::query()->selectRaw('TRIM(SPROD) AS part_number')->where('SORD', $orderNumber)->value('PART_NUMBER');

        if (!$partNumberCode) {
            return redirect()->back()->with('error', "No se encontró información para la orden: {$orderNumber}");
        }

        $partNumber = PartNumber::where('number', $partNumberCode)->first();

        if (!$partNumber) {
            return redirect()->back()->with('error', "Número de parte no encontrado: {$partNumberCode}");
        }

        $nextPartNumber = $partNumber->nextProcesses->first();

        if (!$nextPartNumber) {
            return redirect()->back()->with('warning', 'No hay procesos siguientes configurados para este número de parte.');
        }

        $productionPlan = ProductionPlan::query()
            ->where('part_number_id', $nextPartNumber->id)
            // ->where('planned_date', Carbon::now()->format('Y-m-d'))
            ->first();

        if (!$productionPlan) {
            return redirect()->back()->with('error', 'No se encontró un plan de producción activo para este proceso.');
        }

        $existingRecord = ProductionRecord::query()
            ->where('production_plan_id', $productionPlan->id)
            ->where('order_number', $orderNumber)
            ->where('part_number_id', $partNumber->id)
            ->where('sequence', $sequence)
            ->where('quantity', $standardPack)
            ->first();

        if ($existingRecord) {
            return redirect()->back()->with('warning', 'Esta etiqueta ya ha sido procesada anteriormente.');
        }

        $productionRecord = ProductionRecord::create([
            'production_plan_id' => $productionPlan->id,
            'order_number' => $orderNumber,
            'part_number_id' => $partNumber->id,
            'sequence' => $sequence,
            'quantity' => $standardPack,
        ]);

        // Disparar evento
        event(new ProductionRecordCreated());

        // Actualizar cantidad producida en el plan
        if ($productionPlan->produced_quantity == 0) {
            $status = Status::where('key', 'LIKE', 'in_progress')->first();
            $productionPlan->update([
                'produced_quantity' => $standardPack,
                'status_id' => $status->id ?? $productionPlan->status_id
            ]);
        } else {
            $totalQuantity = $productionPlan->produced_quantity + intval($standardPack);
            $productionPlan->update(['produced_quantity' => $totalQuantity]);
        }

        return redirect()->route('production-records.scan-label')->with('success', 'Etiqueta procesada correctamente');
    }
}
