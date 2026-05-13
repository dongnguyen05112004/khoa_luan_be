<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * PtBookingSeeder
 * Lịch đặt buổi tập PT theo hợp đồng
 * Cột: id, contract_id, trainer_id, schedule_time, status, notes, created_at, updated_at
 *
 * Logic: Mỗi hợp đồng có số buổi theo used_sessions + 1-2 buổi upcoming
 */
class PtBookingSeeder extends Seeder
{
    public function run(): void
    {
        $contracts = DB::table('pt_contracts')
            ->where('status', '!=', 'cancelled')
            ->orderBy('id')
            ->get();

        $notesDone = [
            'Buổi tập tốt, hội viên đạt mục tiêu bài squat 80kg.',
            'Tập cardio + upper body. Hội viên có tiến bộ rõ rệt.',
            'Review form tập, chỉnh sửa tư thế deadlift.',
            'Tập core và flexibility, hội viên phản hồi tích cực.',
            'Buổi đầu tiên: đánh giá thể lực tổng quan.',
            'Tăng thêm tạ cho bài bench press, hội viên đáp ứng tốt.',
            'Tập sức bền cardio 30 phút + tạ nhẹ.',
            null,
            null,
        ];

        $slotTimes = [
            '06:30', '07:00', '08:00', '09:00',
            '10:00', '15:00', '16:00', '17:00',
            '18:00', '19:00', '20:00',
        ];

        $bookings = [];

        foreach ($contracts as $ci => $contract) {
            $usedSessions = $contract->used_sessions ?? 0;
            $startDate    = strtotime($contract->start_date);
            $endDate      = strtotime($contract->end_date);

            // Tạo booking cho buổi đã dùng (done)
            for ($s = 0; $s < $usedSessions; $s++) {
                // Tính ngày buổi tập: mỗi tuần 2-3 buổi
                $daysOffset   = $s * intdiv(7, 2); // ~3-4 ngày/buổi
                $scheduleTs   = min($startDate + $daysOffset * 86400, $endDate - 86400);
                // Chỉ tạo booking trong quá khứ
                if ($scheduleTs >= time()) break;

                $dateStr  = date('Y-m-d', $scheduleTs);
                $timeSlot = $slotTimes[($ci + $s) % count($slotTimes)];

                $bookings[] = [
                    'contract_id'   => $contract->id,
                    'trainer_id'    => $contract->trainer_id,
                    'schedule_time' => $dateStr . ' ' . $timeSlot . ':00',
                    'status'        => 'done',
                    'notes'         => $notesDone[($s + $ci) % count($notesDone)],
                    'created_at'    => $dateStr . ' 08:00:00',
                    'updated_at'    => $dateStr . ' ' . $timeSlot . ':00',
                ];
            }

            // Tạo 1-2 buổi sắp tới (confirmed/pending) nếu hợp đồng còn active
            if (in_array($contract->status, ['active', 'pending'])) {
                $upcomingCount = ($ci % 2 === 0) ? 2 : 1;
                for ($u = 0; $u < $upcomingCount; $u++) {
                    $futureDays   = rand(1, 14);
                    $futureDateTs = time() + $futureDays * 86400;
                    if ($futureDateTs > $endDate) continue;

                    $dateStr  = date('Y-m-d', $futureDateTs);
                    $timeSlot = $slotTimes[($ci + $u + 3) % count($slotTimes)];

                    $bookings[] = [
                        'contract_id'   => $contract->id,
                        'trainer_id'    => $contract->trainer_id,
                        'schedule_time' => $dateStr . ' ' . $timeSlot . ':00',
                        'status'        => ($u === 0 && $ci % 3 !== 0) ? 'confirmed' : 'pending',
                        'notes'         => 'Khách hẹn trước, nhắc nhở qua Zalo.',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
            }

            // 1-2 buổi cancelled trong lịch sử của mỗi hợp đồng
            if ($usedSessions > 3) {
                $cancelTs   = $startDate + rand(1, 10) * 86400;
                $cancelDate = date('Y-m-d', $cancelTs);
                $timeSlot   = $slotTimes[$ci % count($slotTimes)];
                $bookings[] = [
                    'contract_id'   => $contract->id,
                    'trainer_id'    => $contract->trainer_id,
                    'schedule_time' => $cancelDate . ' ' . $timeSlot . ':00',
                    'status'        => 'cancelled',
                    'notes'         => 'Hội viên báo hủy buổi do bận việc đột xuất.',
                    'created_at'    => $cancelDate . ' 07:00:00',
                    'updated_at'    => $cancelDate . ' 07:00:00',
                ];
            }
        }

        // Batch insert
        foreach (array_chunk($bookings, 200) as $chunk) {
            DB::table('pt_bookings')->insert($chunk);
        }
    }
}
