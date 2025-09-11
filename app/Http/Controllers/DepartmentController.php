<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $departments = Department::where('name', 'like', "%{$search}%")
            ->orWhere('code', 'like', "%{$search}%")
            ->orderBy('code')
            ->paginate(10);

        return view('departments.index', ['departments' => $departments, 'search' => $search]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:departments,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500'
        ], [
            'code.required' => 'El código es obligatorio.',
            'code.unique' => 'Este código ya está en uso.',
            'code.max' => 'El código no puede tener más de 20 caracteres.',
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'description.max' => 'La descripción no puede tener más de 500 caracteres.'
        ]);

        try {
            Department::create($validated);

            return redirect()->route('departments.index')
                ->with('success', 'Departamento creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el departamento. Inténtalo de nuevo.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:departments,code,' . $department->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500'
        ], [
            'code.required' => 'El código es obligatorio.',
            'code.unique' => 'Este código ya está en uso.',
            'code.max' => 'El código no puede tener más de 20 caracteres.',
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'description.max' => 'La descripción no puede tener más de 500 caracteres.'
        ]);

        try {
            $department->update($validated);

            return redirect()->route('departments.index')
                ->with('success', 'Departamento actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el departamento. Inténtalo de nuevo.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        try {
            // Verificar si el departamento tiene áreas asociadas
            if ($department->lines()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar el departamento porque tiene áreas asociadas.');
            }

            $department->delete();

            return redirect()->route('departments.index')
                ->with('success', 'Departamento eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el departamento. Inténtalo de nuevo.');
        }
    }
}
