<?php

namespace App\Http\Controllers;

use App\Events\ProductionRecordCreated;
use App\Models\FSO;
use App\Models\PartNumber;
use App\Models\ProductionPlan;
use App\Models\ProductionRecord;
use App\Models\Shift;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionRecordController extends Controller
{
    /**
     * Display a listing of the production records.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $productionRecords = ProductionRecord::query()
            ->with(['productionPlan', 'partNumber', 'partNumber.workCenter'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    // Buscar por número de estación/centro de trabajo
                    $q->whereHas('partNumber.workCenter', function ($workCenterQuery) use ($search) {
                        $workCenterQuery->where('number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    })
                        // Buscar por número de parte
                        ->orWhereHas('partNumber', function ($partQuery) use ($search) {
                            $partQuery->where('number', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        })
                        // Buscar por secuencia (si existe en tu modelo)
                        ->orWhere('sequence', 'like', "%{$search}%")
                        // Buscar por cantidad
                        ->orWhere('quantity', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('production-records.index')->with([
            'productionRecords' => $productionRecords,
            'search' => $search,
        ]);
    }

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
            return view('production-records.scan-label', [
                'message' => 'Código de etiqueta inválido. Verifique que sea correcto.',
                'messageType' => 'error'
            ]);
        }

        $orderNumber = substr($request->input('scanInput'), 0, 8);
        $sequence = substr($request->input('scanInput'), 8, 6);
        $standardPack = substr($request->input('scanInput'), 14, 6);

        $partNumberCode = FSO::query()->selectRaw('TRIM(SPROD) AS part_number')->where('SORD', $orderNumber)->value('PART_NUMBER');

        if (!$partNumberCode) {
            return view('production-records.scan-label', [
                'message' => "No se encontró información para la orden: {$orderNumber}",
                'messageType' => 'error'
            ]);
        }

        $partNumber = PartNumber::where('number', $partNumberCode)->first();

        if (!$partNumber) {
            return view('production-records.scan-label', [
                'message' => "Número de parte no encontrado: {$partNumberCode}",
                'messageType' => 'error'
            ]);
        }

        $nextPartNumber = $partNumber->nextProcesses->first();

        if (!$nextPartNumber) {
            return view('production-records.scan-label', [
                'message' => 'No hay procesos siguientes configurados para este número de parte.',
                'messageType' => 'warning'
            ]);
        }

        $shift = Shift::getCurrentShift()->first();

        $productionPlan = ProductionPlan::query()
            ->where('part_number_id', $nextPartNumber->id)
            ->where('planned_date', Carbon::now()->format('Y-m-d'))
            ->where('shift_id', $shift->id)
            ->first();

        if (!$productionPlan) {
            return view('production-records.scan-label', [
                'message' => 'No se encontró un plan de producción activo para este proceso.',
                'messageType' => 'error'
            ]);
        }

        $existingRecord = ProductionRecord::query()
            ->where('production_plan_id', $productionPlan->id)
            ->where('order_number', $orderNumber)
            ->where('part_number_id', $partNumber->id)
            ->where('sequence', $sequence)
            ->where('quantity', $standardPack)
            ->first();

        if ($existingRecord) {
            return view('production-records.scan-label', [
                'message' => 'Esta etiqueta ya ha sido procesada anteriormente.',
                'messageType' => 'warning'
            ]);
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

        return view('production-records.scan-label', [
            'message' => 'Etiqueta procesada correctamente',
            'messageType' => 'success'
        ]);
    }
}
