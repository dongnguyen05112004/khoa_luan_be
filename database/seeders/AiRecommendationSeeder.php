<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AiRecommendation;
use App\Models\User;

class AiRecommendationSeeder extends Seeder
{
    public function run(): void
    {
        $members = User::where('role_id', 5)->where('branch_id', 1)->get();
        if ($members->isEmpty()) return;

        $types = ['workout_plan', 'nutrition', 'upgrade_plan', 'recovery'];
        $faker = \Faker\Factory::create('vi_VN');

        for ($i = 0; $i < 300; $i++) {
            $member = $members->random();
            $date = \Carbon\Carbon::now()->subDays(rand(1, 90));

            AiRecommendation::create([
                'user_id'             => $member->id,
                'recommendation_type' => collect($types)->random(),
                'title'               => 'Gợi ý từ AI - ' . $date->format('d/m'),
                'ai_diagnosis'        => $faker->sentence(10),
                'ai_suggestions'      => $faker->paragraph(2),
                'is_system_created'   => rand(0, 1) == 1,
                'ai_next'             => (clone $date)->addDays(rand(7, 30))->toDateString(),
                'created_at'          => $date,
                'updated_at'          => $date,
            ]);
        }
    }
}
