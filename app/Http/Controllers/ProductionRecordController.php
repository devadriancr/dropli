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
            'scanInput.max'      => 'El código escaneado es demasiado largo',
        ]);

        $scanData = $validated['scanInput'];

        $redirect = redirect()->route('production-records.scan-label');

        // Validar longitud mínima
        if (strlen($scanData) < 14) {
            return $redirect->with([
                'message'     => 'Código de etiqueta inválido. Verifique que sea correcto.',
                'messageType' => 'error',
            ]);
        }

        // Fragmentar etiqueta
        $orderNumber  = substr($scanData, 0, 8);
        $sequence     = substr($scanData, 8, 6);
        $standardPack = substr($scanData, 14, 6);

        // Buscar número de parte en FSO
        $partNumberCode = FSO::query()
            ->selectRaw('TRIM(SPROD) AS part_number')
            ->where('SORD', $orderNumber)
            ->value('PART_NUMBER');

        if (!$partNumberCode) {
            return $redirect->with([
                'message'     => "No se encontró información para la orden: {$orderNumber}",
                'messageType' => 'error',
            ]);
        }

        // Buscar número de parte
        $partNumber = PartNumber::where('number', $partNumberCode)->first();
        if (! $partNumber) {
            return $redirect->with([
                'message'     => "Número de parte no encontrado: {$partNumberCode}",
                'messageType' => 'error',
            ]);
        }

        // Procesos siguientes
        $nextPart = $partNumber->nextProcesses->first();
        if (! $nextPart) {
            return $redirect->with([
                'message'     => 'No hay procesos siguientes configurados para este número de parte.',
                'messageType' => 'warning',
            ]);
        }

        // Turno y plan de producción
        $shift = Shift::getShift()->first();
        $today = Shift::getPlannedDate()->first();

        $plan = ProductionPlan::query()
            ->where('part_number_id', $nextPart->id)
            ->where('planned_date', $today)
            ->where('shift_id', $shift->id)
            ->first();

        if (! $plan) {
            return $redirect->with([
                'message'     => 'No se encontró un plan de producción activo para este proceso.',
                'messageType' => 'error',
            ]);
        }

        // Verificar duplicados
        $exists = ProductionRecord::query()
            ->where('production_plan_id', $plan->id)
            ->where('order_number', $orderNumber)
            ->where('part_number_id', $partNumber->id)
            ->where('sequence', $sequence)
            ->where('quantity', $standardPack)
            ->exists();

        if ($exists) {
            return $redirect->with([
                'message'     => 'Esta etiqueta ya ha sido procesada anteriormente.',
                'messageType' => 'warning',
            ]);
        }

        // Crear registro
        ProductionRecord::create([
            'production_plan_id' => $plan->id,
            'order_number'       => $orderNumber,
            'part_number_id'     => $partNumber->id,
            'sequence'           => $sequence,
            'quantity'           => $standardPack,
        ]);

        // Evento y actualización de producido
        event(new ProductionRecordCreated());

        if ($plan->produced_quantity == 0) {
            $status = Status::where('key', 'in_progress')->first();
            $plan->update([
                'produced_quantity' => $standardPack,
                'status_id'         => $status->id ?? $plan->status_id,
            ]);
        } else {
            $plan->increment('produced_quantity', intval($standardPack));
        }

        // Éxito con PRG
        return $redirect->with([
            'message'     => 'Etiqueta procesada correctamente',
            'messageType' => 'success',
        ]);
    }
}
