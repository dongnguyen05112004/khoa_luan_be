<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * TrainerSeeder
 * Tạo bản ghi trainers cho 6 PT
 * Cột: id, user_id, branch_id, specialization, pt_rate, max_sessions,
 *       experience, description, created_at, updated_at
 */
class TrainerSeeder extends Seeder
{
    public function run(): void
    {
        $get = fn($email) => DB::table('users')->where('email', $email)->value('id');

        $trainers = [
            [
                'user_id'        => $get('pt.minh@fitlifegym.vn'),
                'branch_id'      => 1,
                'specialization' => 'Tăng cơ - Giảm mỡ',
                'pt_rate'        => 300000.00,
                'max_sessions'   => 30,
                'experience'     => 6,
                'description'    => 'Lê Văn Minh - 6 năm kinh nghiệm, chuyên gia tăng cơ giảm mỡ. Chứng chỉ ACE-CPT. Đã huấn luyện hơn 200 khách hàng.',
            ],
            [
                'user_id'        => $get('pt.hoa@fitlifegym.vn'),
                'branch_id'      => 1,
                'specialization' => 'Yoga - Pilates - Linh hoạt',
                'pt_rate'        => 250000.00,
                'max_sessions'   => 25,
                'experience'     => 4,
                'description'    => 'Nguyễn Thị Hoa - Chuyên gia Yoga và Pilates. Chứng chỉ RYT-200, 4 năm kinh nghiệm.',
            ],
            [
                'user_id'        => $get('pt.tuan@fitlifegym.vn'),
                'branch_id'      => 2,
                'specialization' => 'Sức mạnh - Powerlifting',
                'pt_rate'        => 320000.00,
                'max_sessions'   => 28,
                'experience'     => 7,
                'description'    => 'Phạm Anh Tuấn - Vô địch Powerlifting quốc gia 2022. 7 năm kinh nghiệm huấn luyện sức mạnh.',
            ],
            [
                'user_id'        => $get('pt.lan@fitlifegym.vn'),
                'branch_id'      => 2,
                'specialization' => 'Cardio - Giảm cân - Zumba',
                'pt_rate'        => 230000.00,
                'max_sessions'   => 30,
                'experience'     => 3,
                'description'    => 'Trần Thị Lan - Chuyên gia cardio và giảm cân. Chứng chỉ Zumba Instructor, 3 năm kinh nghiệm.',
            ],
            [
                'user_id'        => $get('pt.hung@fitlifegym.vn'),
                'branch_id'      => 3,
                'specialization' => 'Thể hình - Bodybuilding',
                'pt_rate'        => 280000.00,
                'max_sessions'   => 25,
                'experience'     => 5,
                'description'    => 'Võ Quốc Hùng - Chuyên gia thể hình. Top 3 Bodybuilding mở rộng TP.HCM 2023. 5 năm kinh nghiệm.',
            ],
            [
                'user_id'        => $get('pt.thao@fitlifegym.vn'),
                'branch_id'      => 3,
                'specialization' => 'Phục hồi chức năng - Người cao tuổi',
                'pt_rate'        => 250000.00,
                'max_sessions'   => 20,
                'experience'     => 4,
                'description'    => 'Đinh Thị Thảo - Chuyên gia phục hồi chức năng. Chứng chỉ NASM-CES. Kinh nghiệm 4 năm với người cao tuổi và chấn thương.',
            ],
        ];

        foreach ($trainers as $trainer) {
            if (!$trainer['user_id']) continue;
            DB::table('trainers')->updateOrInsert(
                ['user_id' => $trainer['user_id']],
                array_merge($trainer, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
