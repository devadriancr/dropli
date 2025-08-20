<?php

namespace App\Http\Controllers;

use App\Events\ProductionRecordCreated;
use App\Http\Requests\StoreExitScanRequest;
use App\Models\FSO;
use App\Models\PartNumber;
use App\Models\ProductionPlan;
use App\Models\ProductionRecord;
use App\Models\Shift;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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
    public function scanLabel()
    {
        return view('production-records.scan-label');
    }

    /**
     * Store the scanned label data.
     */
    public function storeLabel(Request $request)
    {
        $validated = $request->validate([
            'scanInput' => 'required|string|min:5|max:20',
            'orderNumber' => 'required|string|max:8',
            'sequence' => 'required|string|max:6',
            'quantity' => 'required|integer|min:1',
        ], [
            'scanInput.required' => 'Debe escanear una etiqueta',
            'scanInput.min' => 'El código escaneado es demasiado corto',
            'scanInput.max' => 'El código escaneado es demasiado largo',

            'quantity.required' => 'Indique la cantidad producida.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'La cantidad debe ser al menos 1.',
        ]);

        $barCode = $request->input('scanInput');
        $orderNumber = $request->input('orderNumber', '00000000');
        $sequence = $request->input('sequence', '000000');
        $quantity = $request->input('quantity', '000000');

        $redirect = redirect()->route('production-records.scan-label');

        // Validar longitud mínima
        if (strlen($barCode) < 14) {
            return $redirect->with('error', 'Código de etiqueta inválido. Verifique que sea correcto.');
        }

        // Buscar número de parte en FSO
        $orderPartNumber = FSO::query()->selectRaw('TRIM(SPROD) AS part_number')->where('SORD', $orderNumber)->value('PART_NUMBER');
        if (!$orderPartNumber) {
            return $redirect->with('error', "No se encontró información para la orden: {$orderNumber}");
        }

        // Buscar número de parte
        $partNumber = PartNumber::where('number', $orderPartNumber)->where('is_obsolete', false)->first();
        if (!$partNumber) {
            return $redirect->with('error', "Número de parte no encontrado: {$orderPartNumber}");
        }

        // Número de parte siguientes
        $nextPartNumber = $partNumber->nextProcesses->where('is_obsolete', false)->first();
        if (!$nextPartNumber) {
            return $redirect->with('warning', 'No hay procesos siguientes configurados para este número de parte.');
        }

        // Turno y plan de producción
        $shift = Shift::getShift()->first();
        $today = Shift::getPlannedDate();

        $productionPlan = ProductionPlan::query()
            ->where('part_number_id', $nextPartNumber->id)
            ->where('planned_date', $today)
            ->where('shift_id', $shift->id)
            ->first();

        if (!$productionPlan) {
            return $redirect->with('warning', 'No se encontró un plan de producción para este número de parte.');
        }

        // Verificar duplicados
        $exists = ProductionRecord::query()
            ->where('production_plan_id', $productionPlan->id)
            ->where('order_number', $orderNumber)
            ->where('part_number_id', $partNumber->id)
            ->where('sequence', $sequence)
            ->where('quantity', $quantity)
            ->exists();

        if ($exists) {
            return $redirect->with('error', 'Esta etiqueta ya ha sido escaneada anteriormente.');
        }

        // Crear registro
        ProductionRecord::create([
            'production_plan_id' => $productionPlan->id,
            'order_number' => $orderNumber,
            'part_number_id' => $partNumber->id,
            'sequence' => $sequence,
            'quantity' => $quantity,
        ]);

        // Evento y actualización de producido
        event(new ProductionRecordCreated());

        if ($productionPlan->produced_quantity == 0) {
            $status = Status::where('key', 'in_progress')->first();
            $productionPlan->update([
                'produced_quantity' => $quantity,
                'status_id' => $status->id ?? $productionPlan->status_id,
            ]);
        } else {
            $productionPlan->increment('produced_quantity', intval($quantity));
        }

        return $redirect->with('success', 'Etiqueta registrada correctamente');
    }

    /**
     * Show the form for entering a part number.
     */
    public function partNumberEntry()
    {
        return view('production-records.part-number-entry');
    }

    /**
     * Store the entered part number data.
     */
    public function storePartNumber(Request $request)
    {
        $validated = $request->validate([
            'partNumber' => 'required|string|max:20',
            'quantity' => 'required|integer|min:1',
        ], [
            'partNumber.required' => 'Debe ingresar un número de parte',
            'partNumber.max' => 'El número de parte es demasiado largo',
            'quantity.required' => 'Indique la cantidad producida.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'La cantidad debe ser al menos 1.',
        ]);

        $partNumberInput = $request->input('partNumber');
        $quantity = $request->input('quantity');

        $redirect = redirect()->route('production-records.part-number-entry');

        // Buscar número de parte
        $partNumber = PartNumber::where('number', $partNumberInput)->where('is_obsolete', false)->first();
        if (!$partNumber) {
            return $redirect->with('error', "Número de parte no encontrado: {$partNumberInput}");
        }

        // Número de parte siguientes
        $nextPartNumber = $partNumber->nextProcesses->where('is_obsolete', false)->first();
        if (!$nextPartNumber) {
            return $redirect->with('warning', 'No hay procesos siguientes configurados para este número de parte.');
        }

        // Turno y plan de producción
        $shift = Shift::getShift()->first();
        $today = Shift::getPlannedDate();

        $productionPlan = ProductionPlan::query()
            ->where('part_number_id', $nextPartNumber->id)
            ->where('planned_date', $today)
            ->where('shift_id', $shift->id)
            ->first();

        if (!$productionPlan) {
            return $redirect->with('warning', 'No se encontró un plan de producción para este número de parte.');
        }

        // Crear registro
        ProductionRecord::create([
            'production_plan_id' => $productionPlan->id,
            'order_number' => '00000000',
            'part_number_id' => $partNumber->id,
            'sequence' => '000000',
            'quantity' => $quantity,
        ]);

        // Evento y actualización de producido
        event(new ProductionRecordCreated());

        if ($productionPlan->produced_quantity == 0) {
            $status = Status::where('key', 'in_progress')->first();
            $productionPlan->update([
                'produced_quantity' => $quantity,
                'status_id' => $status->id ?? $productionPlan->status_id,
            ]);
        } else {
            $productionPlan->increment('produced_quantity', intval($quantity));
        }

        return $redirect->with('success', 'Número de parte registrado correctamente');
    }

    public function exitScan(): View
    {
        return view('production-records.exit-scan');
    }

    public function storeExit(StoreExitScanRequest $request)
    {
        $exitCode = $request->input('exitCode');
        $orderNumber = $request->input('orderNumber');
        $sequence = $request->input('sequence');
        $quantity = $request->input('quantity');

        if (strlen($exitCode) <= 20) {

            $orderPartNumber = FSO::query()->selectRaw('TRIM(SPROD) AS part_number')->where('SORD', $orderNumber)->value('PART_NUMBER');
            if (!$orderPartNumber) {
                return redirect()->route('production-records.exit-scan')->with('error', "No se encontró información para la orden: {$orderNumber}");
            }

            $partNumber = PartNumber::query()->where('number', $orderPartNumber)->where('is_obsolete', false)->first();
            if (!$partNumber) {
                return redirect()->route('production-records.exit-scan')->with('error', "Número de parte no encontrado: {$orderPartNumber}");
            }

            $previousPartNumber = $partNumber->previousProcesses->where('is_obsolete', false)->first();
            if (!$previousPartNumber) {
                return  redirect()->route('production-records.exit-scan')->with('warning', 'No hay procesos anterior configurado para este número de parte.');
            }

            $shift = Shift::getShift()->first();
            $today = Shift::getPlannedDate();

            $productionPlan = ProductionPlan::query()
                ->where('part_number_id', $partNumber->id)
                ->where('planned_date', $today)
                ->where('shift_id', $shift->id)
                ->first();

            if (!$productionPlan) {
                return redirect()->route('production-records.exit-scan')->with('warning', 'No se encontró un plan de producción para este número de parte.');
            }

            $exists = ProductionRecord::query()
                ->where('production_plan_id', $productionPlan->id)
                ->where('part_number_id', $partNumber->id)
                ->where('order_number', $orderNumber)
                ->where('sequence', $sequence)
                ->where('quantity', $quantity)
                ->exists();
            if ($exists) {
                return redirect()->route('production-records.exit-scan')->with('error', 'Esta etiqueta ya ha sido escaneada anteriormente.');
            }

            $productionRecord = ProductionRecord::create([
                'production_plan_id' => $productionPlan->id,
                'part_number_id' => $partNumber->id,
                'order_number' => $orderNumber,
                'sequence' => $sequence,
                'quantity' => $quantity,
                 'exited' => true,
            ]);

             return redirect()->route('production-records.exit-scan')->with('success', 'Etiqueta registrada correctamente');
        }
    }
}
