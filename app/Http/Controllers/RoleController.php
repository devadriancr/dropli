<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $roles = Role::withCount('permissions')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        return view('roles.index', compact('roles', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[1] : $parts[0];
        });

        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id']
        ]);

        // Prevenir crear roles con nombres de administrador
        $adminNames = ['Administrator', 'Admin', 'administrator', 'admin'];
        if (in_array(strtolower($request->name), array_map('strtolower', $adminNames))) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No se puede crear un rol con nombre de administrador.');
        }

        // Crear el rol especificando el guard
        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->has('permissions')) {
            // Obtener los permisos por ID y especificar el guard
            $permissions = Permission::whereIn('id', $request->permissions)
                ->where('guard_name', 'web')
                ->get();

            $role->syncPermissions($permissions);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $role->load('permissions');

        $permissions = Permission::orderBy('name')->get()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[1] : $parts[0];
        });

        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id']
        ]);

        // Prevenir cambiar nombre a roles de administrador
        $adminNames = ['Administrator', 'Admin', 'administrator', 'admin'];
        if (in_array(strtolower($request->name), array_map('strtolower', $adminNames))) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No se puede usar un nombre de administrador.');
        }

        $role->update(['name' => $request->name]);

        // Obtener los permisos por ID y especificar el guard
        $permissions = $request->has('permissions')
            ? Permission::whereIn('id', $request->permissions)
            ->where('guard_name', 'web')
            ->get()
            : [];

        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')
            ->with('success', 'Rol actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Prevenir eliminar el rol Administrator
        if ($role->name === 'Administrator') {
            return redirect()->route('roles.index')
                ->with('error', 'No se puede eliminar el rol Administrator.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado exitosamente.');
    }
}
