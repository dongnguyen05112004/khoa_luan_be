<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GymClass;
use App\Models\Trainer;

class GymClassSeeder extends Seeder
{
    public function run(): void
    {
        $trainers = Trainer::where('branch_id', 1)->get();
        if ($trainers->isEmpty()) return;

        $faker = \Faker\Factory::create('vi_VN');
        $classes = [
            'Yoga Cơ Bản', 'Yoga Nâng Cao', 'Zumba Dance', 'CrossFit', 'Boxing Fitness', 'Cardio & Core', 'Pilates', 'Kickboxing', 'HIIT'
        ];

        for ($i = 0; $i < 200; $i++) {
            $scheduleDate = \Carbon\Carbon::now()->subDays(rand(1, 90))->setTime(rand(6, 19), rand(0, 1) === 0 ? 0 : 30, 0);
            GymClass::create([
                'class_name'    => $faker->randomElement($classes) . ' - ' . $scheduleDate->format('d/m'),
                'trainer_id'    => $trainers->random()->id,
                'branch_id'     => 1,
                'max_members'   => rand(20, 50),
                'class_cost'    => rand(5, 20) * 10000,
                'description'   => $faker->sentence(10),
                'schedule_date' => $scheduleDate,
            ]);
        }
    }
}
