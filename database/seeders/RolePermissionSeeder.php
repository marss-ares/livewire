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

            // Statuses permissions
            ['name' => 'View Statuses',    'slug' => 'statuses.view',    'category' => 'Statuses',     'description' => 'Can view statuses list'],
            ['name' => 'Create Statuses',  'slug' => 'statuses.create',  'category' => 'Statuses',     'description' => 'Can create new statuses'],
            ['name' => 'Edit Statuses',    'slug' => 'statuses.edit',    'category' => 'Statuses',     'description' => 'Can edit statuses'],
            ['name' => 'Delete Statuses',  'slug' => 'statuses.delete',  'category' => 'Statuses',     'description' => 'Can delete statuses'],
            ['name' => 'Reorder Statuses', 'slug' => 'statuses.reorder', 'category' => 'Statuses',     'description' => 'Can change the order of statuses'],

            ['name' => 'Import Excel', 'slug' => 'forms.import', 'category' => 'Forms',     'description' => 'Can import forms from Excel'],
        ];

        foreach ($permissions as $data) {
            // Folosim updateOrCreate pentru a putea rula seeder-ul de mai multe ori fără erori
            Permission::updateOrCreate(['slug' => $data['slug']], $data);
        }

        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Full access to everything',
                'permissions' => Permission::pluck('id')->toArray(),
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Can manage users, statuses and view roles',
                'permissions' => Permission::whereIn('slug', [
                    'users.view', 'users.create', 'users.edit',
                    'roles.view',
                    'statuses.view', 'statuses.create', 'statuses.edit', 'statuses.reorder',
                ])->pluck('id')->toArray(),
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Basic authenticated user',
                'permissions' => Permission::whereIn('slug', [
                    'users.view',
                    'statuses.view',
                ])->pluck('id')->toArray(),
            ],
        ];

        foreach ($roles as $data) {
            $permissionIds = $data['permissions'];
            unset($data['permissions']);

            $role = Role::updateOrCreate(['slug' => $data['slug']], $data);
            $role->permissions()->sync($permissionIds);
        }
    }
}
