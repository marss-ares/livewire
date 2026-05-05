<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Users permissions
            ['name' => 'View Users',       'slug' => 'users.view',       'category' => 'Users',        'description' => 'Can view users list'],
            ['name' => 'Create Users',     'slug' => 'users.create',     'category' => 'Users',        'description' => 'Can create new users'],
            ['name' => 'Edit Users',       'slug' => 'users.edit',       'category' => 'Users',        'description' => 'Can edit users'],
            ['name' => 'Delete Users',     'slug' => 'users.delete',     'category' => 'Users',        'description' => 'Can delete users'],
            // Roles permissions
            ['name' => 'View Roles',       'slug' => 'roles.view',       'category' => 'Roles',        'description' => 'Can view roles list'],
            ['name' => 'Create Roles',     'slug' => 'roles.create',     'category' => 'Roles',        'description' => 'Can create new roles'],
            ['name' => 'Edit Roles',       'slug' => 'roles.edit',       'category' => 'Roles',        'description' => 'Can edit roles'],
            ['name' => 'Delete Roles',     'slug' => 'roles.delete',     'category' => 'Roles',        'description' => 'Can delete roles'],
        ];

        foreach ($permissions as $data) {
            Permission::create($data);
        }

        $roles = [
            [
                'name'        => 'Administrator',
                'slug'        => 'admin',
                'description' => 'Full access to everything',
                'permissions' => Permission::pluck('id')->toArray(),
            ],
            [
                'name'        => 'Manager',
                'slug'        => 'manager',
                'description' => 'Can manage users, view roles',
                'permissions' => Permission::whereIn('slug', [
                    'users.view', 'users.create', 'users.edit',
                    'roles.view',
                ])->pluck('id')->toArray(),
            ],
            [
                'name'        => 'User',
                'slug'        => 'user',
                'description' => 'Basic authenticated user',
                'permissions' => Permission::whereIn('slug', [
                    'users.view',
                ])->pluck('id')->toArray(),
            ],
        ];

        foreach ($roles as $data) {
            $permissionIds = $data['permissions'];
            unset($data['permissions']);

            $role = Role::create($data);
            $role->permissions()->sync($permissionIds);
        }
    }
}
