<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Checkin;
use App\Models\User;

class CheckinSeeder extends Seeder
{
    public function run(): void
    {
        $members = User::whereIn('role_id', [4, 5])->where('branch_id', 1)->get();
        if ($members->isEmpty()) return;

        foreach ($members as $member) {
            // Generate 15-45 checkins for each member over the last 90 days
            $numCheckins = rand(15, 45);
            for ($i = 0; $i < $numCheckins; $i++) {
                $checkinDate = \Carbon\Carbon::now()->subDays(rand(1, 90))->setTime(rand(6, 18), rand(0, 59), 0);
                $duration = rand(45, 120);
                $checkoutDate = (clone $checkinDate)->addMinutes($duration);
                
                // 10% chance they forgot to checkout
                if (rand(1, 10) == 10) {
                    $checkoutDate = null;
                    $duration = null;
                }
                
                Checkin::create([
                    'user_id' => $member->id,
                    'branch_id' => 1,
                    'check_in_at' => $checkinDate,
                    'check_out_at' => $checkoutDate,
                    'duration' => $duration,
                    'schedule_done' => rand(0, 1) == 1,
                    'method' => rand(0, 1) == 1 ? 'qr' : 'face',
                    'notes' => null,
                ]);
            }
        }
    }
}
