<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * GymClassSeeder - Các lớp học nhóm
 * Cột: id, class_name, trainer_id, branch_id, max_members, class_cost,
 *       description, schedule_date, created_at, updated_at
 */
class GymClassSeeder extends Seeder
{
    public function run(): void
    {
        $getTrainer = fn($email) => DB::table('trainers')
            ->whereIn('user_id', DB::table('users')->where('email', $email)->pluck('id'))
            ->value('id');

        $classes = [
            // ===== CHI NHÁNH QUẬN 1 =====
            [
                'class_name'    => 'Yoga Buổi Sáng - Thứ 2/4/6',
                'trainer_id'    => $getTrainer('pt.hoa@fitlifegym.vn'),
                'branch_id'     => 1,
                'max_members'   => 15,
                'class_cost'    => 0,
                'description'   => 'Lớp Yoga dành cho người mới bắt đầu. Tập trung vào hơi thở và tư thế cơ bản. Thời gian: 7:00 - 8:00 sáng.',
                'schedule_date' => '2025-10-06 07:00:00',
            ],
            [
                'class_name'    => 'HIIT Tổng Lực - Thứ 3/5/7',
                'trainer_id'    => $getTrainer('pt.minh@fitlifegym.vn'),
                'branch_id'     => 1,
                'max_members'   => 20,
                'class_cost'    => 0,
                'description'   => 'Bài tập HIIT cường độ cao, đốt cháy calo hiệu quả trong 45 phút. Phù hợp trình độ trung bình trở lên.',
                'schedule_date' => '2025-10-07 18:00:00',
            ],
            [
                'class_name'    => 'Pilates Căng Chỉnh - Thứ 2/4/6',
                'trainer_id'    => $getTrainer('pt.hoa@fitlifegym.vn'),
                'branch_id'     => 1,
                'max_members'   => 12,
                'class_cost'    => 0,
                'description'   => 'Lớp Pilates kéo giãn và chỉnh tư thế cột sống. Thời gian: 17:00 - 18:00.',
                'schedule_date' => '2025-10-06 17:00:00',
            ],
            [
                'class_name'    => 'Tăng Cơ Gym - Thứ 3/5',
                'trainer_id'    => $getTrainer('pt.minh@fitlifegym.vn'),
                'branch_id'     => 1,
                'max_members'   => 10,
                'class_cost'    => 200000,
                'description'   => 'Lớp học kỹ thuật tập tạ cơ bản và nâng cao. Phù hợp mọi trình độ. Thứ 3/5, 19:00-20:30.',
                'schedule_date' => '2025-10-07 19:00:00',
            ],
            // ===== CHI NHÁNH BÌNH THẠNH =====
            [
                'class_name'    => 'Zumba Dance - Thứ 2/4/6',
                'trainer_id'    => $getTrainer('pt.lan@fitlifegym.vn'),
                'branch_id'     => 2,
                'max_members'   => 25,
                'class_cost'    => 0,
                'description'   => 'Lớp nhảy Zumba năng động, kết hợp âm nhạc Latin vui nhộn. Đốt mỡ cực hiệu quả. 18:30-19:30.',
                'schedule_date' => '2025-10-06 18:30:00',
            ],
            [
                'class_name'    => 'Cardio Giảm Cân - Thứ 3/5/7',
                'trainer_id'    => $getTrainer('pt.lan@fitlifegym.vn'),
                'branch_id'     => 2,
                'max_members'   => 20,
                'class_cost'    => 0,
                'description'   => 'Chương trình cardio giảm cân 60 phút bao gồm warming up, cardio chính và cooldown. 6:30-7:30.',
                'schedule_date' => '2025-10-07 06:30:00',
            ],
            [
                'class_name'    => 'Powerlifting Cơ Bản - Thứ 2/5',
                'trainer_id'    => $getTrainer('pt.tuan@fitlifegym.vn'),
                'branch_id'     => 2,
                'max_members'   => 8,
                'class_cost'    => 300000,
                'description'   => 'Học kỹ thuật 3 bài cơ bản Squat, Bench Press, Deadlift đúng chuẩn. 19:00-20:30.',
                'schedule_date' => '2025-10-06 19:00:00',
            ],
            // ===== CHI NHÁNH GÒ VẤP =====
            [
                'class_name'    => 'Yoga Tối - Thứ 2/4/6',
                'trainer_id'    => $getTrainer('pt.thao@fitlifegym.vn'),
                'branch_id'     => 3,
                'max_members'   => 15,
                'class_cost'    => 0,
                'description'   => 'Lớp Yoga buổi tối nhẹ nhàng giúp giải tỏa stress sau ngày làm việc. 19:30-20:30.',
                'schedule_date' => '2025-10-06 19:30:00',
            ],
            [
                'class_name'    => 'Phục Hồi Chức Năng - Thứ 3/5/7',
                'trainer_id'    => $getTrainer('pt.thao@fitlifegym.vn'),
                'branch_id'     => 3,
                'max_members'   => 10,
                'class_cost'    => 150000,
                'description'   => 'Lớp tập chuyên biệt cho người cao tuổi và người có chấn thương. Nhẹ nhàng, an toàn. 9:00-10:00.',
                'schedule_date' => '2025-10-07 09:00:00',
            ],
            [
                'class_name'    => 'Thể Hình Nhập Môn - Thứ 2/4',
                'trainer_id'    => $getTrainer('pt.hung@fitlifegym.vn'),
                'branch_id'     => 3,
                'max_members'   => 12,
                'class_cost'    => 0,
                'description'   => 'Lớp giới thiệu các thiết bị gym và bài tập cơ bản cho người mới. Miễn phí cho hội viên mới tháng đầu. 17:00-18:30.',
                'schedule_date' => '2025-10-06 17:00:00',
            ],
        ];

        foreach ($classes as $class) {
            if (!$class['trainer_id']) continue;
            DB::table('classes')->updateOrInsert(
                ['class_name' => $class['class_name'], 'branch_id' => $class['branch_id']],
                array_merge($class, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
