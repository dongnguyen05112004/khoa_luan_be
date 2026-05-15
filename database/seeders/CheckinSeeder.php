<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * CheckinSeeder
 * Dữ liệu check-in thực tế từ 10/2025 đến 05/2026
 * Cột: id, user_id, branch_id, check_in_at, check_out_at, duration, schedule_done, method, notes
 *
 * Logic:
 * - Hội viên active tập 3-5 buổi/tuần (chủ yếu)
 * - Hội viên inactive tập ít hơn (0-2 buổi/tuần)
 * - Khách vãng lai: chỉ 1-3 lần check-in tổng
 * - Method: chủ yếu 'qr' và 'manual', ít 'face'
 * - Giờ tập: 6-9 sáng và 17-21 tối (peak hours)
 */
class CheckinSeeder extends Seeder
{
    public function run(): void
    {
        $checkins = [];

        $members = DB::table('users')
            ->where('role_id', 5)
            ->orderBy('id')
            ->get();

        $methods = ['qr', 'qr', 'qr', 'manual', 'face'];
        $morningHours = ['06:15', '06:30', '06:45', '07:00', '07:15', '07:30', '08:00', '08:30', '09:00'];
        $eveningHours = ['17:00', '17:30', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00'];

        // Tạo checkin cho từng hội viên
        foreach ($members as $idx => $user) {
            $branchId = $user->branch_id;
            // Hội viên: tập đều đặn từ khi đăng ký
                $sub = DB::table('member_subscriptions')
                    ->where('user_id', $user->id)
                    ->orderBy('start_date')
                    ->first();
                if (!$sub) continue;
                $startDate = $sub->start_date;
                $endDate   = min($sub->end_date, date('Y-m-d'));
                $checkinDates = $this->generateMemberCheckinDates($startDate, $endDate, $idx);

            foreach ($checkinDates as $dateStr) {
                $isPeakMorning = rand(0, 1) === 0;
                $hour = $isPeakMorning
                    ? $morningHours[$idx % count($morningHours)]
                    : $eveningHours[$idx % count($eveningHours)];

                $checkInAt  = $dateStr . ' ' . $hour . ':00';
                $duration   = rand(45, 120); // 45-120 phút
                $checkOutAt = date('Y-m-d H:i:s', strtotime($checkInAt) + $duration * 60);

                $checkins[] = [
                    'user_id'       => $user->id,
                    'branch_id'     => $branchId,
                    'check_in_at'   => $checkInAt,
                    'check_out_at'  => $checkOutAt,
                    'duration'      => $duration,
                    'schedule_done' => rand(0, 4) > 0 ? 1 : 0, // 80% hoàn thành
                    'method'        => $methods[($idx + strlen($dateStr)) % count($methods)],
                    'notes'         => null,
                    'created_at'    => $checkInAt,
                    'updated_at'    => $checkOutAt,
                ];
            }
        }

        // Batch insert
        foreach (array_chunk($checkins, 200) as $chunk) {
            DB::table('checkins')->insert($chunk);
        }
    }

    /**
     * Tạo ngày check-in cho hội viên thường
     * Tập trung vào thứ 2-7, thứ 3/5/7 cường độ cao hơn
     */
    private function generateMemberCheckinDates(string $startDate, string $endDate, int $seed): array
    {
        $dates  = [];
        $start  = strtotime($startDate);
        $end    = strtotime($endDate);
        $cur    = $start;

        // Tần suất: 3-5 buổi/tuần
        $freqPerWeek = ($seed % 3 === 0) ? 3 : (($seed % 3 === 1) ? 4 : 5);

        while ($cur <= $end) {
            $dow = (int) date('N', $cur); // 1=Mon ... 7=Sun
            // Lịch tập theo tần suất
            $shouldTrain = match ($freqPerWeek) {
                3 => in_array($dow, [1, 3, 5]),
                4 => in_array($dow, [1, 2, 4, 6]),
                5 => in_array($dow, [1, 2, 3, 5, 6]),
                default => in_array($dow, [1, 3, 5]),
            };

            // Một số ngày bỏ tập (nghỉ lễ, bận)
            $skipDay = ($seed + (int) date('Yz', $cur)) % 7 === 0;

            if ($shouldTrain && !$skipDay && date('Y-m-d', $cur) <= date('Y-m-d')) {
                $dates[] = date('Y-m-d', $cur);
            }
            $cur = strtotime('+1 day', $cur);
        }

        return $dates;
    }
}
