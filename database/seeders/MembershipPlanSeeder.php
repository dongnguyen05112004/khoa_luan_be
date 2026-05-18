<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * MembershipPlanSeeder - Các gói tập phổ biến tại phòng gym Việt Nam
 * Cột: id, plan_name, duration_days, price, value, description, status, created_at, updated_at
 */
class MembershipPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            // --- GÓI THỬ NGHIỆM / NGẮN HẠN ---
            [
                'id'           => 1,
                'plan_name'    => 'Gói 1 Tháng',
                'duration_days'=> 30,
                'price'        => 500000.00,
                'value'        => 500000.00,
                'description'  => 'Gói tập cơ bản 1 tháng. Truy cập tất cả khu vực tập luyện, phòng cardio, phòng gym. Giờ mở cửa 5:00 - 22:00.',
                'status'       => 'active',
                'created_at'   => '2025-09-01 08:00:00',
                'updated_at'   => '2025-09-01 08:00:00',
            ],
            // --- GÓI PHỔ BIẾN ---
            [
                'id'           => 2,
                'plan_name'    => 'Gói 3 Tháng',
                'duration_days'=> 90,
                'price'        => 1300000.00,
                'value'        => 1500000.00,
                'description'  => 'Gói 3 tháng tiết kiệm hơn 13%. Bao gồm truy cập toàn bộ cơ sở, lớp học nhóm cơ bản miễn phí.',
                'status'       => 'active',
                'created_at'   => '2025-09-01 08:00:00',
                'updated_at'   => '2025-09-01 08:00:00',
            ],
            // --- GÓI TIẾT KIỆM ---
            [
                'id'           => 3,
                'plan_name'    => 'Gói 6 Tháng',
                'duration_days'=> 180,
                'price'        => 2400000.00,
                'value'        => 3000000.00,
                'description'  => 'Gói 6 tháng tiết kiệm 20%. Bao gồm toàn bộ quyền lợi cơ bản + 2 buổi tư vấn dinh dưỡng miễn phí.',
                'status'       => 'active',
                'created_at'   => '2025-09-01 08:00:00',
                'updated_at'   => '2025-09-01 08:00:00',
            ],
            // --- GÓI VIP ---
            [
                'id'           => 4,
                'plan_name'    => 'Gói 1 Năm (VIP)',
                'duration_days'=> 365,
                'price'        => 4200000.00,
                'value'        => 6000000.00,
                'description'  => 'Gói năm VIP tiết kiệm 30%. Truy cập toàn bộ cơ sở + lớp học nhóm không giới hạn + 1 buổi PT/tháng.',
                'status'       => 'active',
                'created_at'   => '2025-09-01 08:00:00',
                'updated_at'   => '2025-09-01 08:00:00',
            ],
            // --- GÓI PREMIUM (Tất cả chi nhánh) ---
            [
                'id'           => 5,
                'plan_name'    => 'Gói Đa Chi Nhánh 3 Tháng',
                'duration_days'=> 90,
                'price'        => 1800000.00,
                'value'        => 2100000.00,
                'description'  => 'Gói 3 tháng có thể tập tại tất cả 3 chi nhánh FitLife. Phù hợp cho hội viên thường xuyên di chuyển.',
                'status'       => 'active',
                'created_at'   => '2025-10-01 08:00:00',
                'updated_at'   => '2025-10-01 08:00:00',
            ],
            // --- GÓI SINH VIÊN ---
            [
                'id'           => 6,
                'plan_name'    => 'Gói Sinh Viên 3 Tháng',
                'duration_days'=> 90,
                'price'        => 950000.00,
                'value'        => 1300000.00,
                'description'  => 'Ưu đãi đặc biệt dành cho sinh viên (cần thẻ SV). Giờ tập 8:00-16:00 các ngày trong tuần.',
                'status'       => 'active',
                'created_at'   => '2025-10-15 08:00:00',
                'updated_at'   => '2025-10-15 08:00:00',
            ],
            // --- GÓI NGỪNG BÁN (inactive) ---
            [
                'id'           => 7,
                'plan_name'    => 'Gói Combo PT + Phòng Gym 1 Tháng',
                'duration_days'=> 30,
                'price'        => 2500000.00,
                'value'        => 3200000.00,
                'description'  => 'Gói cũ bao gồm 8 buổi PT + quyền dùng phòng gym. Đã ngưng bán từ 03/2026 (thay bằng hợp đồng PT riêng).',
                'status'       => 'inactive',
                'created_at'   => '2025-09-01 08:00:00',
                'updated_at'   => '2026-03-01 10:00:00',
            ],
            [
                'id'           => 8,
                'plan_name'    => 'Gói demo 1 Tháng',
                'duration_days'=> 30,
                'price'        => 10000,
                'value'        => 100000,
                'description'  => 'Gói demo 1 Tháng',
                'status'       => 'active',
                'created_at'   => '2025-09-01 08:00:00',
                'updated_at'   => '2026-03-01 10:00:00',
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('membership_plans')->updateOrInsert(['id' => $plan['id']], $plan);
        }
    }
}
