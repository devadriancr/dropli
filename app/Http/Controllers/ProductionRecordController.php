<?php

namespace App\Http\Controllers;

use App\Events\ProductionRecord\MaterialEntryRegistered;
use App\Events\ProductionRecord\MaterialExitRegistered;
use App\Http\Requests\StoreEntryScanRequest;
use App\Http\Requests\StoreExitScanRequest;
use App\Models\FSO;
use App\Models\PartNumber;
use App\Models\ProductionPlan;
use App\Models\ProductionRecord;
use App\Models\Shift;
use App\Models\Status;
use Illuminate\Http\Request;
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
    public function entryScan()
    {
        return view('production-records.entry-scan');
    }

    /**
     * Store the scanned label data.
     */
    public function storeEntry(StoreEntryScanRequest $request)
    {
        $entryCode = $request->input('entryCode');
        $orderNumber = $request->input('orderNumber');
        $sequence = $request->input('sequence');
        $quantity = $request->input('quantity');

        $redirect = redirect()->route('production-records.entry-scan');

        if (strlen($entryCode) < 14) {
            return $redirect->with('error', 'Código de etiqueta inválido. Verifique que sea correcto.');
        }

        $orderPartNumber = FSO::getPartNumberByOrder($orderNumber);
        if (!$orderPartNumber) {
            return $redirect->with('error', "No se encontró información para la orden: {$orderNumber}");
        }

        $partNumber = PartNumber::where('number', $orderPartNumber)->where('is_obsolete', false)->first();
        if (!$partNumber) {
            return $redirect->with('error', "Número de parte no encontrado: {$orderPartNumber}");
        }

        $nextPartNumbers = $partNumber->nextProcesses->where('is_obsolete', false);
        if ($nextPartNumbers->isEmpty()) {
            return $redirect->with('warning', 'No hay procesos siguientes configurados para este número de parte.');
        }

        $shift = Shift::getShift()->first();
        $today = Shift::getPlannedDate();
        $productionPlan = null;

        foreach ($nextPartNumbers as $nextPart) {
            $productionPlan = ProductionPlan::getProductionPlan($nextPart->id, $today, $shift->id);
            if ($productionPlan) {
                break;
            }
        }
        if (!$productionPlan) {
            return $redirect->with('warning', 'No se encontró un plan de producción para este número de parte.');
        }

        $exists = ProductionRecord::productionPlanExists($productionPlan->id, $orderNumber, $partNumber->id, $sequence, $quantity, 'entry');
        if ($exists) {
            return $redirect->with('error', 'Esta etiqueta ya ha sido escaneada anteriormente.');
        }

        ProductionRecord::store($productionPlan->id, $orderNumber, $partNumber->id, $sequence, $quantity, 'entry');

        event(new MaterialEntryRegistered());

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
        event(new MaterialEntryRegistered());

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

    /**
     * Show the form for scanning an exit label.
     */
    public function exitScan(): View
    {
        return view('production-records.exit-scan');
    }

    /**
     * Store the exit scan data.
     */
    public function storeExit(StoreExitScanRequest $request)
    {
        $exitCode = $request->input('exitCode');
        $orderNumber = $request->input('orderNumber');
        $sequence = $request->input('sequence');
        $quantity = $request->input('quantity');

        if (strlen($exitCode) <= 20) {

            $orderPartNumber = FSO::getPartNumberByOrder($orderNumber);
            if (!$orderPartNumber) {
                return redirect()->route('production-records.exit-scan')->with('error', "No se encontró información para la orden: {$orderNumber}");
            }

            $partNumber = PartNumber::query()->where('number', $orderPartNumber)->where('is_obsolete', false)->first();
            if (!$partNumber) {
                return redirect()->route('production-records.exit-scan')->with('error', "Número de parte no encontrado: {$orderPartNumber}");
            }

            $previousPartNumber = $partNumber->previousProcesses->where('is_obsolete', false)->first();
            if (!$previousPartNumber) {
                return redirect()->route('production-records.exit-scan')->with('warning', 'No hay procesos anterior configurado para este número de parte.');
            }

            $shift = Shift::getShift()->first();
            $today = Shift::getPlannedDate();

            $productionPlan = ProductionPlan::getProductionPlan($partNumber->id, $today, $shift->id);
            if (!$productionPlan) {
                return redirect()->route('production-records.exit-scan')->with('warning', 'No se encontró un plan de producción para este número de parte.');
            }

            $exists = ProductionRecord::productionPlanExists($productionPlan->id, $orderNumber, $partNumber->id, $sequence, $quantity, 'exit');
            if ($exists) {
                return redirect()->route('production-records.exit-scan')->with('error', 'Esta etiqueta ya ha sido escaneada anteriormente.');
            }

            ProductionRecord::store($productionPlan->id, $orderNumber, $partNumber->id, $sequence, $quantity, 'exit');

            event(new MaterialExitRegistered());

            if ($productionPlan->produced_quantity == 0) {
                $status = Status::where('key', 'in_progress')->first();
                $productionPlan->update([
                    'produced_quantity' => $quantity,
                    'status_id' => $status->id ?? $productionPlan->status_id,
                ]);
            } else {
                $productionPlan->increment('produced_quantity', intval($quantity));
            }

            return redirect()->route('production-records.exit-scan')->with('success', 'Etiqueta registrada correctamente');
        }
    }
}
