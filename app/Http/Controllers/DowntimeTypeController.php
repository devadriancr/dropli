<?php

namespace App\Http\Controllers;

use App\Models\DowntimeType;
use Illuminate\Http\Request;

class DowntimeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $downtimeTypes = DowntimeType::withCount('downtimeReasons')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', '%' . $search . '%')
                           ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('downtime-types.index', compact('downtimeTypes', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('downtime-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:downtime_types,name',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            DowntimeType::create($request->all());

            return redirect()->route('downtime-types.index')
                ->with('success', 'Tipo de paro creado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear el tipo de paro: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DowntimeType $downtimeType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DowntimeType $downtimeType)
    {
        return view('downtime-types.edit', compact('downtimeType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DowntimeType $downtimeType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:downtime_types,name,' . $downtimeType->id,
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $downtimeType->update($request->all());

            return redirect()->route('downtime-types.index')
                ->with('success', 'Tipo de paro actualizado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar el tipo de paro: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DowntimeType $downtimeType)
    {
        try {
            // Verificar si tiene razones asociadas antes de eliminar
            if ($downtimeType->downtimeReasons()->count() > 0) {
                return redirect()->route('downtime-types.index')
                    ->with('error', 'No se puede eliminar el tipo de paro porque tiene razones asociadas.');
            }

            $downtimeType->delete();

            return redirect()->route('downtime-types.index')
                ->with('success', 'Tipo de paro eliminado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->route('downtime-types.index')
                ->with('error', 'Error al eliminar el tipo de paro: ' . $e->getMessage());
        }
    }
}
