<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * MemberSubscriptionSeeder
 * Tạo đăng ký gói tập cho hội viên, trải dài 5-7 tháng (10/2025 - 05/2026)
 * Cột: id, user_id, plan_id, promotion_id, start_date, end_date, price, status, cancel_reason
 *
 * Logic thực tế:
 * - Hội viên đăng ký rải rác từng tháng
 * - Có người gia hạn (2 lần), có người hủy, có người expired
 * - Khách vãng lai KHÔNG có subscription (không có gói tập dài hạn)
 */
class MemberSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy tất cả hội viên (role_id = 5)
        $members = DB::table('users')->where('role_id', 5)->orderBy('id')->get();

        // Ánh xạ plan: [plan_id, duration_days, base_price]
        $plans = [
            1 => [1, 30,  500000],
            2 => [2, 90,  1300000],
            3 => [3, 180, 2400000],
            4 => [4, 365, 4200000],
            5 => [5, 90,  1800000],
            6 => [6, 90,  950000],
        ];

        // Promotion mapping: [promotion_id, discount%]
        $promoMap = [
            1 => [1, 30],  // GOVAP2025
            2 => [2, 25],  // BLACKFRI25
            3 => [3, 15],  // XMAS2025
            4 => [4, 20],  // HAPPY2026
            5 => [5, 15],  // TET2026
            6 => [6, 20],  // WOMEN0803
            7 => [7, 10],  // REFER2026
            8 => [8, 20],  // SUMMER2026
        ];

        // Script thực tế: mỗi hội viên có 1-3 subscription
        // Dữ liệu cấu trúc: [email, plan_idx, start_date, promo_idx|null, status, cancel_reason|null]
        $subscriptionScript = [
            // ============ Hội viên Quận 1 (40 người) ============
            // Đăng ký tháng 10/2025
            ['member.an@gmail.com',        2, '2025-10-05', 1,    'active',    null],
            ['member.binh@gmail.com',       3, '2025-10-12', null, 'active',    null],
            ['member.chau@gmail.com',       1, '2025-10-20', 1,    'expired',   null],
            ['member.dung@gmail.com',       2, '2025-10-28', null, 'active',    null],
            ['member.em@gmail.com',         4, '2025-10-15', 1,    'active',    null],
            // Đăng ký tháng 11/2025 (Black Friday)
            ['member.phuong@gmail.com',     3, '2025-11-25', 2,    'active',    null],
            ['member.giang@gmail.com',      2, '2025-11-25', 2,    'expired',   null],
            ['member.hanh@gmail.com',       2, '2025-11-10', null, 'active',    null],
            ['member.hai@gmail.com',        1, '2025-11-03', null, 'expired',   null],
            ['member.huyen@gmail.com',      3, '2025-11-18', 2,    'active',    null],
            // Tháng 12/2025 (Giáng Sinh)
            ['member.khai@gmail.com',       3, '2025-12-20', 3,    'active',    null],
            ['member.lan.q1@gmail.com',     4, '2025-12-20', 3,    'active',    null],
            ['member.long@gmail.com',       2, '2025-12-02', null, 'active',    null],
            ['member.mai.q1@gmail.com',     1, '2025-12-09', null, 'expired',   null],
            ['member.nam@gmail.com',        3, '2025-12-23', 3,    'active',    null],
            // Tháng 1/2026 (Năm Mới)
            ['member.ngoc.q1@gmail.com',    2, '2026-01-05', 4,    'active',    null],
            ['member.phu@gmail.com',        2, '2026-01-12', 4,    'active',    null],
            ['member.quynh@gmail.com',      6, '2026-01-05', 4,    'active',    null],
            ['member.son@gmail.com',        1, '2026-01-20', null, 'expired',   null],
            ['member.thanh.q1@gmail.com',   3, '2026-01-15', 4,    'active',    null],
            // Tháng 2/2026 (Tết)
            ['member.thang@gmail.com',      2, '2026-02-03', 5,    'active',    null],
            ['member.thu.q1@gmail.com',     3, '2026-02-10', null, 'active',    null],
            ['member.tien@gmail.com',       2, '2026-02-17', 5,    'cancelled', 'Tôi bận công tác dài hạn, tạm thời không tập được.'],
            ['member.trang.q1@gmail.com',   4, '2026-02-24', null, 'active',    null],
            ['member.tuan.q1@gmail.com',    2, '2026-02-03', 5,    'active',    null],
            // Tháng 3/2026 (8/3)
            ['member.uyen@gmail.com',       3, '2026-03-08', 6,    'active',    null],
            ['member.viet@gmail.com',       2, '2026-03-10', null, 'active',    null],
            ['member.xuan@gmail.com',       2, '2026-03-03', 6,    'active',    null],
            ['member.yen@gmail.com',        6, '2026-03-15', null, 'active',    null],
            ['member.zung@gmail.com',       2, '2026-03-17', 6,    'cancelled', 'Chuyển sang tập ở gần nhà hơn.'],
            // Tháng 4/2026 (Giới thiệu bạn bè)
            ['member.canh@gmail.com',       3, '2026-04-01', 7,    'active',    null],
            ['member.bao@gmail.com',        2, '2026-04-08', 7,    'active',    null],
            ['member.cuong@gmail.com',      4, '2026-04-15', null, 'active',    null],
            ['member.dieu@gmail.com',       2, '2026-04-22', 7,    'active',    null],
            ['member.dat@gmail.com',        3, '2026-04-01', 7,    'active',    null],
            // Tháng 5/2026 (Hè Khỏe Đẹp)
            ['member.giang.f@gmail.com',    2, '2026-05-02', 8,    'active',    null],
            ['member.hieu@gmail.com',       3, '2026-05-05', 8,    'pending',   null],
            ['member.hien@gmail.com',       2, '2026-05-09', 8,    'active',    null],
            ['member.khang@gmail.com',      1, '2026-05-10', null, 'pending',   null],
            ['member.kim@gmail.com',        6, '2026-05-12', 8,    'pending',   null],

            // ============ Hội viên Bình Thạnh (25 người) ============
            ['member.bao.bt@gmail.com',     2, '2025-10-10', 1,    'active',    null],
            ['member.cam@gmail.com',        2, '2025-11-05', 2,    'expired',   null],
            ['member.dung.bt@gmail.com',    3, '2025-11-25', 2,    'active',    null],
            ['member.diem@gmail.com',       4, '2025-12-20', 3,    'active',    null],
            ['member.hau@gmail.com',        2, '2026-01-05', 4,    'active',    null],
            ['member.huong@gmail.com',      3, '2026-01-12', null, 'active',    null],
            ['member.khanh@gmail.com',      2, '2026-01-27', 5,    'active',    null],
            ['member.loan@gmail.com',       2, '2026-02-10', null, 'active',    null],
            ['member.manh@gmail.com',       3, '2026-02-17', 5,    'cancelled', 'Lý do cá nhân.'],
            ['member.ngan@gmail.com',       2, '2026-03-01', 6,    'active',    null],
            ['member.phong@gmail.com',      4, '2026-03-10', null, 'active',    null],
            ['member.phuc@gmail.com',       2, '2026-03-15', 6,    'active',    null],
            ['member.quan@gmail.com',       3, '2026-04-05', 7,    'active',    null],
            ['member.ram@gmail.com',        6, '2026-04-08', 7,    'active',    null],
            ['member.sang@gmail.com',       2, '2026-04-15', null, 'active',    null],
            ['member.sen@gmail.com',        2, '2026-04-22', 7,    'active',    null],
            ['member.tam@gmail.com',        3, '2026-05-03', 8,    'active',    null],
            ['member.tho@gmail.com',        2, '2026-05-05', 8,    'pending',   null],
            ['member.toan@gmail.com',       2, '2026-05-09', null, 'active',    null],
            ['member.truc@gmail.com',       4, '2025-12-01', 3,    'active',    null],
            ['member.tu@gmail.com',         2, '2026-01-20', 4,    'expired',   null],
            ['member.van.bt@gmail.com',     3, '2026-02-24', null, 'active',    null],
            ['member.xuan.bt@gmail.com',    2, '2026-03-20', null, 'active',    null],
            ['member.yen.bt@gmail.com',     2, '2026-04-25', 7,    'active',    null],
            ['member.chau.bt@gmail.com',    3, '2026-05-10', 8,    'pending',   null],

            // ============ Hội viên Gò Vấp (15 người) ============
            ['member.an.gv@gmail.com',      2, '2025-10-05', 1,    'expired',   null],
            ['member.binh.gv@gmail.com',    3, '2025-11-10', 2,    'active',    null],
            ['member.cuc@gmail.com',        2, '2025-12-15', 3,    'active',    null],
            ['member.dong@gmail.com',       4, '2026-01-08', 4,    'active',    null],
            ['member.ha.gv@gmail.com',      2, '2026-01-27', 5,    'active',    null],
            ['member.huy.gv@gmail.com',     2, '2026-02-10', null, 'active',    null],
            ['member.hang.gv@gmail.com',    3, '2026-03-01', 6,    'active',    null],
            ['member.lam.gv@gmail.com',     2, '2026-03-15', null, 'active',    null],
            ['member.nhi@gmail.com',        6, '2026-04-01', 7,    'active',    null],
            ['member.phat@gmail.com',       2, '2026-04-10', 7,    'active',    null],
            ['member.quyen@gmail.com',      2, '2026-04-22', null, 'active',    null],
            ['member.rung@gmail.com',       3, '2026-05-03', 8,    'active',    null],
            ['member.suong@gmail.com',      2, '2026-05-07', 8,    'pending',   null],
            ['member.tai@gmail.com',        2, '2026-05-10', null, 'active',    null],
            ['member.uyen.gv@gmail.com',    4, '2025-11-01', 2,    'active',    null],
        ];

        foreach ($subscriptionScript as $row) {
            [$email, $planIdx, $startDate, $promoIdx, $status, $cancelReason] = $row;

            $userId = DB::table('users')->where('email', $email)->value('id');
            if (!$userId) continue;

            [$planId, $durationDays, $basePrice] = $plans[$planIdx];
            $promotionId = null;
            $finalPrice  = $basePrice;

            if ($promoIdx) {
                [$promotionId, $discountPct] = $promoMap[$promoIdx];
                $finalPrice = round($basePrice * (1 - $discountPct / 100));
            }

            $endDate = date('Y-m-d', strtotime($startDate . " +{$durationDays} days"));

            // Nếu status vẫn là active mà endDate đã qua -> tự động expired
            if ($status === 'active' && $endDate < date('Y-m-d')) {
                $status = 'expired';
            }

            // Không tạo trùng
            $exists = DB::table('member_subscriptions')
                ->where('user_id', $userId)
                ->where('start_date', $startDate)
                ->exists();
            if ($exists) continue;

            DB::table('member_subscriptions')->insert([
                'user_id'       => $userId,
                'plan_id'       => $planId,
                'promotion_id'  => $promotionId,
                'start_date'    => $startDate,
                'end_date'      => $endDate,
                'price'         => $finalPrice,
                'status'        => $status,
                'cancel_reason' => $cancelReason,
                'created_at'    => $startDate . ' 10:00:00',
                'updated_at'    => now(),
            ]);
        }
    }
}
