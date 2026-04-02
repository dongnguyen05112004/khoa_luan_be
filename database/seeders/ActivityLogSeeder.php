<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        $actions = ['USER_LOGIN', 'VIEW_PROFILE', 'CHECKIN_MANUAL', 'UPDATE_PROFILE', 'BOOK_PT', 'CANCEL_CLASS', 'REGISTER_CLASS'];
        
        for ($i = 0; $i < 1000; $i++) {
            $user = $users->random();
            $action = collect($actions)->random();
            $date = \Carbon\Carbon::now()->subDays(rand(1, 90))->setTime(rand(6, 22), rand(0, 59), rand(0, 59));

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'ip_address' => rand(192, 200) . '.168.1.' . rand(2, 255),
                'old_values' => null,
                'new_values' => '{"details":"' . $action . ' executed"}',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
}
