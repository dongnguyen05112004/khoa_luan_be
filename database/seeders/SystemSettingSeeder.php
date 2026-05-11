<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;
use App\Models\ActivityLog;
use App\Models\User;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['setting_key' => 'gym_name',          'setting_value' => 'FitLife GYM',         'setting_group' => 'general',      'description' => 'Tên phòng gym'],
            ['setting_key' => 'gym_slogan',        'setting_value' => 'Nơi khởi nguồn sức mạnh', 'setting_group' => 'general',      'description' => 'Slogan thương hiệu'],
            ['setting_key' => 'gym_logo',          'setting_value' => '',                    'setting_group' => 'general',      'description' => 'Logo thương hiệu'],
            ['setting_key' => 'gym_hotline',        'setting_value' => '1800-9999',            'setting_group' => 'general',      'description' => 'Số điện thoại hotline'],
            ['setting_key' => 'membership_expiry_warning_days', 'setting_value' => '7',     'setting_group' => 'notification', 'description' => 'Số ngày cảnh báo hết hạn gói tập'],
            ['setting_key' => 'max_checkin_per_day',           'setting_value' => '2',     'setting_group' => 'checkin',      'description' => 'Số lần check-in tối đa mỗi ngày'],
            ['setting_key' => 'enable_ai_recommendation',      'setting_value' => 'true',  'setting_group' => 'ai',           'description' => 'Bật/tắt tính năng gợi ý AI'],
            ['setting_key' => 'ai_recommendation_interval',    'setting_value' => '7',     'setting_group' => 'ai',           'description' => 'Số ngày giữa các lần gợi ý AI'],
            ['setting_key' => 'currency',                      'setting_value' => 'VND',   'setting_group' => 'payment',      'description' => 'Đơn vị tiền tệ'],
            ['setting_key' => 'payment_methods',               'setting_value' => 'cash,transfer,momo,vnpay', 'setting_group' => 'payment', 'description' => 'Phương thức thanh toán hỗ trợ'],
            ['setting_key' => 'language',                      'setting_value' => 'vi',    'setting_group' => 'system',       'description' => 'Ngôn ngữ hiển thị hệ thống'],
            ['setting_key' => 'auto_logout_time',              'setting_value' => '30',    'setting_group' => 'system',       'description' => 'Thời gian hệ thống tự động đăng xuất (phút)'],
            ['setting_key' => 'date_format',                   'setting_value' => 'd/m/Y', 'setting_group' => 'system',       'description' => 'Định dạng hiển thị ngày tháng'],
        ];

        foreach ($settings as $s) {
            SystemSetting::firstOrCreate(['setting_key' => $s['setting_key']], $s);
        }
    }
}
