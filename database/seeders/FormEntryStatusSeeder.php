<?php

namespace Database\Seeders;

use App\Models\FormEntryStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class FormEntryStatusSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        $statuses = [
            ['name' => 'Contact Nou',          'color' => '#3b82f6', 'order' => 0], // Albastru
            ['name' => 'Apel Efectuat',        'color' => '#eab308', 'order' => 1], // Galben
            ['name' => 'Apelare Suplimentara', 'color' => '#f97316', 'order' => 2], // Portocaliu
            ['name' => 'Succes',               'color' => '#22c55e', 'order' => 3], // Verde
            ['name' => 'Refuzat',              'color' => '#ef4444', 'order' => 4], // Roșu
        ];

        foreach ($statuses as $data) {
            FormEntryStatus::firstOrCreate(
                ['name' => $data['name'], 'owner_id' => $admin?->id],
                array_merge($data, ['owner_id' => $admin?->id])
            );
        }
    }
}
