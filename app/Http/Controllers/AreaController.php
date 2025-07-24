<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Department;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $areas = Area::with(['department' => function ($query) {
            $query->orderBy('name');
        }])
            ->when($search, function ($query) use ($search) {
                $query->where('areas.name', 'like', "%{$search}%")
                    ->orWhere('areas.description', 'like', "%{$search}%")
                    ->orWhereHas('department', function ($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
            })
            ->orderBy('name')
            ->paginate();

        return view('areas.index')->with([
            'areas' => $areas,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('areas.create')->with('departments', $departments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        Area::create($request->all());

        return redirect()->route('areas.index')
            ->with('success', 'Área creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Puedes implementar esto si necesitas una vista detallada
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Area $area)
    {
        $departments = Department::orderBy('name')->get();

        return view('areas.edit')->with([
            'area' => $area,
            'departments' => $departments
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $area->update($request->all());

        return redirect()->route('areas.index')
            ->with('success', 'Área actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area)
    {
        try {
            $area->delete();
            return redirect()->route('areas.index')
                ->with('success', 'Área eliminada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('areas.index')
                ->with('error', 'No se puede eliminar el área porque está asociada a uno o más centros de trabajo.');
        }
    }
}
