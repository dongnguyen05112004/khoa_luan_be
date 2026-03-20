<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MemberProfile;
use App\Models\User;

class MemberProfileSeeder extends Seeder
{
    public function run(): void
    {
        $members = User::where('role_id', 5)->get();

        $profiles = [
            ['date_of_birth' => '1995-05-15', 'join_date' => '2024-01-10', 'emergency_contact' => '0900000001', 'health_notes' => 'Không có vấn đề sức khỏe'],
            ['date_of_birth' => '1998-08-22', 'join_date' => '2024-02-01', 'emergency_contact' => '0900000002', 'health_notes' => 'Dị ứng nhẹ với phấn hoa'],
            ['date_of_birth' => '1993-12-03', 'join_date' => '2024-01-20', 'emergency_contact' => '0900000003', 'health_notes' => 'Huyết áp thấp'],
            ['date_of_birth' => '2000-03-18', 'join_date' => '2024-03-05', 'emergency_contact' => '0900000004', 'health_notes' => 'Sức khỏe tốt'],
            ['date_of_birth' => '1990-07-30', 'join_date' => '2023-12-15', 'emergency_contact' => '0900000005', 'health_notes' => 'Đang điều trị đau lưng'],
        ];

        foreach ($members as $i => $member) {
            MemberProfile::firstOrCreate(
                ['user_id' => $member->id],
                $profiles[$i] ?? ['join_date' => '2024-01-01']
            );
        }
    }
}
