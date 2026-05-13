<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * AiRecommendationSeeder
 * Các gợi ý AI cho hội viên dựa trên chỉ số sức khỏe, tần suất tập, phản hồi
 * Cột: id, user_id, recommendation_type, ai_diagnosis, title, ai_suggestions,
 *       is_system_created, created_at_custom, ai_next, created_at, updated_at
 */
class AiRecommendationSeeder extends Seeder
{
    public function run(): void
    {
        $getUser = fn($email) => DB::table('users')->where('email', $email)->value('id');

        $recs = [
            // ===== HEALTH ASSESSMENT (Đánh giá sức khỏe) =====
            [
                'user'    => 'member.an@gmail.com',
                'type'    => 'health_assessment',
                'title'   => 'Đánh Giá Tiến Trình Tháng 3/2026',
                'diag'    => 'Hội viên đã giảm 4.2kg sau 5 tháng luyện tập. Chỉ số mỡ cơ thể giảm từ 24.5% xuống 20.8%. Tỷ lệ cơ tăng tốt. BMI hiện tại 23.1 - trong ngưỡng bình thường.',
                'sugg'    => "1. Duy trì chế độ ăn protein cao (1.6-2g/kg cân nặng)\n2. Tăng cường bài tập sức mạnh 4 buổi/tuần\n3. Bổ sung BCAA trước và sau buổi tập\n4. Đảm bảo ngủ đủ 7-8 tiếng/ngày để tái tạo cơ\n5. Kiểm tra lại chỉ số sau 4 tuần",
                'system'  => true,
                'custom'  => '2026-03-12 09:00:00',
                'next'    => '2026-04-12 09:00:00',
                'created' => '2026-03-12 09:05:00',
            ],
            [
                'user'    => 'member.em@gmail.com',
                'type'    => 'health_assessment',
                'title'   => 'Cảnh Báo: Tiến Trình Chậm - Cần Điều Chỉnh',
                'diag'    => 'Sau 4 tháng tập, cân nặng hầu như không thay đổi (dao động -0.5kg). Tần suất check-in chỉ đạt 2 buổi/tuần trong tháng 2. AI phát hiện sự sụt giảm động lực.',
                'sugg'    => "1. Đặt mục tiêu ngắn hạn cụ thể (4 tuần) để dễ theo dõi\n2. Thay đổi lịch tập: thử cardio buổi sáng thay vì tối\n3. Cân nhắc đăng ký gói PT cá nhân để có người đồng hành\n4. Điều chỉnh chế độ ăn: giảm tinh bột sau 18:00\n5. Đặt nhắc nhở tập qua ứng dụng mỗi ngày",
                'system'  => true,
                'custom'  => '2026-03-05 10:00:00',
                'next'    => '2026-04-05 10:00:00',
                'created' => '2026-03-05 10:02:00',
            ],
            [
                'user'    => 'member.binh.gv@gmail.com',
                'type'    => 'health_assessment',
                'title'   => 'Đánh Giá Sau 6 Tháng - Kết Quả Xuất Sắc',
                'diag'    => 'Hội viên đạt tiến bộ vượt bậc sau 6 tháng. Giảm 7.5kg, mỡ từ 28% xuống 21.5%. Cơ bắp tăng +3.2kg. Check-in đều đặn 5 buổi/tuần.',
                'sugg'    => "1. Xuất sắc! Có thể tăng cường độ bài tập lên mức advanced\n2. Xem xét tham gia các cuộc thi thể hình nội bộ\n3. Bổ sung bài tập mobility để tránh chấn thương\n4. Tăng khẩu phần protein lên 2-2.2g/kg để duy trì cơ\n5. Duy trì check-in đều đặn như hiện tại",
                'system'  => true,
                'custom'  => '2026-05-01 09:00:00',
                'next'    => '2026-06-01 09:00:00',
                'created' => '2026-05-01 09:03:00',
            ],
            // ===== WORKOUT PLAN (Kế hoạch tập luyện) =====
            [
                'user'    => 'member.khai@gmail.com',
                'type'    => 'workout_plan',
                'title'   => 'Kế Hoạch Tập 4 Tuần Tháng 1/2026',
                'diag'    => 'Hội viên mới bắt đầu tập từ 12/2025, chưa có nền tảng. Cần chương trình full body cho người mới.',
                'sugg'    => "TUẦN 1-2 (Nền tảng):\n- T2/T4/T6: Full body (squat, bench, row)\n- Mỗi bài 3 sets x 10 reps, nghỉ 90 giây\n\nTUẦN 3-4 (Tăng cường):\n- T2/T5: Upper body\n- T3/T6: Lower body + core\n- Cardio 20 phút sau tập\n\nLưu ý: Ưu tiên học form đúng trước khi tăng tạ",
                'system'  => false,
                'custom'  => '2026-01-08 14:00:00',
                'next'    => '2026-02-08 14:00:00',
                'created' => '2026-01-08 14:05:00',
            ],
            [
                'user'    => 'member.dong@gmail.com',
                'type'    => 'workout_plan',
                'title'   => 'Kế Hoạch 12 Tuần - Tăng Cơ Nâng Cao',
                'diag'    => 'Hội viên đã có nền tảng 3 năm, kỹ thuật tốt. Mục tiêu: tăng cơ mạnh chuyên sâu.',
                'sugg'    => "PHASE 1 (4 tuần) - VOLUME:\n- T2: Chest/Tri - 4 sets x 8-12 reps\n- T3: Back/Bi - 4 sets x 8-12 reps\n- T4: Rest hoặc cardio nhẹ\n- T5: Legs/Shoulders - 5 sets x 8-12\n- T6: Arms + core\n\nPHASE 2 (4 tuần) - STRENGTH: Tăng tạ, giảm reps (5x5)\n\nPHASE 3 (4 tuần) - DELOAD + Reassessment",
                'system'  => false,
                'custom'  => '2026-01-12 10:00:00',
                'next'    => '2026-04-12 10:00:00',
                'created' => '2026-01-12 10:05:00',
            ],
            // ===== NUTRITION (Dinh dưỡng) =====
            [
                'user'    => 'member.phuong@gmail.com',
                'type'    => 'nutrition',
                'title'   => 'Kế Hoạch Dinh Dưỡng Giảm Cân Khoa Học',
                'diag'    => 'Hội viên nữ, 52kg, mục tiêu giảm 5kg trong 3 tháng. Chế độ ăn hiện tại thiếu protein, thừa tinh bột.',
                'sugg'    => "TDEE ước tính: ~1,650 kcal/ngày\nDeficit khuyến nghị: 300-400 kcal\n\nMẪU THỰC ĐƠN:\n- Sáng: 2 trứng + yến mạch + sữa không đường\n- Trưa: 150g ức gà + cơm gạo lứt + rau xanh\n- Chiều tối (trước tập): chuối + whey\n- Tối: 120g cá/thịt bò + salad\n\nTránh: nước ngọt, bánh kẹo, đồ chiên xào sau 18h",
                'system'  => true,
                'custom'  => '2026-02-10 11:00:00',
                'next'    => '2026-03-10 11:00:00',
                'created' => '2026-02-10 11:03:00',
            ],
            [
                'user'    => 'member.lan.q1@gmail.com',
                'type'    => 'nutrition',
                'title'   => 'Gợi Ý Dinh Dưỡng Duy Trì Cân Nặng',
                'diag'    => 'Hội viên đã đạt mục tiêu cân nặng. Cần chuyển sang chế độ duy trì và tăng chất lượng cơ.',
                'sugg'    => "Chuyển sang chế độ MAINTENANCE:\n- Tăng kcal thêm ~200 so với deficit cũ\n- Protein: 1.8g/kg cân nặng\n- Carb: tập trung trước và sau tập\n- Fat: 0.8-1g/kg, ưu tiên omega-3\n\nBổ sung: vitamin D, magie, omega-3 cá hồi",
                'system'  => true,
                'custom'  => '2026-04-01 09:00:00',
                'next'    => '2026-05-01 09:00:00',
                'created' => '2026-04-01 09:02:00',
            ],
            // ===== RETENTION (Giữ chân hội viên sắp expire) =====
            [
                'user'    => 'member.giang@gmail.com',
                'type'    => 'retention',
                'title'   => 'Nhắc Nhở: Gói Tập Sắp Hết Hạn (7 Ngày)',
                'diag'    => 'Hội viên đã tập được 3 tháng với tần suất tốt. Gói tập 3 tháng sẽ hết hạn vào 23/02/2026. Hội viên chưa gia hạn.',
                'sugg'    => "Chào Đức! Bạn đã có 3 tháng tuyệt vời tại FitLife.\n\nGói tập của bạn còn 7 ngày. Gia hạn ngay hôm nay để:\n✓ Không mất tiến trình đang đạt được\n✓ Hưởng ưu đãi SUMMER2026 giảm 20%\n✓ Giữ nguyên lịch PT đã đặt\n\nLiên hệ lễ tân hoặc ứng dụng để gia hạn.",
                'system'  => true,
                'custom'  => '2026-02-16 08:00:00',
                'next'    => '2026-02-19 08:00:00',
                'created' => '2026-02-16 08:01:00',
            ],
            [
                'user'    => 'member.tu@gmail.com',
                'type'    => 'retention',
                'title'   => 'Gói Tập Đã Hết Hạn - Mời Gia Hạn',
                'diag'    => 'Hội viên có lịch sử check-in tốt trong 3 tháng (4 buổi/tuần). Gói tập hết hạn 20/04/2026, chưa gia hạn sau 23 ngày.',
                'sugg'    => "Chào Tú! Chúng tôi nhớ bạn!\n\nBạn đã không tập trong 23 ngày kể từ khi gói hết hạn. Cơ thể cần duy trì đều đặn.\n\n🎁 ƯU ĐÃI ĐẶC BIỆT CHO HỘI VIÊN CŨ:\n- Gói 3 tháng: giảm thêm 10%\n- Tặng 1 buổi tư vấn dinh dưỡng\n\nHãy liên hệ ngay: 028-3512-3456",
                'system'  => true,
                'custom'  => '2026-05-10 09:00:00',
                'next'    => '2026-05-17 09:00:00',
                'created' => '2026-05-10 09:01:00',
            ],
            // ===== INJURY PREVENTION (Phòng ngừa chấn thương) =====
            [
                'user'    => 'member.hanh@gmail.com',
                'type'    => 'injury_prevention',
                'title'   => 'Cảnh Báo: Tần Suất Tập Quá Cao - Nguy Cơ Overtraining',
                'diag'    => 'Hội viên check-in 7 buổi/tuần trong 2 tuần liên tiếp. AI phát hiện pattern này có thể dẫn đến overtraining và chấn thương.',
                'sugg'    => "⚠️ LƯU Ý QUAN TRỌNG:\n\nTập 7 ngày/tuần liên tục không mang lại hiệu quả tối ưu và tăng nguy cơ chấn thương.\n\nKhuyến nghị:\n1. Nghỉ ít nhất 1-2 ngày/tuần để cơ phục hồi\n2. Xen kẽ ngày tập nặng - nhẹ - nghỉ\n3. Ngủ đủ 7-8 tiếng, đặc biệt sau ngày tập chân\n4. Theo dõi đau nhức bất thường ở khớp\n5. Báo PT ngay nếu cảm thấy kiệt sức",
                'system'  => true,
                'custom'  => '2026-03-22 07:00:00',
                'next'    => '2026-04-01 07:00:00',
                'created' => '2026-03-22 07:02:00',
            ],
        ];

        foreach ($recs as $r) {
            $userId = $getUser($r['user']);
            if (!$userId) continue;

            DB::table('ai_recommendations')->insert([
                'user_id'             => $userId,
                'recommendation_type' => $r['type'],
                'ai_diagnosis'        => $r['diag'],
                'title'               => $r['title'],
                'ai_suggestions'      => $r['sugg'],
                'is_system_created'   => $r['system'] ? 1 : 0,
                'created_at_custom'   => $r['custom'],
                'ai_next'             => $r['next'],
                'created_at'          => $r['created'],
                'updated_at'          => $r['created'],
            ]);
        }
    }
}
