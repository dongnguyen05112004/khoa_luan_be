<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ClassRegistrationSeeder
 * Hội viên đăng ký các lớp học nhóm
 * Cột: id, user_id, class_id, registration_date, status, created_at, updated_at
 */
class ClassRegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $members = DB::table('users')->where('role_id', 5)->orderBy('id')->get();
        $classes = DB::table('classes')->orderBy('id')->get();

        if ($classes->isEmpty() || $members->isEmpty()) return;

        $classIds   = $classes->pluck('id')->toArray();
        $classByBranch = $classes->groupBy('branch_id')->map(fn($g) => $g->pluck('id')->toArray());

        $statuses = ['registered', 'registered', 'registered', 'completed', 'completed', 'cancelled'];

        $registrations = [];
        $seen = [];

        foreach ($members as $idx => $member) {
            // Mỗi hội viên đăng ký 1-3 lớp phù hợp với chi nhánh của họ
            $branchClasses = $classByBranch[$member->branch_id] ?? $classIds;
            shuffle($branchClasses);
            $count = min(rand(1, 3), count($branchClasses));
            $selected = array_slice($branchClasses, 0, $count);

            foreach ($selected as $classId) {
                $key = "{$member->id}_{$classId}";
                if (isset($seen[$key])) continue;
                $seen[$key] = true;

                // Ngày đăng ký phân bổ thực tế theo thời gian
                $regDates = [
                    '2025-10-10', '2025-10-25', '2025-11-05', '2025-11-20',
                    '2025-12-03', '2025-12-18', '2026-01-08', '2026-01-22',
                    '2026-02-05', '2026-02-20', '2026-03-04', '2026-03-19',
                    '2026-04-02', '2026-04-16', '2026-05-05',
                ];
                $regDate = $regDates[($idx + array_search($classId, $selected)) % count($regDates)];
                $status  = $statuses[($idx + $classId) % count($statuses)];

                $registrations[] = [
                    'user_id'           => $member->id,
                    'class_id'          => $classId,
                    'registration_date' => $regDate,
                    'status'            => $status,
                    'created_at'        => $regDate . ' 10:00:00',
                    'updated_at'        => $regDate . ' 10:00:00',
                ];
            }
        }

        // Batch insert
        foreach (array_chunk($registrations, 100) as $chunk) {
            DB::table('class_registrations')->insert($chunk);
        }
    }
}
