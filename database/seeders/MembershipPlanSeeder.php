<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MembershipPlan;

class MembershipPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'plan_name'     => 'Gói 1 Tháng Cơ Bản',
                'duration_days' => 30,
                'price'         => 500000,
                'value'         => 600000,
                'description'   => 'Tập tự do, không kèm PT, không tính lớp học nhóm',
                'status'        => 'active',
            ],
            [
                'plan_name'     => 'Gói 3 Tháng Tiêu Chuẩn',
                'duration_days' => 90,
                'price'         => 1200000,
                'value'         => 1500000,
                'description'   => 'Tập tự do + tham gia 4 lớp học nhóm/tháng',
                'status'        => 'active',
            ],
            [
                'plan_name'     => 'Gói 6 Tháng Nâng Cao',
                'duration_days' => 180,
                'price'         => 2000000,
                'value'         => 2800000,
                'description'   => 'Tập tự do + lớp học nhóm không giới hạn',
                'status'        => 'active',
            ],
            [
                'plan_name'     => 'Gói 12 Tháng VIP',
                'duration_days' => 365,
                'price'         => 3500000,
                'value'         => 5000000,
                'description'   => 'Tập tự do + lớp học nhóm + 2 buổi PT/tháng + locker',
                'status'        => 'active',
            ],
            [
                'plan_name'     => 'Gói PT 10 Buổi',
                'duration_days' => 60,
                'price'         => 3000000,
                'value'         => 3000000,
                'description'   => 'Gói huấn luyện cá nhân 10 buổi với PT chuyên nghiệp',
                'status'        => 'active',
            ],
            [
                'plan_name'     => 'Gói PT 20 Buổi',
                'duration_days' => 90,
                'price'         => 5500000,
                'value'         => 7000000,
                'description'   => 'Gói huấn luyện cá nhân 20 buổi, có lộ trình tập tùy chỉnh',
                'status'        => 'active',
            ],
        ];

        foreach ($plans as $plan) {
            MembershipPlan::firstOrCreate(['plan_name' => $plan['plan_name']], $plan);
        }
    }
}
