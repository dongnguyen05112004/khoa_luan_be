<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MemberFeedback;
use App\Models\User;
use App\Models\Trainer;
use App\Models\GymClass;

class MemberFeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $members  = User::where('role_id', 5)->where('branch_id', 1)->get();
        $trainers = Trainer::where('branch_id', 1)->get();
        $classes  = GymClass::where('branch_id', 1)->get();
        if ($members->isEmpty()) return;

        $faker = \Faker\Factory::create('vi_VN');

        for ($i = 0; $i < 200; $i++) {
            $isTrainer = rand(0, 1) === 0;
            $rating = rand(3, 5); // mostly positive
            if (rand(1, 10) > 8) $rating = rand(1, 2); // some negative
            
            $aiSentiment = $rating >= 4 ? 'positive' : ($rating === 3 ? 'neutral' : 'negative');
            $aiScore = $rating >= 4 ? (rand(70, 99) / 100) : ($rating === 3 ? (rand(40, 60) / 100) : (rand(10, 30) / 100));

            MemberFeedback::create([
                'user_id'      => $members->random()->id,
                'trainer_id'   => $isTrainer && $trainers->isNotEmpty() ? $trainers->random()->id : null,
                'class_id'     => !$isTrainer && $classes->isNotEmpty() ? $classes->random()->id : null,
                'rating'       => $rating,
                'title'        => 'Góp ý ngày ' . \Carbon\Carbon::now()->subDays(rand(1, 90))->format('d/m'),
                'comment'      => $faker->paragraph(2),
                'ai_sentiment' => $aiSentiment,
                'ai_score'     => $aiScore,
            ]);
        }
    }
}
