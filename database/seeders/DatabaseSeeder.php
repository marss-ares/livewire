<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $adminRole = \App\Models\Role::where('slug', 'admin')->first();

        User::factory()->create([
            'name' => 'Test User',
            'avatar' => 'https://ui-avatars.com/api/?name=Test+User',
            'email' => 'test@example.com',
            'password' => 'password',
            'role_id' => $adminRole->id,
        ]);
    }
}
