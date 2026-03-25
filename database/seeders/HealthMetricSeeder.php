<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HealthMetric;
use App\Models\User;

class HealthMetricSeeder extends Seeder
{
    public function run(): void
    {
        $members = User::where('role_id', 5)->get();
        if ($members->isEmpty()) return;

        $metrics = [
            // member1 - 3 lần đo để thấy tiến trình
            ['user_id' => $members->get(0)?->id, 'record_date' => '2026-01-10', 'weight' => 75.5, 'height' => 172.0, 'body_fat_percentage' => 22.5, 'muscle_mass_kg' => 35.0, 'bmi' => 25.52],
            ['user_id' => $members->get(0)?->id, 'record_date' => '2026-02-10', 'weight' => 73.8, 'height' => 172.0, 'body_fat_percentage' => 21.0, 'muscle_mass_kg' => 36.2, 'bmi' => 24.95],
            ['user_id' => $members->get(0)?->id, 'record_date' => '2026-03-10', 'weight' => 72.1, 'height' => 172.0, 'body_fat_percentage' => 19.5, 'muscle_mass_kg' => 37.5, 'bmi' => 24.38],
            // member2
            ['user_id' => $members->get(1)?->id, 'record_date' => '2026-02-01', 'weight' => 58.0, 'height' => 162.0, 'body_fat_percentage' => 28.0, 'muscle_mass_kg' => 26.0, 'bmi' => 22.10],
            ['user_id' => $members->get(1)?->id, 'record_date' => '2026-03-01', 'weight' => 57.2, 'height' => 162.0, 'body_fat_percentage' => 27.0, 'muscle_mass_kg' => 26.5, 'bmi' => 21.80],
            // member3
            ['user_id' => $members->get(2)?->id, 'record_date' => '2026-01-20', 'weight' => 82.3, 'height' => 178.0, 'body_fat_percentage' => 25.0, 'muscle_mass_kg' => 40.0, 'bmi' => 25.98],
            ['user_id' => $members->get(2)?->id, 'record_date' => '2026-03-15', 'weight' => 80.1, 'height' => 178.0, 'body_fat_percentage' => 23.0, 'muscle_mass_kg' => 41.5, 'bmi' => 25.28],
        ];

        foreach ($metrics as $m) {
            if ($m['user_id']) {
                HealthMetric::firstOrCreate(
                    ['user_id' => $m['user_id'], 'record_date' => $m['record_date']],
                    $m
                );
            }
        }
    }
}
