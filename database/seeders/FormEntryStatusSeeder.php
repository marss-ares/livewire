<?php

namespace Database\Seeders;

use App\Models\FormEntryStatus;
use Illuminate\Database\Seeder;

class FormEntryStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Contact Nou',          'color' => '#3b82f6', 'order' => 0],
            ['name' => 'Apel Efectuat',        'color' => '#eab308', 'order' => 1],
            ['name' => 'Apelare Suplimentara', 'color' => '#f97316', 'order' => 2],
            ['name' => 'Succes',               'color' => '#22c55e', 'order' => 3],
            ['name' => 'Refuzat',              'color' => '#ef4444', 'order' => 4],
        ];

        foreach ($statuses as $data) {
            FormEntryStatus::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
