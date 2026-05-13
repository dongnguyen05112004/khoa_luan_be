<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ActivityLogSeeder
 * Log hoạt động thực tế của các tác nhân trong hệ thống
 * Cột: id, user_id, action, severity, target_type, target_id, old_values, new_values, ip_address
 */
class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $getUser = fn($email) => DB::table('users')->where('email', $email)->value('id');

        $adminId  = $getUser('admin@fitlifegym.vn');
        $mgr1Id   = $getUser('manager.q1@fitlifegym.vn');
        $mgr2Id   = $getUser('manager.bt@fitlifegym.vn');
        $mgr3Id   = $getUser('manager.gv@fitlifegym.vn');
        $staff1Id = $getUser('staff.mai@fitlifegym.vn');
        $staff2Id = $getUser('staff.duc@fitlifegym.vn');
        $staff3Id = $getUser('staff.linh@fitlifegym.vn');
        $staff4Id = $getUser('staff.tien@fitlifegym.vn');
        $staff5Id = $getUser('staff.phuong@fitlifegym.vn');
        $pt1Id    = $getUser('pt.minh@fitlifegym.vn');
        $pt2Id    = $getUser('pt.tuan@fitlifegym.vn');

        $logs = [
            // ===== ADMIN =====
            [$adminId, '2025-10-01 08:05:00', 'Khởi tạo hệ thống và cấu hình phòng gym Gò Vấp', 'info', 'Branch', '3', null, '{"branch":"Gò Vấp","capacity":120}', '192.168.1.1'],
            [$adminId, '2025-10-01 09:00:00', 'Cấu hình API Gemini AI cho module gợi ý tập luyện', 'info', 'SystemSetting', null, null, null, '192.168.1.1'],
            [$adminId, '2025-11-20 10:00:00', 'Tạo chương trình khuyến mãi Black Friday (BLACKFRI25)', 'info', 'Promotion', '2', null, '{"code":"BLACKFRI25","discount":25}', '192.168.1.1'],
            [$adminId, '2025-12-15 09:00:00', 'Tạo chương trình khuyến mãi Giáng Sinh (XMAS2025)', 'info', 'Promotion', '3', null, '{"code":"XMAS2025","discount":15}', '192.168.1.1'],
            [$adminId, '2025-12-28 11:00:00', 'Tạo chiến dịch HAPPY2026 cho Năm Mới', 'info', 'Promotion', '4', null, '{"code":"HAPPY2026","discount":20}', '192.168.1.1'],
            [$adminId, '2026-01-05 09:00:00', 'Cập nhật cấu hình hệ thống AI - bật tính năng giữ chân hội viên', 'info', 'SystemSetting', null, '{"enable_ai_recommendation":"false"}', '{"enable_ai_recommendation":"true"}', '192.168.1.1'],
            [$adminId, '2026-02-25 10:00:00', 'Tạo chiến dịch WOMEN0803 ngày 8/3', 'info', 'Promotion', '6', null, '{"code":"WOMEN0803","discount":20}', '192.168.1.1'],
            [$adminId, '2026-04-28 09:00:00', 'Tạo chiến dịch SUMMER2026 - Hè 2026', 'info', 'Promotion', '8', null, '{"code":"SUMMER2026","discount":20}', '192.168.1.1'],
            [$adminId, '2026-05-01 10:00:00', 'Xuất báo cáo tổng hợp Q1/2026 (Tháng 1-3)', 'info', 'BusinessReport', null, null, null, '192.168.1.1'],

            // ===== MANAGER Q1 =====
            [$mgr1Id, '2025-10-05 08:30:00', 'Xác nhận đăng ký gói tập cho hội viên mới (Nguyễn Văn An)', 'info', 'MemberSubscription', '1', null, null, '192.168.1.10'],
            [$mgr1Id, '2025-11-12 09:00:00', 'Ghi nhận sự cố điều hòa phòng Cardio, gửi báo cáo bảo trì', 'warning', 'Equipment', null, null, '{"issue":"Điều hòa khu Cardio bị hỏng"}', '192.168.1.10'],
            [$mgr1Id, '2025-12-01 10:00:00', 'Tạo báo cáo doanh thu tháng 11/2025 - Q1', 'info', 'BusinessReport', null, null, '{"revenue":42000000,"branch":"Q1"}', '192.168.1.10'],
            [$mgr1Id, '2026-01-18 09:00:00', 'Ghi nhận bảo trì định kỳ: Máy chạy bộ Technogym Q1', 'info', 'EquipmentMaintenance', null, null, '{"equipment":"TG-RUN-001","cost":800000}', '192.168.1.10'],
            [$mgr1Id, '2026-03-08 14:00:00', 'Phê duyệt chi phí thay dây cáp cable crossover khẩn cấp', 'warning', 'OtherExpense', null, null, '{"expense_type":"misc","amount":1200000}', '192.168.1.10'],
            [$mgr1Id, '2026-04-05 10:00:00', 'Ký hợp đồng influencer review FitLife Q1', 'info', 'OtherExpense', null, null, '{"expense_type":"marketing","amount":8000000}', '192.168.1.10'],

            // ===== MANAGER BÌNH THẠNH =====
            [$mgr2Id, '2025-10-10 09:00:00', 'Xác nhận đăng ký gói 3 tháng cho hội viên Lê Văn Bảo', 'info', 'MemberSubscription', null, null, null, '192.168.1.20'],
            [$mgr2Id, '2025-12-08 11:00:00', 'Ghi nhận sự cố gương phòng tập bị vỡ, xử lý khẩn', 'warning', 'OtherExpense', null, null, '{"issue":"Gương vỡ","cost":2800000}', '192.168.1.20'],
            [$mgr2Id, '2026-02-20 10:00:00', 'Bảo trì khẩn cấp máy leg press Bình Thạnh', 'warning', 'EquipmentMaintenance', null, null, '{"equipment":"LGP-002","cost":850000}', '192.168.1.20'],
            [$mgr2Id, '2026-03-08 09:00:00', 'Tổ chức sự kiện ngày 8/3 tại cơ sở - thu hút 35 hội viên nữ', 'info', 'OtherExpense', null, null, '{"event":"8/3 Event","cost":5000000,"attendees":35}', '192.168.1.20'],

            // ===== MANAGER GÒ VẤP =====
            [$mgr3Id, '2025-10-01 07:30:00', 'Hoàn tất setup cơ sở Gò Vấp, sẵn sàng khai trương', 'info', 'Branch', '3', null, '{"status":"ready_to_open"}', '192.168.1.30'],
            [$mgr3Id, '2025-10-05 09:00:00', 'Xác nhận 12 đăng ký đầu tiên trong ngày khai trương', 'info', 'MemberSubscription', null, null, '{"new_registrations":12,"branch":"Gò Vấp"}', '192.168.1.30'],
            [$mgr3Id, '2026-02-25 10:00:00', 'Báo cáo máy leg press compact GV bị hỏng không sửa được', 'error', 'Equipment', null, '{"status":"active"}', '{"status":"broken","reason":"Không có linh kiện thay thế"}', '192.168.1.30'],
            [$mgr3Id, '2026-04-10 09:00:00', 'Bảo trì định kỳ Q2 cho 2 máy chạy bộ Life Fitness GV', 'info', 'EquipmentMaintenance', null, null, '{"equipment":"LF-T5-003","cost":750000}', '192.168.1.30'],

            // ===== NHÂN VIÊN LỄ TÂN =====
            [$staff1Id, '2025-10-20 10:30:00', 'Xác nhận thanh toán gói 1 tháng cho khách vãng lai (tiền mặt)', 'info', 'Payment', null, null, '{"method":"cash","amount":500000}', '192.168.1.11'],
            [$staff1Id, '2025-11-25 11:00:00', 'Xử lý đơn đăng ký Black Friday x3 hội viên mới Q1', 'info', 'MemberSubscription', null, null, '{"promo":"BLACKFRI25","count":3}', '192.168.1.11'],
            [$staff1Id, '2026-01-15 14:30:00', 'Xác nhận gia hạn gói tập cho Nguyễn Văn An (chuyển khoản)', 'info', 'Payment', null, null, '{"method":"transfer","amount":1300000}', '192.168.1.11'],
            [$staff2Id, '2026-02-03 09:15:00', 'Check-in thủ công cho hội viên Trần Quốc Thắng (quên thẻ)', 'info', 'Checkin', null, null, '{"method":"manual","member":"Trần Quốc Thắng"}', '192.168.1.12'],
            [$staff2Id, '2026-03-10 17:00:00', 'Hỗ trợ hội viên Bùi Quốc Việt đăng ký gói WOMEN0803 cho người thân', 'info', 'MemberSubscription', null, null, '{"promo":"WOMEN0803"}', '192.168.1.12'],
            [$staff3Id, '2026-04-22 10:00:00', 'Xác nhận thanh toán MoMo gói 3 tháng hội viên Bình Thạnh', 'info', 'Payment', null, null, '{"method":"momo","amount":1300000}', '192.168.1.13'],
            [$staff4Id, '2026-01-27 09:30:00', 'Tư vấn và đăng ký hợp đồng PT cho hội viên Bình Thạnh', 'info', 'PtContract', null, null, '{"trainer":"PT Tuấn","sessions":20}', '192.168.1.14'],
            [$staff5Id, '2025-10-10 08:00:00', 'Check-in thủ công và hỗ trợ hội viên mới ngày đầu GV', 'info', 'Checkin', null, null, '{"branch":"Gò Vấp","count":8}', '192.168.1.15'],

            // ===== PT =====
            [$pt1Id, '2026-01-08 14:00:00', 'Tạo kế hoạch tập AI cho hội viên Nguyễn Văn An (12 tuần)', 'info', 'AiRecommendation', null, null, '{"type":"workout_plan","weeks":12}', '192.168.1.41'],
            [$pt1Id, '2026-03-12 09:05:00', 'AI tự động tạo báo cáo sức khỏe tháng 3 cho Nguyễn Văn An', 'info', 'AiRecommendation', null, null, '{"type":"health_assessment","weight_loss":4.2}', '192.168.1.41'],
            [$pt2Id, '2026-01-12 10:05:00', 'Tạo kế hoạch 12 tuần nâng cao cho Đỗ Văn Đông', 'info', 'AiRecommendation', null, null, '{"type":"workout_plan","level":"advanced"}', '192.168.1.43'],
            [$pt2Id, '2026-04-07 11:00:00', 'Cập nhật tiến trình hội viên Võ Văn Quân sau buổi PT lần 5', 'info', 'PtBooking', null, '{"used_sessions":4}', '{"used_sessions":5,"notes":"Squat tiến bộ tốt"}', '192.168.1.43'],
        ];

        $inserts = [];
        foreach ($logs as [$userId, $createdAt, $action, $severity, $targetType, $targetId, $oldVals, $newVals, $ip]) {
            $inserts[] = [
                'user_id'     => $userId,
                'action'      => $action,
                'severity'    => $severity,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'old_values'  => $oldVals,
                'new_values'  => $newVals,
                'ip_address'  => $ip,
                'created_at'  => $createdAt,
                'updated_at'  => $createdAt,
            ];
        }

        foreach (array_chunk($inserts, 50) as $chunk) {
            DB::table('activity_logs')->insert($chunk);
        }
    }
}
