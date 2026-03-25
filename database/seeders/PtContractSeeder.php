<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PtContract;
use App\Models\PtBooking;
use App\Models\User;
use App\Models\Trainer;

class PtContractSeeder extends Seeder
{
    public function run(): void
    {
        $members  = User::where('role_id', 5)->get();
        $trainers = Trainer::all();
        if ($members->isEmpty() || $trainers->isEmpty()) return;

        $contracts = [
            [
                'user_id'        => $members->get(0)?->id,
                'trainer_id'     => $trainers->first()->id,
                'branch_id'      => 1,
                'total_sessions' => 10,
                'used_sessions'  => 4,
                'start_date'     => '2026-01-10',
                'end_date'       => '2026-03-10',
                'price'          => 3000000,
                'status'         => 'active',
            ],
            [
                'user_id'        => $members->get(2)?->id,
                'trainer_id'     => $trainers->get(1)?->id ?? $trainers->first()->id,
                'branch_id'      => 2,
                'total_sessions' => 20,
                'used_sessions'  => 8,
                'start_date'     => '2026-02-01',
                'end_date'       => '2026-05-01',
                'price'          => 5500000,
                'status'         => 'active',
            ],
            [
                'user_id'        => $members->get(4)?->id,
                'trainer_id'     => $trainers->first()->id,
                'branch_id'      => 3,
                'total_sessions' => 10,
                'used_sessions'  => 10,
                'start_date'     => '2025-11-01',
                'end_date'       => '2026-01-01',
                'price'          => 3000000,
                'status'         => 'completed',
            ],
        ];

        foreach ($contracts as $contract) {
            if ($contract['user_id']) {
                PtContract::firstOrCreate(
                    ['user_id' => $contract['user_id'], 'trainer_id' => $contract['trainer_id'], 'start_date' => $contract['start_date']],
                    $contract
                );
            }
        }
    }
}
