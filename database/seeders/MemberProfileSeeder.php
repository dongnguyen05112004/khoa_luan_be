<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * MemberProfileSeeder
 * Tạo profile cho 80 hội viên + 15 khách vãng lai
 * Cột: id, user_id, date_of_birth, join_date, emergency_contact, health_notes,
 *       profile_picture, membership_type, created_at, updated_at
 */
class MemberProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy tất cả user có role 5 (member) hoặc 6 (guest) chưa có profile
        $memberRoleIds = [5];
        $existingProfileUserIds = DB::table('member_profiles')->pluck('user_id')->toArray();

        $users = DB::table('users')
            ->whereIn('role_id', $memberRoleIds)
            ->whereNotIn('id', $existingProfileUserIds)
            ->orderBy('id')
            ->get(['id', 'role_id', 'gender', 'branch_id', 'created_at']);

        $healthNotes = [
            'Không có vấn đề sức khỏe đặc biệt.',
            'Đau lưng nhẹ, tránh bài tập nặng phần lưng.',
            'Tiểu đường type 2 - cần theo dõi đường huyết.',
            'Huyết áp cao - tránh bài tập cường độ quá cao.',
            'Chấn thương đầu gối trái cũ, cần thận trọng với bài squat.',
            'Tình trạng sức khỏe bình thường.',
            'Béo phì độ 1, đang tập giảm cân.',
            'Hen suyễn nhẹ, mang theo thuốc khi tập.',
            null,
            null,
        ];

        $membershipTypes = ['basic', 'premium', 'vip', 'student', 'basic', 'basic', 'premium', 'basic'];

        $joinDates = [
            '2025-10-05', '2025-10-12', '2025-10-20', '2025-10-28',
            '2025-11-03', '2025-11-10', '2025-11-18', '2025-11-25',
            '2025-12-02', '2025-12-09', '2025-12-16', '2025-12-23',
            '2026-01-05', '2026-01-12', '2026-01-19', '2026-01-26',
            '2026-02-03', '2026-02-10', '2026-02-17', '2026-02-24',
            '2026-03-03', '2026-03-10', '2026-03-17', '2026-03-24',
            '2026-04-01', '2026-04-08', '2026-04-15', '2026-04-22',
            '2026-05-02', '2026-05-09',
        ];

        $dobs = [
            '1998-04-15', '1995-08-22', '2001-11-10', '1999-03-05', '1997-07-18',
            '2000-12-30', '1996-01-25', '2002-06-14', '1994-09-08', '1998-02-27',
            '2000-05-03', '1997-10-19', '1995-04-12', '2001-08-07', '1993-12-20',
            '1999-07-11', '2003-03-28', '1998-11-16', '1996-06-04', '2000-01-22',
        ];

        $emergencyContacts = [
            '0901234567', '0912345678', '0923456789', '0934567890', '0945678901',
            '0956789012', '0967890123', '0978901234', '0989012345', '0990123456',
        ];

        $i = 0;
        foreach ($users as $user) {
            $joinDate = $joinDates[$i % count($joinDates)];
            $dob      = $dobs[$i % count($dobs)];

            DB::table('member_profiles')->insert([
                'user_id'           => $user->id,
                'date_of_birth'     => $dob,
                'join_date'         => $joinDate,
                'emergency_contact' => $emergencyContacts[$i % count($emergencyContacts)],
                'health_notes'      => $healthNotes[$i % count($healthNotes)],
                'profile_picture'   => null,
                'membership_type'   => $membershipTypes[$i % count($membershipTypes)],
                'created_at'        => $joinDate . ' 09:00:00',
                'updated_at'        => $joinDate . ' 09:00:00',
            ]);
            $i++;
        }
    }
}
