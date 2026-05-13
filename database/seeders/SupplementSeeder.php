<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SupplementSeeder
 * Bổ sung 2 thứ:
 *  1. Health Metrics cho 79 hội viên đang thiếu (2 lần đo/người, cách nhau 30 ngày)
 *  2. PT Contracts: mỗi PT có 3-4 hội viên (PT1-6, branch tương ứng)
 *
 * Chạy: php artisan db:seed --class=SupplementSeeder
 */
class SupplementSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHealthMetrics();
        $this->seedPtContracts();
    }

    // =========================================================================
    // 1. HEALTH METRICS - bổ sung cho tất cả hội viên còn thiếu
    // =========================================================================
    private function seedHealthMetrics(): void
    {
        $memberIds = DB::table('users')->where('role_id', 5)->pluck('id');
        $hasMetric = DB::table('health_metrics')->pluck('user_id')->unique();
        $missing   = $memberIds->diff($hasMetric); // hội viên chưa có metrics

        $this->command->info("Bo sung health_metrics cho {$missing->count()} hoi vien...");

        $inserts = [];
        foreach ($missing as $userId) {
            $user   = DB::table('users')->where('id', $userId)->first(['gender', 'created_at']);
            $gender = $user->gender ?? 'male';

            // Chỉ số ban đầu thực tế người Việt
            $weight1 = $gender === 'male' ? rand(60, 90) : rand(48, 68);
            $height  = $gender === 'male' ? rand(163, 178) : rand(152, 165);
            $fat1    = $gender === 'male' ? rand(16, 28) : rand(22, 35);
            $muscle1 = $gender === 'male' ? rand(30, 44) : rand(22, 32);
            $bmi1    = round($weight1 / (($height / 100) ** 2), 1);

            // Lần đo 1: ~60 ngày trước
            $date1 = now()->subDays(60)->toDateString();
            $inserts[] = [
                'user_id'             => $userId,
                'record_date'         => $date1,
                'weight'              => $weight1,
                'height'              => $height,
                'body_fat_percentage' => $fat1,
                'muscle_mass_kg'      => $muscle1,
                'bmi'                 => $bmi1,
                'created_at'          => $date1 . ' 09:00:00',
                'updated_at'          => $date1 . ' 09:00:00',
            ];

            // Lần đo 2: ~30 ngày trước (có tiến triển nhẹ, thực tế)
            $weight2 = round($weight1 + (rand(-300, -50) / 100), 1); // giảm 0.5-3 kg
            $fat2    = max(10, $fat1 + rand(-3, -1));
            $muscle2 = min(55, $muscle1 + rand(0, 2));
            $bmi2    = round($weight2 / (($height / 100) ** 2), 1);

            $date2 = now()->subDays(30)->toDateString();
            $inserts[] = [
                'user_id'             => $userId,
                'record_date'         => $date2,
                'weight'              => $weight2,
                'height'              => $height,
                'body_fat_percentage' => $fat2,
                'muscle_mass_kg'      => $muscle2,
                'bmi'                 => $bmi2,
                'created_at'          => $date2 . ' 09:00:00',
                'updated_at'          => $date2 . ' 09:00:00',
            ];
        }

        foreach (array_chunk($inserts, 50) as $chunk) {
            DB::table('health_metrics')->insert($chunk);
        }

        $this->command->info("Da them " . count($inserts) . " rows health_metrics.");
    }

    // =========================================================================
    // 2. PT CONTRACTS - mỗi PT có đúng 3-4 hội viên
    // =========================================================================
    private function seedPtContracts(): void
    {
        $this->command->info("Bo sung PT contracts...");

        // Lấy trainers và branch tương ứng
        $trainers = DB::table('trainers')
            ->join('users', 'trainers.user_id', '=', 'users.id')
            ->get(['trainers.id as trainer_id', 'users.branch_id', 'users.full_name']);

        // Hội viên đã có contract (user_id trong pt_contracts)
        $alreadyInContract = DB::table('pt_contracts')->pluck('user_id')->unique()->toArray();

        // Hội viên theo branch, chưa có contract
        $branchMembers = [];
        foreach ([1, 2, 3] as $bid) {
            $branchMembers[$bid] = DB::table('users')
                ->where('role_id', 5)
                ->where('branch_id', $bid)
                ->whereNotIn('id', $alreadyInContract)
                ->pluck('id')
                ->shuffle()
                ->values();
        }

        // Pool chung (dự phòng nếu branch thiếu)
        $allFreeMembers = DB::table('users')
            ->where('role_id', 5)
            ->whereNotIn('id', $alreadyInContract)
            ->pluck('id')
            ->shuffle()
            ->values();
        $usedIds = [];

        $pricePerSession = [150000, 180000, 200000, 220000, 250000];
        $sessions        = [10, 12, 15, 20];

        foreach ($trainers as $trainer) {
            // Số contract hiện tại
            $existing = DB::table('pt_contracts')->where('trainer_id', $trainer->trainer_id)->count();
            $target   = rand(3, 4);
            $need     = max(0, $target - $existing);

            if ($need === 0) continue;

            // Lấy members từ cùng branch, chưa dùng
            $pool = $branchMembers[$trainer->branch_id]
                ->diff($usedIds)
                ->values();

            // Nếu không đủ thì lấy từ pool toàn hệ thống
            if ($pool->count() < $need) {
                $pool = $allFreeMembers->diff($usedIds)->values();
            }

            $picked = $pool->take($need);
            foreach ($picked as $memberId) {
                $usedIds[] = $memberId;

                $sess      = $sessions[array_rand($sessions)];
                $used      = rand(0, $sess - 1);
                $price     = $pricePerSession[array_rand($pricePerSession)];
                $startDate = now()->subDays(rand(30, 90))->toDateString();
                $endDate   = date('Y-m-d', strtotime($startDate . ' +90 days'));

                DB::table('pt_contracts')->insert([
                    'user_id'        => $memberId,
                    'trainer_id'     => $trainer->trainer_id,
                    'branch_id'      => $trainer->branch_id,
                    'total_sessions' => $sess,
                    'used_sessions'  => $used,
                    'start_date'     => $startDate,
                    'end_date'       => $endDate,
                    'price'          => $sess * $price,
                    'status'         => ($used >= $sess) ? 'completed' : 'active',
                    'created_at'     => $startDate . ' 10:00:00',
                    'updated_at'     => now(),
                ]);
            }

            $this->command->info("  PT [{$trainer->full_name}]: +{$need} contracts (tong " . ($existing + $need) . ")");
        }

        $this->command->info("Hoan tat PT contracts.");
    }
}
