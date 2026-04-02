<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HealthMetric;
use App\Models\User;

class HealthMetricSeeder extends Seeder
{
    public function run(): void
    {
        $members = User::where('role_id', 5)->where('branch_id', 1)->get();
        if ($members->isEmpty()) return;

        foreach ($members as $member) {
            // Pick a random starting weight and height
            $weight = rand(500, 900) / 10;
            $height = rand(155, 185);
            $bodyFat = rand(150, 300) / 10;
            $muscle = rand(250, 400) / 10;
            
            $numRecords = rand(2, 6);
            $baseDate = \Carbon\Carbon::now()->subDays($numRecords * 20);

            for ($i = 0; $i < $numRecords; $i++) {
                $recordDate = (clone $baseDate)->addDays($i * 20);
                if ($recordDate->isFuture()) continue;

                // Randomize a small progressive change
                $weight += rand(-15, 5) / 10; // slightly losing weight
                $bodyFat += rand(-10, 2) / 10; // losing fat
                $muscle += rand(0, 5) / 10; // gaining muscle
                $bmi = round($weight / (($height / 100) ** 2), 2);

                HealthMetric::create([
                    'user_id' => $member->id,
                    'record_date' => $recordDate->toDateString(),
                    'weight' => round($weight, 1),
                    'height' => $height,
                    'body_fat_percentage' => round(max(5, $bodyFat), 1),
                    'muscle_mass_kg' => round($muscle, 1),
                    'bmi' => $bmi
                ]);
            }
        }
    }
}
