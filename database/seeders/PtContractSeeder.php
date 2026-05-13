<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * PtContractSeeder
 * Hợp đồng PT giữa hội viên và huấn luyện viên
 * Cột: id, user_id, trainer_id, total_sessions, used_sessions, start_date, end_date,
 *       price, status, branch_id, created_at, updated_at
 *
 * Logic:
 * - ~35% hội viên có hợp đồng PT (phổ biến ở gym tầm trung)
 * - Gói PT: 10, 20, hoặc 30 buổi
 * - Thời hạn: thường 1-3 tháng
 * - Giá: theo pt_rate x total_sessions
 */
class PtContractSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy trainer IDs
        $ptMinhId  = DB::table('trainers')->whereIn('user_id', DB::table('users')->where('email', 'pt.minh@fitlifegym.vn')->pluck('id'))->value('id');
        $ptHoaId   = DB::table('trainers')->whereIn('user_id', DB::table('users')->where('email', 'pt.hoa@fitlifegym.vn')->pluck('id'))->value('id');
        $ptTuanId  = DB::table('trainers')->whereIn('user_id', DB::table('users')->where('email', 'pt.tuan@fitlifegym.vn')->pluck('id'))->value('id');
        $ptLanId   = DB::table('trainers')->whereIn('user_id', DB::table('users')->where('email', 'pt.lan@fitlifegym.vn')->pluck('id'))->value('id');
        $ptHungId  = DB::table('trainers')->whereIn('user_id', DB::table('users')->where('email', 'pt.hung@fitlifegym.vn')->pluck('id'))->value('id');
        $ptThaoId  = DB::table('trainers')->whereIn('user_id', DB::table('users')->where('email', 'pt.thao@fitlifegym.vn')->pluck('id'))->value('id');

        $getUser = fn($email) => DB::table('users')->where('email', $email)->value('id');

        // [email, trainer_id, total_sessions, start_date, months, status, branch_id]
        $contracts = [
            // === PT Minh - Q1 (rate: 300k/buổi) ===
            [$getUser('member.an@gmail.com'),        $ptMinhId, 20, '2025-10-08',  2, 'completed', 1],
            [$getUser('member.em@gmail.com'),         $ptMinhId, 30, '2025-10-18',  3, 'active',    1],
            [$getUser('member.khai@gmail.com'),       $ptMinhId, 20, '2025-12-22',  2, 'active',    1],
            [$getUser('member.nam@gmail.com'),        $ptMinhId, 10, '2026-01-10',  1, 'active',    1],
            [$getUser('member.thang@gmail.com'),      $ptMinhId, 20, '2026-02-05',  2, 'active',    1],
            [$getUser('member.canh@gmail.com'),       $ptMinhId, 30, '2026-04-03',  3, 'active',    1],
            [$getUser('member.cuong@gmail.com'),      $ptMinhId, 10, '2026-04-20',  1, 'active',    1],
            [$getUser('member.hien@gmail.com'),       $ptMinhId, 20, '2026-05-10',  2, 'pending',   1],

            // === PT Hoa - Q1 (rate: 250k/buổi) ===
            [$getUser('member.binh@gmail.com'),       $ptHoaId,  10, '2025-10-15',  1, 'completed', 1],
            [$getUser('member.phuong@gmail.com'),     $ptHoaId,  20, '2025-11-28',  2, 'active',    1],
            [$getUser('member.lan.q1@gmail.com'),     $ptHoaId,  30, '2025-12-22',  3, 'active',    1],
            [$getUser('member.uyen@gmail.com'),       $ptHoaId,  20, '2026-03-10',  2, 'active',    1],
            [$getUser('member.xuan@gmail.com'),       $ptHoaId,  10, '2026-03-05',  1, 'active',    1],
            [$getUser('member.giang.f@gmail.com'),    $ptHoaId,  20, '2026-05-05',  2, 'pending',   1],

            // === PT Tuấn - Bình Thạnh (rate: 320k/buổi) ===
            [$getUser('member.bao.bt@gmail.com'),     $ptTuanId, 20, '2025-10-15',  2, 'completed', 2],
            [$getUser('member.dung.bt@gmail.com'),    $ptTuanId, 30, '2025-11-28',  3, 'active',    2],
            [$getUser('member.hau@gmail.com'),        $ptTuanId, 20, '2026-01-08',  2, 'active',    2],
            [$getUser('member.phong@gmail.com'),      $ptTuanId, 30, '2026-03-12',  3, 'active',    2],
            [$getUser('member.quan@gmail.com'),       $ptTuanId, 10, '2026-04-07',  1, 'active',    2],
            [$getUser('member.tam@gmail.com'),        $ptTuanId, 20, '2026-05-05',  2, 'pending',   2],

            // === PT Lan - Bình Thạnh (rate: 230k/buổi) ===
            [$getUser('member.cam@gmail.com'),        $ptLanId,  10, '2025-11-08',  1, 'completed', 2],
            [$getUser('member.diem@gmail.com'),       $ptLanId,  20, '2025-12-22',  2, 'active',    2],
            [$getUser('member.ngan@gmail.com'),       $ptLanId,  20, '2026-03-03',  2, 'active',    2],
            [$getUser('member.phuc@gmail.com'),       $ptLanId,  10, '2026-03-18',  1, 'active',    2],
            [$getUser('member.sen@gmail.com'),        $ptLanId,  20, '2026-04-25',  2, 'active',    2],
            [$getUser('member.tho@gmail.com'),        $ptLanId,  10, '2026-05-07',  1, 'pending',   2],

            // === PT Hùng - Gò Vấp (rate: 280k/buổi) ===
            [$getUser('member.binh.gv@gmail.com'),   $ptHungId, 20, '2025-11-12',  2, 'active',    3],
            [$getUser('member.dong@gmail.com'),       $ptHungId, 30, '2026-01-10',  3, 'active',    3],
            [$getUser('member.huy.gv@gmail.com'),     $ptHungId, 10, '2026-02-12',  1, 'active',    3],
            [$getUser('member.phat@gmail.com'),       $ptHungId, 20, '2026-04-12',  2, 'active',    3],
            [$getUser('member.rung@gmail.com'),       $ptHungId, 20, '2026-05-05',  2, 'pending',   3],

            // === PT Thảo - Gò Vấp (rate: 250k/buổi) ===
            [$getUser('member.an.gv@gmail.com'),      $ptThaoId, 10, '2025-10-08',  1, 'completed', 3],
            [$getUser('member.cuc@gmail.com'),        $ptThaoId, 20, '2025-12-18',  2, 'active',    3],
            [$getUser('member.ha.gv@gmail.com'),      $ptThaoId, 20, '2026-01-29',  2, 'active',    3],
            [$getUser('member.hang.gv@gmail.com'),    $ptThaoId, 10, '2026-03-03',  1, 'active',    3],
            [$getUser('member.nhi@gmail.com'),        $ptThaoId, 20, '2026-04-03',  2, 'active',    3],
            [$getUser('member.uyen.gv@gmail.com'),    $ptThaoId, 30, '2025-11-05',  3, 'active',    3],
        ];

        // Rate map theo trainer ID
        $rateMap = [
            $ptMinhId  => 300000,
            $ptHoaId   => 250000,
            $ptTuanId  => 320000,
            $ptLanId   => 230000,
            $ptHungId  => 280000,
            $ptThaoId  => 250000,
        ];

        foreach ($contracts as [$userId, $trainerId, $totalSessions, $startDate, $months, $status, $branchId]) {
            if (!$userId || !$trainerId) continue;
            $endDate = date('Y-m-d', strtotime($startDate . " +{$months} months"));
            $rate    = $rateMap[$trainerId] ?? 280000;
            $price   = $totalSessions * $rate;

            // Tính used_sessions theo thực tế
            $usedSessions = match ($status) {
                'completed' => $totalSessions,
                'cancelled' => rand(0, (int) ($totalSessions * 0.4)),
                'pending'   => 0,
                'active'    => (int) round($totalSessions * rand(20, 80) / 100),
                default     => 0,
            };

            $exists = DB::table('pt_contracts')
                ->where('user_id', $userId)
                ->where('trainer_id', $trainerId)
                ->where('start_date', $startDate)
                ->exists();
            if ($exists) continue;

            DB::table('pt_contracts')->insert([
                'user_id'        => $userId,
                'trainer_id'     => $trainerId,
                'total_sessions' => $totalSessions,
                'used_sessions'  => $usedSessions,
                'start_date'     => $startDate,
                'end_date'       => $endDate,
                'price'          => $price,
                'status'         => $status,
                'branch_id'      => $branchId,
                'created_at'     => $startDate . ' 10:00:00',
                'updated_at'     => now(),
            ]);
        }
    }
}
