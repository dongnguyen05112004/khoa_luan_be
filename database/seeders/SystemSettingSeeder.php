<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SystemSettingSeeder - Cấu hình hệ thống thực tế
 * Cột: id, setting_key, setting_value, setting_group, description
 */
class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ===== Thông tin chung =====
            ['setting_key' => 'gym_name',       'setting_value' => 'FitLife GYM',               'setting_group' => 'general',      'description' => 'Tên thương hiệu phòng gym'],
            ['setting_key' => 'gym_slogan',     'setting_value' => 'Bứt Phá Giới Hạn - Sống Khỏe Mỗi Ngày', 'setting_group' => 'general', 'description' => 'Slogan thương hiệu'],
            ['setting_key' => 'gym_logo',       'setting_value' => '',                           'setting_group' => 'general',      'description' => 'Đường dẫn logo thương hiệu'],
            ['setting_key' => 'gym_hotline',    'setting_value' => '1800-1234',                  'setting_group' => 'general',      'description' => 'Số điện thoại hotline hỗ trợ'],
            ['setting_key' => 'gym_email',      'setting_value' => 'support@fitlifegym.vn',      'setting_group' => 'general',      'description' => 'Email liên hệ chính thức'],
            ['setting_key' => 'gym_website',    'setting_value' => 'https://fitlifegym.vn',      'setting_group' => 'general',      'description' => 'Website chính thức'],
            ['setting_key' => 'gym_fanpage',    'setting_value' => 'https://facebook.com/fitlifegym', 'setting_group' => 'general', 'description' => 'Fanpage Facebook'],

            // ===== Hệ thống & Bảo mật =====
            ['setting_key' => 'language',             'setting_value' => 'vi',      'setting_group' => 'system', 'description' => 'Ngôn ngữ mặc định hệ thống'],
            ['setting_key' => 'date_format',          'setting_value' => 'd/m/Y',   'setting_group' => 'system', 'description' => 'Định dạng hiển thị ngày tháng'],
            ['setting_key' => 'timezone',             'setting_value' => 'Asia/Ho_Chi_Minh', 'setting_group' => 'system', 'description' => 'Múi giờ hệ thống'],
            ['setting_key' => 'auto_logout_time',     'setting_value' => '30',      'setting_group' => 'system', 'description' => 'Tự động đăng xuất sau N phút không hoạt động'],
            ['setting_key' => 'max_login_attempts',   'setting_value' => '5',       'setting_group' => 'system', 'description' => 'Số lần đăng nhập sai tối đa trước khi khóa tài khoản'],
            ['setting_key' => 'session_lifetime_min', 'setting_value' => '120',     'setting_group' => 'system', 'description' => 'Thời gian tồn tại session (phút)'],

            // ===== Thanh toán =====
            ['setting_key' => 'currency',           'setting_value' => 'VND',                  'setting_group' => 'payment', 'description' => 'Đơn vị tiền tệ mặc định'],
            ['setting_key' => 'payment_methods',    'setting_value' => 'cash,transfer,momo,vnpay', 'setting_group' => 'payment', 'description' => 'Các phương thức thanh toán được hỗ trợ'],
            ['setting_key' => 'momo_merchant_code', 'setting_value' => '',                     'setting_group' => 'payment', 'description' => 'Mã merchant MoMo (đã mã hóa)'],
            ['setting_key' => 'vnpay_merchant_code','setting_value' => '',                     'setting_group' => 'payment', 'description' => 'Mã merchant VNPay (đã mã hóa)'],
            ['setting_key' => 'invoice_prefix',     'setting_value' => 'INV',                  'setting_group' => 'payment', 'description' => 'Tiền tố số hóa đơn'],

            // ===== Check-in =====
            ['setting_key' => 'max_checkin_per_day',       'setting_value' => '2',    'setting_group' => 'checkin', 'description' => 'Số lần check-in tối đa mỗi ngày/hội viên'],
            ['setting_key' => 'checkin_methods_enabled',   'setting_value' => 'qr,face,manual', 'setting_group' => 'checkin', 'description' => 'Phương thức check-in được kích hoạt'],
            ['setting_key' => 'face_recognition_enabled',  'setting_value' => 'false','setting_group' => 'checkin', 'description' => 'Bật/tắt nhận diện khuôn mặt'],
            ['setting_key' => 'checkout_auto_minutes',     'setting_value' => '180',  'setting_group' => 'checkin', 'description' => 'Tự động checkout sau N phút nếu hội viên quên'],

            // ===== Thông báo =====
            ['setting_key' => 'membership_expiry_warning_days', 'setting_value' => '7',  'setting_group' => 'notification', 'description' => 'Gửi cảnh báo hết hạn gói tập trước N ngày'],
            ['setting_key' => 'pt_booking_reminder_hours',      'setting_value' => '24', 'setting_group' => 'notification', 'description' => 'Nhắc nhở lịch tập PT trước N giờ'],
            ['setting_key' => 'inactivity_alert_days',          'setting_value' => '14', 'setting_group' => 'notification', 'description' => 'Cảnh báo hội viên không check-in sau N ngày'],
            ['setting_key' => 'enable_sms_notification',        'setting_value' => 'false', 'setting_group' => 'notification', 'description' => 'Bật/tắt thông báo SMS'],
            ['setting_key' => 'enable_email_notification',      'setting_value' => 'true',  'setting_group' => 'notification', 'description' => 'Bật/tắt thông báo email'],

            // ===== AI =====
            ['setting_key' => 'enable_ai_recommendation',        'setting_value' => 'true',  'setting_group' => 'ai', 'description' => 'Bật/tắt tính năng gợi ý AI'],
            ['setting_key' => 'ai_recommendation_interval_days', 'setting_value' => '30',    'setting_group' => 'ai', 'description' => 'Chu kỳ tạo gợi ý AI (ngày)'],
            ['setting_key' => 'ai_provider',                     'setting_value' => 'gemini','setting_group' => 'ai', 'description' => 'AI provider đang dùng (gemini/groq)'],
            ['setting_key' => 'ai_gemini_api_key',               'setting_value' => '',      'setting_group' => 'ai', 'description' => 'Gemini API Key (mã hóa)'],
            ['setting_key' => 'ai_groq_api_key',                 'setting_value' => '',      'setting_group' => 'ai', 'description' => 'Groq API Key (mã hóa)'],
            ['setting_key' => 'ai_retention_enabled',            'setting_value' => 'true',  'setting_group' => 'ai', 'description' => 'Bật tính năng AI giữ chân hội viên'],
            ['setting_key' => 'ai_feedback_analysis_enabled',    'setting_value' => 'true',  'setting_group' => 'ai', 'description' => 'Bật phân tích phản hồi bằng AI'],
        ];

        foreach ($settings as $s) {
            DB::table('system_settings')->updateOrInsert(
                ['setting_key' => $s['setting_key']],
                array_merge($s, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
