<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GymClass;
use App\Models\Trainer;

class GymClassSeeder extends Seeder
{
    public function run(): void
    {
        $trainers = Trainer::all();
        if ($trainers->isEmpty()) return;

        $classes = [
            [
                'class_name'  => 'Lớp Yoga Buổi Sáng',
                'trainer_id'  => $trainers->get(1)?->id ?? $trainers->first()->id,
                'branch_id'   => 2,
                'max_members' => 15,
                'class_cost'  => 100000,
                'description' => 'Yoga nhẹ nhàng, thư giãn tinh thần và tăng dẻo dai',
            ],
            [
                'class_name'  => 'Zumba Dance',
                'trainer_id'  => $trainers->get(1)?->id ?? $trainers->first()->id,
                'branch_id'   => 2,
                'max_members' => 20,
                'class_cost'  => 120000,
                'description' => 'Lớp nhảy Zumba năng động đốt cháy calories hiệu quả',
            ],
            [
                'class_name'  => 'CrossFit Beginner',
                'trainer_id'  => $trainers->get(2)?->id ?? $trainers->first()->id,
                'branch_id'   => 3,
                'max_members' => 12,
                'class_cost'  => 150000,
                'description' => 'CrossFit cho người mới bắt đầu, cường độ vừa phải',
            ],
            [
                'class_name'  => 'Boxing Fitness',
                'trainer_id'  => $trainers->first()->id,
                'branch_id'   => 1,
                'max_members' => 10,
                'class_cost'  => 180000,
                'description' => 'Lớp boxing kết hợp fitness, rèn phản xạ và thể lực',
            ],
            [
                'class_name'  => 'Cardio & Core',
                'trainer_id'  => $trainers->first()->id,
                'branch_id'   => 1,
                'max_members' => 25,
                'class_cost'  => 80000,
                'description' => 'Bài tập cardio và cơ lõi, phù hợp mọi trình độ',
            ],
        ];

        foreach ($classes as $class) {
            GymClass::firstOrCreate(['class_name' => $class['class_name'], 'branch_id' => $class['branch_id']], $class);
        }
    }
}
