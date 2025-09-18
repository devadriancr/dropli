<?php

namespace App\Http\Controllers;

use App\Models\DowntimeRecord;
use App\Models\DowntimeReason;
use App\Models\WorkCenter;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DowntimeRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $downtimeRecords = DowntimeRecord::with(['downtimeReason.downtimeType', 'workCenter'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('downtimeReason', function ($q) use ($search) {
                    $q->where('code', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('workCenter', function ($q) use ($search) {
                        $q->where('number', 'like', '%' . $search . '%')
                            ->orWhere('name', 'like', '%' . $search . '%');
                    })
                    ->orWhere('minutes', 'like', '%' . $search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('downtime-records.index', compact('downtimeRecords', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $downtimeReasons = DowntimeReason::with('downtimeType')
            ->orderBy('code')
            ->get();

        if (Auth::check()) {
            $workCenters = Auth::user()->workCenters;
        } else {
            // Para invitados, mostrar solo el work center específico
            $workCenters = WorkCenter::with('area')
                ->whereHas('area', function ($query) {
                    $query->where('name', 'PAINT');
                })
                ->get();
        }

        // Guardar la URL de origen en la sesión
        $origin = $request->get('origin');
        if ($origin) {
            session(['downtime_origin' => $origin]);
        }

        // Determinar qué vista usar según si es usuario autenticado o invitado
        $view = Auth::check() ? 'downtime-records.create' : 'guest.downtime-records.create';

        return view($view, compact('downtimeReasons', 'workCenters'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'downtime_reason_id' => 'required|exists:downtime_reasons,id',
            'work_center_id' => 'required|exists:work_centers,id',
            'minutes' => 'required|numeric|min:0',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time'
        ]);

        try {
            DowntimeRecord::create($request->all());

            // Obtener la URL de origen de la sesión
            $origin = session('downtime_origin');

            // Verificar si es una de las rutas específicas y redirigir apropiadamente
            if ($origin) {
                // Limpiar la sesión después de usarla
                session()->forget('downtime_origin');

                return redirect($origin)
                    ->with('success', 'Registro de paro creado exitosamente.');
            }

            // Redirección por defecto para usuarios autenticados
            if (Auth::check()) {
                return redirect()->route('downtime-records.index')
                    ->with('success', 'Registro de tiempo muerto creado exitosamente.');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear el registro de paro: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DowntimeRecord $downtimeRecord)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DowntimeRecord $downtimeRecord)
    {
        $downtimeReasons = DowntimeReason::with('downtimeType')
            ->orderBy('code')
            ->get();

        if (Auth::check()) {
            $workCenters = Auth::user()->workCenters;
        } else {
            // Para invitados, mostrar solo el work center específico
            $workCenters = WorkCenter::with('area')
                ->whereHas('area', function ($query) {
                    $query->where('name', 'PAINT');
                })
                ->get();
        }

        return view('downtime-records.edit', compact('downtimeRecord', 'downtimeReasons', 'workCenters'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DowntimeRecord $downtimeRecord)
    {
        $request->validate([
            'downtime_reason_id' => 'required|exists:downtime_reasons,id',
            'work_center_id' => 'required|exists:work_centers,id',
            'minutes' => 'required|numeric|min:0',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time'
        ]);

        try {
            $downtimeRecord->update($request->all());

            return redirect()->route('downtime-records.index')
                ->with('success', 'Registro de tiempo muerto actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar el registro de tiempo muerto: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DowntimeRecord $downtimeRecord)
    {
        try {
            $downtimeRecord->delete();

            return redirect()->route('downtime-records.index')
                ->with('success', 'Registro de tiempo muerto eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('downtime-records.index')
                ->with('error', 'Error al eliminar el registro de tiempo muerto: ' . $e->getMessage());
        }
    }

    /**
     * Calculate minutes based on start and end time
     */
    public function calculateMinutes(Request $request)
    {
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time'
        ]);

        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);

        $minutes = $endTime->diffInMinutes($startTime);

        return response()->json(['minutes' => $minutes]);
    }
}
