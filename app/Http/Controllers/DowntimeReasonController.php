<?php

namespace App\Http\Controllers;

use App\Models\DowntimeReason;
use App\Models\DowntimeType;
use Illuminate\Http\Request;

class DowntimeReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $downtimeReasons = DowntimeReason::with(['downtimeType', 'downtimeRecords'])
            ->withCount('downtimeRecords')
            ->when($search, function ($query, $search) {
                return $query->where('code', 'like', '%' . $search . '%')
                           ->orWhere('name', 'like', '%' . $search . '%')
                           ->orWhere('description', 'like', '%' . $search . '%')
                           ->orWhereHas('downtimeType', function ($q) use ($search) {
                               $q->where('name', 'like', '%' . $search . '%');
                           });
            })
            ->orderBy('code')
            ->paginate(10);

        return view('downtime-reasons.index', compact('downtimeReasons', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $downtimeTypes = DowntimeType::orderBy('name')->get();
        return view('downtime-reasons.create', compact('downtimeTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:downtime_reasons,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'downtime_type_id' => 'required|exists:downtime_types,id'
        ]);

        try {
            DowntimeReason::create($request->all());

            return redirect()->route('downtime-reasons.index')
                ->with('success', 'Razón de paro creada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear la razón de paro: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DowntimeReason $downtimeReason)
    {
        return view('downtime-reasons.show', compact('downtimeReason'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DowntimeReason $downtimeReason)
    {
        $downtimeTypes = DowntimeType::orderBy('name')->get();
        return view('downtime-reasons.edit', compact('downtimeReason', 'downtimeTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DowntimeReason $downtimeReason)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:downtime_reasons,code,' . $downtimeReason->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'downtime_type_id' => 'required|exists:downtime_types,id'
        ]);

        try {
            $downtimeReason->update($request->all());

            return redirect()->route('downtime-reasons.index')
                ->with('success', 'Razón de paro actualizada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la razón de paro: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DowntimeReason $downtimeReason)
    {
        try {
            // Verificar si tiene registros asociados antes de eliminar
            if ($downtimeReason->downtimeRecords()->count() > 0) {
                return redirect()->route('downtime-reasons.index')
                    ->with('error', 'No se puede eliminar la razón de paro porque tiene registros asociados.');
            }

            $downtimeReason->delete();

            return redirect()->route('downtime-reasons.index')
                ->with('success', 'Razón de paro eliminada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->route('downtime-reasons.index')
                ->with('error', 'Error al eliminar la razón de paro: ' . $e->getMessage());
        }
    }
}
