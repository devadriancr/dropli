<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Department;
use App\Models\WorkCenter;
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
        $workCenters = WorkCenter::whereNull('area_id')->orderBy('number')->get();

        return view('areas.create')->with([
            'departments' => $departments,
            'workCenters' => $workCenters
        ]);
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
            'work_centers' => 'nullable|array',
            'work_centers.*' => 'exists:work_centers,id'
        ]);

        $area = Area::create($request->only(['department_id', 'name', 'description']));

        // Asignar work centers seleccionados
        if ($request->has('work_centers')) {
            WorkCenter::whereIn('id', $request->work_centers)
                ->update(['area_id' => $area->id]);
        }

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
        $assignedWorkCenters = $area->workCenters()->pluck('id')->toArray();
        $workCenters = WorkCenter::whereNull('area_id')
            ->orWhereIn('id', $assignedWorkCenters)
            ->orderBy('number')
            ->get();

        return view('areas.edit')->with([
            'area' => $area,
            'departments' => $departments,
            'workCenters' => $workCenters,
            'assignedWorkCenters' => $assignedWorkCenters
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
            'work_centers' => 'nullable|array',
            'work_centers.*' => 'exists:work_centers,id'
        ]);

        $area->update($request->only(['department_id', 'name', 'description']));

        // Primero, desasignar todos los work centers de esta área
        WorkCenter::where('area_id', $area->id)
            ->update(['area_id' => null]);

        // Luego asignar los nuevos work centers seleccionados
        if ($request->has('work_centers')) {
            WorkCenter::whereIn('id', $request->work_centers)
                ->update(['area_id' => $area->id]);
        }

        return redirect()->route('areas.index')
            ->with('success', 'Área actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area)
    {
        try {
            // Desasignar work centers antes de eliminar el área
            WorkCenter::where('area_id', $area->id)
                ->update(['area_id' => null]);

            $area->delete();
            return redirect()->route('areas.index')
                ->with('success', 'Área eliminada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('areas.index')
                ->with('error', 'No se puede eliminar el área porque está asociada a otros registros.');
        }
    }
}
