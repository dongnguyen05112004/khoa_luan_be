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

        // Định nghĩa bản đồ logic cho các hành động (Bao gồm cả các action FE yêu cầu)
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
            'CONFIG_SYSTEM'   => ['severity' => 'warning', 'type' => 'App\Models\SystemSetting'],
            'CONFIG_API'      => ['severity' => 'critical', 'type' => 'App\Models\SystemSetting'],
            'AUTO_BACKUP'     => ['severity' => 'info', 'type' => 'Database'],
        ];

        $actionKeys = array_keys($actionMap);
        
        $totalRecords = 10000;
        $batchSize = 1000;
        $batchData = [];

        for ($i = 0; $i < $totalRecords; $i++) {
            $user = $users->random();
            $action = collect($actionKeys)->random();
            $logic = $actionMap[$action];

            // Dàn đều dữ liệu trong vòng 90 ngày qua (3 tháng)
            $date = Carbon::now()->subDays(rand(1, 90))->setTime(rand(6, 22), rand(0, 59), rand(0, 59));

            // Các giá trị thay đổi ngẫu nhiên
            $oldVal = $logic['severity'] === 'warning' ? json_encode(['status' => 'old_state']) : null;
            $newVal = json_encode(['details' => $action . ' executed successfully', 'status' => 'new_state']);

            $batchData[] = [
                'user_id'     => $user->id,
                'action'      => $action,
                'severity'    => $logic['severity'],
                'target_type' => $logic['type'],
                'target_id'   => $logic['type'] ? rand(1, 50) : null,
                'ip_address'  => rand(192, 200) . '.168.1.' . rand(2, 255),
                'old_values'  => $oldVal,
                'new_values'  => $newVal,
                'created_at'  => $date->format('Y-m-d H:i:s'),
                'updated_at'  => $date->format('Y-m-d H:i:s'),
            ];

            // Nếu mảng đủ lớn (theo batchSize), insert một lượt để tránh nặng bộ nhớ và chậm DB
            if (count($batchData) >= $batchSize) {
                ActivityLog::insert($batchData);
                $batchData = [];
            }
        }

        // Insert phần dư còn lại
        if (!empty($batchData)) {
            ActivityLog::insert($batchData);
        }
    }
}
