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
            // Actualizar solo la cantidad del registro de producción
            $productionRecord->update([
                'quantity' => $validated['quantity']
            ]);

            return redirect()->route('production-records.index')
                ->with('success', 'Cantidad actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la cantidad: ' . $e->getMessage());
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

        $orderAlreadyExists = ProductionRecord::productionRecordExists($orderNumber, $sequence, $quantity, 'entry');
        if ($orderAlreadyExists) {
            return $redirect->with('error', 'Esta etiqueta ya ha sido escaneada anteriormente.');
        }

        if (strlen($entryCode) >= 20 && strlen($entryCode) <= 25) {
            $orderPartNumber = FSO::getPartNumberByOrder($orderNumber);
        } elseif (strlen($entryCode) > 25 && str_starts_with($entryCode, '1')) {
            $orderPartNumber = HPO::getPartNumberByOrder($orderNumber);
        } else {
            $orderPartNumber = null;
        }

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

        // if ($productionPlan == null) {
        //     return $redirect->with('warning', 'No se encontró un plan de producción para este número de parte.');
        // }

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

        // Obtener la URL de origen de la sesión para determinar el redireccionamiento
        $origin = session('part_number_entry_origin');

        // Determinar si es entrada o salida basado en la URL de origen
        $isEntry = true; // Por defecto asumimos entrada
        if ($origin) {
            $isEntry = str_contains($origin, 'entry-scan') || !str_contains($origin, 'exit-scan');
        }

        // Configurar redirección
        $redirect = $origin ? redirect($origin) : redirect()->route('production-records.part-number-entry');

        // Buscar número de parte
        $partNumber = PartNumber::where('number', $partNumberInput)->where('is_obsolete', false)->first();
        if (!$partNumber) {
            return $redirect->with('error', "Número de parte no encontrado: {$partNumberInput}");
        }

        if ($isEntry) {
            $shift = Shift::getShift();
            $today = Shift::getPlannedDate();
            $productionPlan = null;

            $productionPlan = ProductionPlan::getProductionPlan($partNumber->id, $today, $shift->id);
            if ($productionPlan === null) {
                $productionPlan = ProductionPlan::store(null, $partNumber->id, 0, $today, $shift->id);
            }

            // if (!$productionPlan) {
            //     return $redirect->with('warning', 'No se encontró un plan de producción para este número de parte.');
            // }

            // $exists = ProductionRecord::productionPlanExists($productionPlan->id, '00000000', $partNumber->id, '000000', $quantity, 'entry');
            // if ($exists) {
            //     return $redirect->with('error', 'Este número de parte ya ha sido registrado anteriormente.');
            // }

            ProductionRecord::store($productionPlan->id, '00000000', $partNumber->previousProcesses->where('is_obsolete', false)->first()->id, '000000', $quantity, 'entry');

            event(new MaterialEntryRegistered());

            // if ($productionPlan->produced_quantity == 0) {
            //     $status = Status::where('key', 'in_progress')->first();
            //     $productionPlan->update([
            //         'produced_quantity' => $quantity,
            //         'status_id' => $status->id ?? $productionPlan->status_id,
            //     ]);
            // } else {
            //     $productionPlan->increment('produced_quantity', intval($quantity));
            // }
        } else {
            $shift = Shift::getShift();
            $today = Shift::getPlannedDate();

            $productionPlan = ProductionPlan::getProductionPlan($partNumber->id, $today, $shift->id);
            if ($productionPlan === null) {
                $productionPlan = ProductionPlan::store(null, $partNumber->id, 0, $today, $shift->id);
            }

            // $exists = ProductionRecord::productionPlanExists($productionPlan->id, '00000000', $previousPartNumber->id, '000000', $quantity, 'exit');
            // if ($exists) {
            //     return $redirect->with('error', 'Este número de parte ya ha sido registrado anteriormente.');
            // }

            ProductionRecord::store($productionPlan->id, '00000000', $partNumber->id, '000000', $quantity, 'exit');

            event(new MaterialExitRegistered());

            if ($productionPlan->produced_quantity == 0) {
                $productionPlan->update([
                    'produced_quantity' => $quantity
                ]);
            } else {
                $productionPlan->increment('produced_quantity', intval($quantity));
            }
        }

        // Limpiar la sesión después de usarla
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

        $previousPartNumber = $partNumber->previousProcesses->where('is_obsolete', false);
        if (!$previousPartNumber) {
            return $redirect->with('warning', 'No hay procesos anterior configurado para este número de parte.');
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
