<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::with('workCenters', 'roles')
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['Administrator', 'Admin', 'administrator', 'admin']);
            })
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        return view('users.index', compact('users', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $workCenters = WorkCenter::orderBy('number')->get();
        $roles = Role::where('guard_name', 'web')
            ->whereNotIn('name', ['Administrator', 'Admin', 'administrator', 'admin'])
            ->orderBy('name')
            ->get();
        return view('users.create', compact('workCenters', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'exists:roles,id'],
            'work_centers' => ['nullable', 'array'],
            'work_centers.*' => ['exists:work_centers,id']
        ]);

        try {
            // Verificar que el rol no sea de administrador
            $role = Role::where('id', $request->role)
                ->where('guard_name', 'web')
                ->firstOrFail();

            if (in_array(strtolower($role->name), ['administrator', 'admin'])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No se puede asignar un rol de administrador.');
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Asignar el rol al usuario
            $user->assignRole($role);

            if ($request->has('work_centers')) {
                $user->workCenters()->sync($request->work_centers);
            }

            return redirect()->route('users.index')
                ->with('success', 'Usuario creado exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El rol seleccionado no existe.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // Verificar que el usuario no sea administrador
        if ($user->hasRole(['Administrator', 'Admin', 'administrator', 'admin'])) {
            return redirect()->route('users.index')
                ->with('error', 'No se puede editar un usuario administrador.');
        }

        $user->load('workCenters', 'roles');
        $workCenters = WorkCenter::orderBy('number')->get();
        $roles = Role::where('guard_name', 'web')
            ->whereNotIn('name', ['Administrator', 'Admin', 'administrator', 'admin'])
            ->orderBy('name')
            ->get();
        return view('users.edit', compact('user', 'workCenters', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Verificar que el usuario no sea administrador
        if ($user->hasRole(['Administrator', 'Admin', 'administrator', 'admin'])) {
            return redirect()->route('users.index')
                ->with('error', 'No se puede actualizar un usuario administrador.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'exists:roles,id'],
            'work_centers' => ['nullable', 'array'],
            'work_centers.*' => ['exists:work_centers,id']
        ]);

        try {
            // Verificar que el nuevo rol no sea de administrador - USANDO EL GUARD CORRECTO
            $role = Role::where('id', $request->role)
                ->where('guard_name', 'web')
                ->firstOrFail();

            if (in_array(strtolower($role->name), ['administrator', 'admin'])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No se puede asignar un rol de administrador.');
            }

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            // Sincronizar el rol del usuario
            $user->syncRoles([$role]);

            $user->workCenters()->sync($request->work_centers ?? []);

            return redirect()->route('users.index')
                ->with('success', 'Usuario actualizado exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El rol seleccionado no existe.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Verificar que el usuario no sea administrador
        if ($user->hasRole(['Administrator', 'Admin', 'administrator', 'admin'])) {
            return redirect()->route('users.index')
                ->with('error', 'No se puede eliminar un usuario administrador.');
        }

        try {
            $user->workCenters()->detach();
            $user->delete();

            return redirect()->route('users.index')
                ->with('success', 'Usuario eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }
}
