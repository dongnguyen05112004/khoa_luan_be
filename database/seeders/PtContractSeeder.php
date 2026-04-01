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
        $members  = User::where('role_id', 5)->where('branch_id', 1)->get();
        $trainers = Trainer::where('branch_id', 1)->get();
        if ($members->isEmpty() || $trainers->isEmpty()) return;

        // Give PT contracts to about 20% of members
        $ptMembers = $members->random(max(1, intval($members->count() * 0.2)));
        
        foreach ($ptMembers as $member) {
            $startDate = \Carbon\Carbon::now()->subDays(rand(10, 80));
            $totalSessions = rand(10, 30);
            $usedSessions = rand(0, $totalSessions);
            $endDate = (clone $startDate)->addMonths(3);

            PtContract::create([
                'user_id'        => $member->id,
                'trainer_id'     => $trainers->random()->id,
                'branch_id'      => 1,
                'total_sessions' => $totalSessions,
                'used_sessions'  => $usedSessions,
                'start_date'     => $startDate->toDateString(),
                'end_date'       => $endDate->toDateString(),
                'price'          => $totalSessions * 300000,
                'status'         => $usedSessions == $totalSessions ? 'completed' : 'active',
            ]);
        }
    }
}
