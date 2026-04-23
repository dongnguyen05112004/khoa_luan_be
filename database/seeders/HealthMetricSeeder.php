<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HealthMetric;
use App\Models\User;

class HealthMetricSeeder extends Seeder
{
    public function run(): void
    {
        $members = User::whereHas('role', function($q) {
            $q->where('role_name', 'member');
        })->get();
        
        if ($members->isEmpty()) return;

        $profiles = [
            [ // Béo phì
                'weight' => 95.0, 'height' => 165.0, 'fat' => 35.0, 'muscle' => 25.0, 'goal' => 'Giảm cân, giảm mỡ thừa'
            ],
            [ // Gầy ốm
                'weight' => 45.0, 'height' => 165.0, 'fat' => 10.0, 'muscle' => 15.0, 'goal' => 'Tăng cân, cải thiện sức khỏe'
            ],
            [ // Thể hình chuẩn
                'weight' => 75.0, 'height' => 175.0, 'fat' => 12.0, 'muscle' => 40.0, 'goal' => 'Duy trì vóc dáng, tăng cơ bắp'
            ],
            [ // Hơi mập
                'weight' => 80.0, 'height' => 168.0, 'fat' => 28.0, 'muscle' => 30.0, 'goal' => 'Giảm mỡ bụng, săn chắc cơ thể'
            ],
        ];

        foreach ($members as $index => $member) {
            // NẾU LÀ 4 HỘI VIÊN ĐẦU TIÊN -> Set profile đặc biệt
            if ($index < 4) {
                $p = $profiles[$index];
                
                // Bản ghi tháng trước
                HealthMetric::create([
                    'user_id' => $member->id,
                    'record_date' => now()->subDays(30)->toDateString(),
                    'weight' => $p['weight'] + ($index == 0 ? 3 : ($index == 1 ? -2 : 0)), // béo thì lúc trước béo hơn
                    'height' => $p['height'],
                    'body_fat_percentage' => $p['fat'] + 2,
                    'muscle_mass_kg' => $p['muscle'] - 1,
                    'bmi' => round(($p['weight'] + 2) / (($p['height'] / 100) ** 2), 2)
                ]);

                // Bản ghi hiện tại
                HealthMetric::create([
                    'user_id' => $member->id,
                    'record_date' => now()->toDateString(),
                    'weight' => $p['weight'],
                    'height' => $p['height'],
                    'body_fat_percentage' => $p['fat'],
                    'muscle_mass_kg' => $p['muscle'],
                    'bmi' => round($p['weight'] / (($p['height'] / 100) ** 2), 2)
                ]);
                
                \App\Models\MemberProfile::updateOrCreate(
                    ['user_id' => $member->id],
                    ['health_notes' => $p['goal']]
                );
            } 
            // CÁC HỘI VIÊN CÒN LẠI -> Random như cũ
            else {
                $weight = rand(500, 900) / 10;
                $height = rand(155, 185);
                $bodyFat = rand(150, 300) / 10;
                $muscle = rand(250, 400) / 10;
                
                $numRecords = rand(2, 6);
                $baseDate = \Carbon\Carbon::now()->subDays($numRecords * 20);

                for ($i = 0; $i < $numRecords; $i++) {
                    $recordDate = (clone $baseDate)->addDays($i * 20);
                    if ($recordDate->isFuture()) continue;

                    $weight += rand(-15, 5) / 10; 
                    $bodyFat += rand(-10, 2) / 10; 
                    $muscle += rand(0, 5) / 10; 
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
}
