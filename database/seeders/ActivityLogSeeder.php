<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role_id', 1)->first();
        $staff = User::where('role_id', 3)->first();

        $logs = [
            ['user_id' => $admin?->id, 'action' => 'USER_LOGIN',          'ip_address' => '127.0.0.1',   'old_values' => null, 'new_values' => '{"email":"admin@gym.vn"}'],
            ['user_id' => $admin?->id, 'action' => 'CREATE_BRANCH',        'ip_address' => '127.0.0.1',   'old_values' => null, 'new_values' => '{"branch_name":"Chi nhánh Quận 1"}'],
            ['user_id' => $admin?->id, 'action' => 'CREATE_MEMBERSHIP_PLAN','ip_address' => '127.0.0.1',  'old_values' => null, 'new_values' => '{"plan_name":"Gói 1 Tháng Cơ Bản"}'],
            ['user_id' => $staff?->id, 'action' => 'CREATE_SUBSCRIPTION',   'ip_address' => '192.168.1.5','old_values' => null, 'new_values' => '{"user_id":7,"plan_id":1}'],
            ['user_id' => $staff?->id, 'action' => 'CHECKIN_MANUAL',        'ip_address' => '192.168.1.5','old_values' => null, 'new_values' => '{"user_id":7,"branch_id":1}'],
            ['user_id' => $admin?->id, 'action' => 'UPDATE_EQUIPMENT_STATUS','ip_address' => '127.0.0.1', 'old_values' => '{"status":"good"}', 'new_values' => '{"status":"maintenance"}'],
        ];

        foreach ($logs as $log) {
            if ($log['user_id']) {
                ActivityLog::create($log);
            }
        }
    }
}
