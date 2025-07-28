<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $shifts = Shift::query()
            ->when($search, function ($query, $search) {
                $query->where('abbreviation', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('shifts.index', compact('shifts', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shifts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'abbreviation' => 'required|string|max:10|unique:shifts',
            'name' => 'required|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string|max:255',
        ], [
            'end_time.after' => 'La hora de finalización debe ser posterior a la hora de inicio.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Shift::create($validator->validated());

        return redirect()->route('shifts.index')
            ->with('success', 'Turno creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Shift $shift)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shift $shift)
    {
        return view('shifts.edit', compact('shift'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shift $shift)
    {
        $validator = Validator::make($request->all(), [
            'abbreviation' => 'required|string|max:10|unique:shifts,abbreviation,' . $shift->id,
            'name' => 'required|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string|max:255',
        ], [
            'end_time.after' => 'La hora de finalización debe ser posterior a la hora de inicio.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $shift->update($validator->validated());

        return redirect()->route('shifts.index')
            ->with('success', 'Turno actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shift $shift)
    {
        // Verificar si el turno está siendo usado en algún plan de producción
        if ($shift->productionPlan()->exists()) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar el turno porque está asignado a uno o más planes de producción.');
        }

        $shift->delete();

        return redirect()->route('shifts.index')
            ->with('success', 'Turno eliminado exitosamente.');
    }
}
