<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Checkin;
use App\Models\User;

class CheckinSeeder extends Seeder
{
    public function run(): void
    {
        $members = User::whereIn('role_id', [4, 5])->get();
        if ($members->isEmpty()) return;

        $checkins = [
            ['user_id' => $members->get(0)?->id, 'branch_id' => 1, 'check_in_at' => '2026-03-15 07:30:00', 'check_out_at' => '2026-03-15 09:15:00', 'duration' => 105, 'schedule_done' => true,  'method' => 'qr'],
            ['user_id' => $members->get(0)?->id, 'branch_id' => 1, 'check_in_at' => '2026-03-17 08:00:00', 'check_out_at' => '2026-03-17 09:30:00', 'duration' => 90,  'schedule_done' => true,  'method' => 'qr'],
            ['user_id' => $members->get(1)?->id, 'branch_id' => 1, 'check_in_at' => '2026-03-14 16:00:00', 'check_out_at' => '2026-03-14 17:45:00', 'duration' => 105, 'schedule_done' => true,  'method' => 'face'],
            ['user_id' => $members->get(2)?->id, 'branch_id' => 2, 'check_in_at' => '2026-03-16 18:00:00', 'check_out_at' => null,                  'duration' => null,'schedule_done' => false, 'method' => 'manual'],
            ['user_id' => $members->get(3)?->id, 'branch_id' => 2, 'check_in_at' => '2026-03-15 09:00:00', 'check_out_at' => '2026-03-15 10:30:00', 'duration' => 90,  'schedule_done' => true,  'method' => 'qr'],
            ['user_id' => $members->get(4)?->id, 'branch_id' => 3, 'check_in_at' => '2026-03-13 07:00:00', 'check_out_at' => '2026-03-13 08:30:00', 'duration' => 90,  'schedule_done' => true,  'method' => 'face'],
            ['user_id' => $members->get(0)?->id, 'branch_id' => 1, 'check_in_at' => '2026-03-18 07:30:00', 'check_out_at' => null,                  'duration' => null,'schedule_done' => false, 'method' => 'qr'],
        ];

        foreach ($checkins as $checkin) {
            if ($checkin['user_id']) {
                Checkin::create($checkin);
            }
        }
    }
}
