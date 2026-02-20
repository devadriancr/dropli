<?php

namespace App\Http\Controllers;

use App\Events\ProductionRecord\MaterialEntryRegistered;
use App\Events\ProductionRecord\MaterialExitRegistered;
use App\Http\Requests\StoreEntryScanRequest;
use App\Http\Requests\StoreExitScanRequest;
use App\Models\ECL;
use App\Models\FSO;
use App\Models\HPO;
use App\Models\PartNumber;
use App\Models\ProductionPlan;
use App\Models\ProductionRecord;
use App\Models\Shift;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
     * Show the form for editing the specified resource.
     */
    public function edit(ProductionRecord $productionRecord)
    {
        return view('production-records.edit', compact('productionRecord'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductionRecord $productionRecord)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ], [
            'quantity.required' => 'La cantidad es obligatoria',
            'quantity.integer' => 'La cantidad debe ser un número entero',
            'quantity.min' => 'La cantidad debe ser al menos 1',
        ]);

        try {
            DB::beginTransaction();

            $oldQuantity = $productionRecord->quantity;
            $newQuantity = $validated['quantity'];

            $productionRecord->update([
                'quantity' => $newQuantity
            ]);

            Log::debug("Production record updated", [
                'production_record_id' => $productionRecord->id,
                'production_plan_id' => $productionRecord->production_plan_id,
                'record_type' => $productionRecord->record_type,
                'planned_quantity' => $productionRecord->productionPlan ? $productionRecord->productionPlan->planned_quantity : null,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
            ]);

            if ($productionRecord->record_type === 'exit' && $productionRecord->productionPlan) {
                $delta = $newQuantity - $oldQuantity;
                if ($delta > 0) {
                    $productionRecord->productionPlan->increment('produced_quantity', $delta);
                } elseif ($delta < 0) {
                    $decrement = min(abs($delta), (int) $productionRecord->productionPlan->produced_quantity);
                    if ($decrement > 0) {
                        $productionRecord->productionPlan->decrement('produced_quantity', $decrement);
                    }
                }
            }

            DB::commit();

            return redirect()->route('production-records.index')
                ->with('success', 'Cantidad actualizada y plan de producción sincronizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
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

        $orderPartNumber = null;

        if (str_contains($entryCode, ',')) {
            $parts = explode(',', $entryCode);
            if (count($parts) >= 3) {
                $rawPart = trim($parts[2]);
                $orderPartNumber = str_replace("'", "-", $rawPart);
            }
        } elseif (strlen($entryCode) >= 20 && strlen($entryCode) <= 25) {
            $orderPartNumber = FSO::getPartNumberByOrder($orderNumber);
        } elseif (strlen($entryCode) > 25 && str_starts_with($entryCode, '1')) {
            $orderPartNumber = HPO::getPartNumberByOrder($orderNumber);
        }

        if (!$orderPartNumber) {
            return $redirect->with('error', "No se pudo determinar el número de parte para la orden: {$orderNumber}");
        }

        $partNumber = PartNumber::where('number', $orderPartNumber)
            ->where('is_obsolete', false)
            ->first();
        if (!$partNumber) {
            return $redirect->with('error', "Número de parte no encontrado o está obsoleto: {$orderPartNumber}");
        }

        $orderAlreadyExists = ProductionRecord::productionRecordExists($orderNumber, $sequence, $quantity, 'entry');
        if ($orderAlreadyExists) {
            return $redirect->with('error', 'Esta etiqueta ya ha sido escaneada anteriormente.');
        }

        $nextPartNumbers = $partNumber->nextProcesses->where('is_obsolete', false);
        if ($nextPartNumbers->isEmpty()) {
            return $redirect->with('warning', 'No hay procesos siguientes configurados para este número de parte.');
        }

        $shift = Shift::getShift();
        $today = Shift::getPlannedDate();
        $productionPlan = null;

        foreach ($nextPartNumbers as $nextPart) {
            $productionPlan = ProductionPlan::getProductionPlan($nextPart->id, $today, $shift->id);
            if ($productionPlan) {
                break;
            }
        }

        if ($productionPlan === null) {
            $productionPlan = ProductionPlan::store(null, $nextPart->id, 0, $today, $shift->id);
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
                'status_id' => $status->id ?? $productionPlan->status_id,
            ]);
        }

        return $redirect->with('success', 'Etiqueta registrada correctamente');
    }

    /**
     * Show the form for manual part number entry.
     */
    public function partNumberEntry(Request $request)
    {
        if (Auth::check()) {
            $workCenterIds = Auth::user()->workCenters->pluck('id');
            $partNumbers = PartNumber::whereIn('work_center_id', $workCenterIds)
                ->where('is_obsolete', false)
                ->orderBy('number')
                ->get();
        } else {
            $partNumbers = PartNumber::with(['workCenter.area'])
                ->whereHas('workCenter.area', function ($query) {
                    $query->where('name', 'PAINT');
                })
                ->where('is_obsolete', false)
                ->orderBy('number')
                ->get();
        }

        // Guardar la URL de origen en la sesión
        $origin = $request->get('origin');
        if ($origin) {
            session(['part_number_entry_origin' => $origin]);
        }

        return view('production-records.part-number-entry', compact('partNumbers'));
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

        $origin = session('part_number_entry_origin');

        $isEntry = $origin ? (str_contains($origin, 'entry-scan') || !str_contains($origin, 'exit-scan')) : true;

        $redirect = $origin ? redirect($origin) : redirect()->route('production-records.part-number-entry');

        $partNumber = PartNumber::where('number', $partNumberInput)->where('is_obsolete', false)->first();
        if (!$partNumber) {
            return $redirect->with('error', "Número de parte no encontrado: {$partNumberInput}");
        }

        $shift = Shift::getShift();
        $today = Shift::getPlannedDate();
        $statusInProgress = Status::where('key', 'in_progress')->first();

        $productionPlan = ProductionPlan::getProductionPlan($partNumber->id, $today, $shift->id);
        if ($productionPlan === null) {
            $productionPlan = ProductionPlan::store(null, $partNumber->id, 0, $today, $shift->id);
        }

        if ($isEntry) {
            $previous = $partNumber->previousProcesses->where('is_obsolete', false)->first();
            if (!$previous) {
                return $redirect->with('warning', 'No hay procesos anteriores para ' . $partNumber->number);
            }

            ProductionRecord::store($productionPlan->id, '00000000', $previous->id, '000000', $quantity, 'entry');

            event(new MaterialEntryRegistered());
        } else {
            ProductionRecord::store($productionPlan->id, '00000000', $partNumber->id, '000000', $quantity, 'exit');
            event(new MaterialExitRegistered());
        }
        $productionPlan->increment('produced_quantity', intval($quantity));
        $productionPlan->update([
            'status_id' => $statusInProgress->id ?? $productionPlan->status_id
        ]);

        session()->forget('part_number_entry_origin');

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

        $redirect = redirect()->route('production-records.exit-scan');

        $orderAlreadyExists = ProductionRecord::productionRecordExists($orderNumber, $sequence, $quantity, 'exit');
        if ($orderAlreadyExists) {
            return $redirect->with('error', 'Esta etiqueta ya ha sido escaneada anteriormente.');
        }

        if (strlen($exitCode) >= 20 && strlen($exitCode) <= 25) {
            $orderPartNumber = FSO::getPartNumberByOrder($orderNumber);
        } elseif (strlen($exitCode) >= 30) {
            $orderPartNumber = ECL::getFinalPartNumber($orderNumber);
        } else {
            $orderPartNumber = null;
        }

        if (!$orderPartNumber) {
            return $redirect->with('error', "No se encontró información para la orden: {$orderNumber}");
        }

        $partNumber = PartNumber::query()->where('number', $orderPartNumber)->where('is_obsolete', false)->first();
        if (!$partNumber) {
            return $redirect->with('error', "Número de parte no encontrado: {$orderPartNumber}");
        }

        $shift = Shift::getShift();
        $today = Shift::getPlannedDate();

        $productionPlan = ProductionPlan::getProductionPlan($partNumber->id, $today, $shift->id);

        if ($productionPlan === null) {
            return $redirect->with('warning', 'No se encontró un plan de producción para este número de parte.');
        }

        $exists = ProductionRecord::productionPlanExists($productionPlan->id, $orderNumber, $partNumber->id, $sequence, $quantity, 'exit');
        if ($exists) {
            return $redirect->with('error', 'Esta etiqueta ya ha sido escaneada anteriormente.');
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

        return $redirect->with('success', 'Etiqueta registrada correctamente');
    }
}
