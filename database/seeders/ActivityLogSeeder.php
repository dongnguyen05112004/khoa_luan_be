<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        // Định nghĩa bản đồ logic cho các hành động
        $actionMap = [
            'USER_LOGIN'      => ['severity' => 'info', 'type' => null],
            'VIEW_PROFILE'    => ['severity' => 'info', 'type' => null],
            'CHECKIN_MANUAL'  => ['severity' => 'info', 'type' => 'App\Models\Checkin'],
            'UPDATE_PROFILE'  => ['severity' => 'warning', 'type' => 'App\Models\MemberProfile'],
            'BOOK_PT'         => ['severity' => 'info', 'type' => 'App\Models\PtBooking'],
            'CANCEL_CLASS'    => ['severity' => 'warning', 'type' => 'App\Models\ClassRegistration'],
            'REGISTER_CLASS'  => ['severity' => 'info', 'type' => 'App\Models\ClassRegistration'],
            // Thêm các hành động của Admin để Dashboard có dữ liệu đẹp
            'UPDATE_MEMBER'   => ['severity' => 'warning', 'type' => 'App\Models\User'],
            'CREATE_PLAN'     => ['severity' => 'info', 'type' => 'App\Models\MembershipPlan'],
            'DELETE_CAMPAIGN' => ['severity' => 'critical', 'type' => 'App\Models\Promotion'],
            'CONFIG_SYSTEM'   => ['severity' => 'critical', 'type' => 'App\Models\SystemSetting'],
            'AUTO_BACKUP'     => ['severity' => 'info', 'type' => 'Database'],
        ];

        $actionKeys = array_keys($actionMap);

        for ($i = 0; $i < 1000; $i++) {
            $user = $users->random();
            $action = collect($actionKeys)->random();
            $logic = $actionMap[$action];

            // Dàn đều dữ liệu trong 90 ngày qua
            $date = Carbon::now()->subDays(rand(1, 90))->setTime(rand(6, 22), rand(0, 59), rand(0, 59));

            ActivityLog::create([
                'user_id'     => $user->id,
                'action'      => $action,
                'severity'    => $logic['severity'], // Cột mới
                'target_type' => $logic['type'],     // Cột mới
                'target_id'   => $logic['type'] ? rand(1, 50) : null, // Cột mới (sinh ID ngẫu nhiên từ 1-50)
                'ip_address'  => rand(192, 200) . '.168.1.' . rand(2, 255),
                'old_values'  => null,
                'new_values'  => json_encode(['details' => $action . ' executed successfully']),
                'created_at'  => $date,
                'updated_at'  => $date,
            ]);
        }
    }
}
