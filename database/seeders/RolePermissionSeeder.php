<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['Administrator', 'Manager', 'Assistant Manager', 'Leader', 'Team Leader', 'Support', 'Operator'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $modules = [
            'areas',
            'attributes',
            'customers',
            'departments',
            'downtime reasons',
            'downtime records',
            'downtime types',
            'item classes',
            'part numbers',
            'permissions',
            'production plans',
            'production records',
            'project prefixes',
            'projects',
            'roles',
            'scrap categories',
            'scrap reasons',
            'scrap records',
            'shifts',
            'standard packs',
            'statuses',
            'users',
            'work centers',
        ];

        $actions = ['create', 'view', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "$action $module"]);
            }
        }

        $adminRole = Role::where('name', 'Administrator')->first();
        $adminRole->syncPermissions(Permission::all());

        $user = User::find(1);
        if ($user && !$user->hasRole('Administrator')) {
            $user->assignRole('Administrator');
        }
    }
}
