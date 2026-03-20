<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trainer;
use App\Models\User;

class TrainerSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy các user có role_id = 4 (trainer)
        $trainerUsers = User::where('role_id', 4)->get();

        $data = [
            [
                'branch_id'      => 1,
                'specialization' => 'Tăng cơ giảm mỡ, Thể hình',
                'pt_rate'        => 300000,
                'max_sessions'   => 20,
            ],
            [
                'branch_id'      => 2,
                'specialization' => 'Yoga, Zumba, Aerobic',
                'pt_rate'        => 250000,
                'max_sessions'   => 25,
            ],
            [
                'branch_id'      => 3,
                'specialization' => 'CrossFit, Cardio, Powerlifting',
                'pt_rate'        => 350000,
                'max_sessions'   => 15,
            ],
        ];

        foreach ($trainerUsers as $i => $user) {
            Trainer::firstOrCreate(
                ['user_id' => $user->id],
                $data[$i] ?? ['branch_id' => 1, 'specialization' => 'Thể hình', 'pt_rate' => 200000, 'max_sessions' => 20]
            );
        }
    }
}
