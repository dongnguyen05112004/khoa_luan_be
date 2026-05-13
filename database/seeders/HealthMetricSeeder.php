<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * HealthMetricSeeder
 * Chỉ số sức khỏe của hội viên, theo dõi hàng tháng
 * Cột: id, user_id, record_date, weight, height, body_fat_percentage, muscle_mass_kg, bmi
 *
 * Logic: Hội viên có hợp đồng PT được theo dõi chặt chẽ hơn (hàng tháng),
 * hội viên thường thì ít hơn (2-3 lần trong kỳ tập)
 */
class HealthMetricSeeder extends Seeder
{
    public function run(): void
    {
        // Danh sách hội viên có PT contract (theo dõi tốt)
        $ptUsers = DB::table('pt_contracts')
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        // Tất cả hội viên
        $members = DB::table('users')
            ->where('role_id', 5)
            ->orderBy('id')
            ->get(['id', 'gender']);

        $metrics = [];

        foreach ($members as $idx => $member) {
            $hasPT      = in_array($member->id, $ptUsers);
            $isMale     = $member->gender === 'male';

            // Chỉ số ban đầu (tháng 1 khi đăng ký)
            $sub = DB::table('member_subscriptions')
                ->where('user_id', $member->id)
                ->orderBy('start_date')
                ->first();
            if (!$sub) continue;

            $startDate = $sub->start_date;

            // Thông số ban đầu tùy giới tính
            if ($isMale) {
                $initWeight = rand(650, 900) / 10; // 65-90 kg
                $height     = rand(165, 183) / 100; // 1.65-1.83m
                $initFat    = rand(180, 280) / 10;  // 18-28%
                $initMuscle = rand(280, 380) / 10;  // 28-38 kg
            } else {
                $initWeight = rand(480, 680) / 10; // 48-68 kg
                $height     = rand(155, 170) / 100;// 1.55-1.70m
                $initFat    = rand(220, 320) / 10; // 22-32%
                $initMuscle = rand(200, 280) / 10; // 20-28 kg
            }

            $bmi = round($initWeight / ($height * $height), 2);

            // Record đầu tiên
            $metrics[] = [
                'user_id'             => $member->id,
                'record_date'         => $startDate,
                'weight'              => $initWeight,
                'height'              => $height * 100, // cm
                'body_fat_percentage' => $initFat,
                'muscle_mass_kg'      => $initMuscle,
                'bmi'                 => $bmi,
                'created_at'          => $startDate . ' 09:00:00',
                'updated_at'          => $startDate . ' 09:00:00',
            ];

            // Số bản ghi theo dõi tiếp theo
            $followUpCount = $hasPT ? rand(3, 6) : rand(1, 3);
            $startTs       = strtotime($startDate);
            $nowTs         = time();

            for ($m = 1; $m <= $followUpCount; $m++) {
                $recordTs = $startTs + $m * 30 * 86400; // mỗi 30 ngày
                if ($recordTs > $nowTs) break;

                $recordDate = date('Y-m-d', $recordTs);

                // Hội viên tập luyện đều → giảm mỡ, tăng cơ
                $weightChange  = rand(-20, 5) / 10;  // -2 đến +0.5 kg/tháng
                $fatChange     = rand(-15, 5) / 10;  // -1.5 đến +0.5% mỡ/tháng
                $muscleChange  = rand(0, 15) / 10;   // 0 đến +1.5 kg cơ/tháng

                $newWeight = round(max(45, $initWeight + $weightChange * $m), 2);
                $newFat    = round(max(10, $initFat + $fatChange * $m), 2);
                $newMuscle = round($initMuscle + $muscleChange * $m, 2);
                $newBmi    = round($newWeight / ($height * $height), 2);

                $metrics[] = [
                    'user_id'             => $member->id,
                    'record_date'         => $recordDate,
                    'weight'              => $newWeight,
                    'height'              => $height * 100,
                    'body_fat_percentage' => $newFat,
                    'muscle_mass_kg'      => $newMuscle,
                    'bmi'                 => $newBmi,
                    'created_at'          => $recordDate . ' 09:00:00',
                    'updated_at'          => $recordDate . ' 09:00:00',
                ];
            }
        }

        foreach (array_chunk($metrics, 100) as $chunk) {
            DB::table('health_metrics')->insert($chunk);
        }
    }
}
