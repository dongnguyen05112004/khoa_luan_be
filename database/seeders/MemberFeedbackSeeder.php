<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * MemberFeedbackSeeder
 * Phản hồi thực tế của hội viên về gym, PT, lớp học
 * Cột: id, user_id, trainer_id, class_id, rating, email (nội dung phản hồi gốc),
 *       ai_sentiment, ai_score, comment, title, ai_topic, ai_severity
 */
class MemberFeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $getUser    = fn($email) => DB::table('users')->where('email', $email)->value('id');
        $getTrainer = fn($ptEmail) => DB::table('trainers')
            ->whereIn('user_id', DB::table('users')->where('email', $ptEmail)->pluck('id'))
            ->value('id');
        $getClass   = fn($name, $branchId) => DB::table('classes')
            ->where('class_name', 'like', "%{$name}%")
            ->where('branch_id', $branchId)
            ->value('id');

        // Format: [user_email, trainer_email|null, class_name_partial|null, branch_id|null,
        //          rating, content(email field), comment, title,
        //          ai_sentiment, ai_score, ai_topic, ai_severity, created_at]
        $feedbacks = [
            // ===== 5 sao - Tốt =====
            [
                'user'    => 'member.an@gmail.com',
                'trainer' => 'pt.minh@fitlifegym.vn',
                'class'   => null, 'branch' => null,
                'rating'  => 5,
                'email'   => 'Anh Minh dạy rất nhiệt tình, chuyên nghiệp. Sau 3 tháng tập tôi giảm được 8kg mà cơ vẫn tăng. Cảm ơn anh!',
                'comment' => 'PT Minh rất tận tâm, luôn theo dõi từng bài tập và nhắc nhở form. Recommend cho ai muốn thay đổi thể hình.',
                'title'   => 'Tuyệt vời sau 3 tháng cùng PT Minh',
                'ai_sentiment' => 'positive', 'ai_score' => 4.90,
                'ai_topic' => 'PT', 'ai_severity' => 'Low',
                'created_at' => '2026-01-10 15:30:00',
            ],
            [
                'user'    => 'member.phuong@gmail.com',
                'trainer' => 'pt.hoa@fitlifegym.vn',
                'class'   => null, 'branch' => null,
                'rating'  => 5,
                'email'   => 'Chị Hoa dạy Yoga rất chuẩn, nhẹ nhàng và dễ hiểu. Cơ sở sạch sẽ, máy móc tốt.',
                'comment' => 'Môi trường tập luyện thoải mái. Nhân viên lễ tân thân thiện. Sẽ giới thiệu bạn bè.',
                'title'   => 'Cơ sở Q1 rất tốt, đặc biệt lớp Yoga',
                'ai_sentiment' => 'positive', 'ai_score' => 4.85,
                'ai_topic' => 'PT', 'ai_severity' => 'Low',
                'created_at' => '2026-02-05 11:00:00',
            ],
            [
                'user'    => 'member.dung.bt@gmail.com',
                'trainer' => 'pt.tuan@fitlifegym.vn',
                'class'   => null, 'branch' => null,
                'rating'  => 5,
                'email'   => 'Anh Tuấn có chuyên môn rất cao, hiểu rõ kỹ thuật powerlifting. Lịch tập được xây dựng khoa học.',
                'comment' => 'Sau 4 tháng tập cùng PT Tuấn, squat của tôi lên từ 60kg lên 100kg. Đáng đồng tiền bỏ ra.',
                'title'   => 'PT Tuấn - Chuyên gia powerlifting thực thụ',
                'ai_sentiment' => 'positive', 'ai_score' => 4.95,
                'ai_topic' => 'PT', 'ai_severity' => 'Low',
                'created_at' => '2026-03-15 09:45:00',
            ],
            [
                'user'    => 'member.lan.q1@gmail.com',
                'trainer' => null,
                'class'   => 'Yoga', 'branch' => 1,
                'rating'  => 5,
                'email'   => 'Lớp Yoga buổi sáng tuyệt vời! Không khí trong lành, không nhạc ồn ào.',
                'comment' => 'Tôi đã thử nhiều phòng gym nhưng FitLife Q1 có không gian tập Yoga đẹp nhất. Thảm mới, sạch.',
                'title'   => 'Không gian Yoga chuẩn nhất trong vùng',
                'ai_sentiment' => 'positive', 'ai_score' => 4.80,
                'ai_topic' => 'Cơ sở vật chất', 'ai_severity' => 'Low',
                'created_at' => '2026-01-22 08:30:00',
            ],
            [
                'user'    => 'member.dong@gmail.com',
                'trainer' => 'pt.hung@fitlifegym.vn',
                'class'   => null, 'branch' => null,
                'rating'  => 5,
                'email'   => 'PT Hùng dạy hay, hiểu tâm lý học viên. Cơ sở Gò Vấp mới nên máy móc hiện đại.',
                'comment' => 'Tập được 3 tháng, cảm giác cơ thể thay đổi rõ rệt. PT Hùng luôn động viên khi tôi muốn bỏ cuộc.',
                'title'   => 'Cơ sở Gò Vấp: Mới, đẹp và PT tận tâm',
                'ai_sentiment' => 'positive', 'ai_score' => 4.88,
                'ai_topic' => 'PT', 'ai_severity' => 'Low',
                'created_at' => '2026-04-12 16:00:00',
            ],
            // ===== 4 sao - Khá tốt =====
            [
                'user'    => 'member.hanh@gmail.com',
                'trainer' => null,
                'class'   => 'HIIT', 'branch' => 1,
                'rating'  => 4,
                'email'   => 'Lớp HIIT khá hay nhưng đôi khi lớp quá đông (hơn 20 người), khó tập.',
                'comment' => 'Đề xuất tăng số buổi hoặc giới hạn số người đăng ký để không bị quá tải.',
                'title'   => 'HIIT tốt nhưng cần hạn chế số lượng',
                'ai_sentiment' => 'positive', 'ai_score' => 3.80,
                'ai_topic' => 'Lớp học', 'ai_severity' => 'Medium',
                'created_at' => '2026-02-18 19:00:00',
            ],
            [
                'user'    => 'member.binh@gmail.com',
                'trainer' => 'pt.hoa@fitlifegym.vn',
                'class'   => null, 'branch' => null,
                'rating'  => 4,
                'email'   => 'Tập cùng chị Hoa rất vui. Chỉ tiếc là đôi khi lịch hay bị dời.',
                'comment' => 'Mong rằng lịch PT sẽ ổn định hơn, tránh dời nhiều lần trong tuần.',
                'title'   => 'Chị Hoa dạy tốt - Lịch cần ổn định hơn',
                'ai_sentiment' => 'positive', 'ai_score' => 3.70,
                'ai_topic' => 'PT', 'ai_severity' => 'Low',
                'created_at' => '2025-12-20 14:00:00',
            ],
            [
                'user'    => 'member.khai@gmail.com',
                'trainer' => null,
                'class'   => null, 'branch' => null,
                'rating'  => 4,
                'email'   => 'Nhân viên lễ tân thân thiện, giải quyết vấn đề nhanh. Cơ sở sạch sẽ.',
                'comment' => 'Khu vực phòng tắm đôi khi thiếu nước nóng vào giờ cao điểm buổi sáng.',
                'title'   => 'Tổng thể tốt - Cần cải thiện phòng tắm',
                'ai_sentiment' => 'positive', 'ai_score' => 3.60,
                'ai_topic' => 'Vệ sinh', 'ai_severity' => 'Medium',
                'created_at' => '2026-01-15 11:30:00',
            ],
            [
                'user'    => 'member.bao.bt@gmail.com',
                'trainer' => 'pt.tuan@fitlifegym.vn',
                'class'   => null, 'branch' => null,
                'rating'  => 4,
                'email'   => 'PT Tuấn dạy rất chuyên nghiệp. Bãi đỗ xe hơi chật.',
                'comment' => 'Nội dung tập rất hay. Chỉ có điều bãi đậu xe khá chật vào buổi tối.',
                'title'   => 'PT chuyên nghiệp - Cần thêm chỗ đỗ xe',
                'ai_sentiment' => 'positive', 'ai_score' => 3.75,
                'ai_topic' => 'Cơ sở vật chất', 'ai_severity' => 'Low',
                'created_at' => '2025-12-28 18:00:00',
            ],
            [
                'user'    => 'member.uyen.gv@gmail.com',
                'trainer' => 'pt.thao@fitlifegym.vn',
                'class'   => null, 'branch' => null,
                'rating'  => 4,
                'email'   => 'Chị Thảo rất nhẹ nhàng, phù hợp với người có vấn đề lưng như tôi.',
                'comment' => 'Cơ sở Gò Vấp còn mới nên máy còn ít hơn Q1. Mong sẽ bổ sung thêm.',
                'title'   => 'PT phục hồi giỏi - Thiết bị GV cần bổ sung',
                'ai_sentiment' => 'positive', 'ai_score' => 3.65,
                'ai_topic' => 'Máy móc', 'ai_severity' => 'Medium',
                'created_at' => '2026-02-28 10:00:00',
            ],
            // ===== 3 sao - Trung bình =====
            [
                'user'    => 'member.giang@gmail.com',
                'trainer' => null,
                'class'   => null, 'branch' => null,
                'rating'  => 3,
                'email'   => 'Phòng gym ổn nhưng máy chạy số 2 hay bị lỗi, mất cả tuần mới sửa.',
                'comment' => 'Khi máy hỏng thì không có thông báo, đến nơi mới biết. Nên dán bảng hoặc gửi tin nhắn cho hội viên.',
                'title'   => 'Máy móc cần bảo trì nhanh hơn',
                'ai_sentiment' => 'neutral', 'ai_score' => 2.80,
                'ai_topic' => 'Máy móc', 'ai_severity' => 'High',
                'created_at' => '2025-11-18 20:00:00',
            ],
            [
                'user'    => 'member.hai@gmail.com',
                'trainer' => null,
                'class'   => 'Cardio', 'branch' => 2,
                'rating'  => 3,
                'email'   => 'Lớp cardio giảm cân thấy bình thường, không như quảng cáo. Nhạc hơi ồn.',
                'comment' => 'Mong PT điều chỉnh âm lượng nhạc phù hợp hơn, nhiều người muốn tập theo nhịp riêng.',
                'title'   => 'Lớp Cardio chưa ấn tượng',
                'ai_sentiment' => 'neutral', 'ai_score' => 2.50,
                'ai_topic' => 'Lớp học', 'ai_severity' => 'Medium',
                'created_at' => '2025-12-05 19:30:00',
            ],
            [
                'user'    => 'member.son@gmail.com',
                'trainer' => null,
                'class'   => null, 'branch' => null,
                'rating'  => 3,
                'email'   => 'Giờ cao điểm khu tạ tự do rất đông, không có đủ chỗ tập.',
                'comment' => 'Nên có hệ thống book chỗ hoặc phân luồng giờ tập để tránh chen chúc.',
                'title'   => 'Giờ cao điểm quá đông',
                'ai_sentiment' => 'neutral', 'ai_score' => 2.60,
                'ai_topic' => 'Cơ sở vật chất', 'ai_severity' => 'Medium',
                'created_at' => '2026-01-28 21:00:00',
            ],
            // ===== 2 sao - Không hài lòng =====
            [
                'user'    => 'member.tien@gmail.com',
                'trainer' => null,
                'class'   => null, 'branch' => null,
                'rating'  => 2,
                'email'   => 'Nhà vệ sinh không sạch, mùi khó chịu. Đã phản ánh nhiều lần nhưng không thay đổi.',
                'comment' => 'Vấn đề vệ sinh phòng tắm là nghiêm trọng. Nếu không khắc phục tôi sẽ không gia hạn.',
                'title'   => 'Vệ sinh phòng tắm rất kém',
                'ai_sentiment' => 'negative', 'ai_score' => 1.80,
                'ai_topic' => 'Vệ sinh', 'ai_severity' => 'Critical',
                'created_at' => '2026-03-05 22:00:00',
            ],
            [
                'user'    => 'member.manh@gmail.com',
                'trainer' => null,
                'class'   => null, 'branch' => null,
                'rating'  => 2,
                'email'   => 'Điều hòa phòng tập bị hỏng 2 tuần, tập trong thời tiết nóng rất khó chịu.',
                'comment' => 'Mùa hè mà điều hòa hỏng là không chấp nhận được. Mong quản lý xử lý nhanh.',
                'title'   => 'Điều hòa hỏng - Ảnh hưởng trải nghiệm tập',
                'ai_sentiment' => 'negative', 'ai_score' => 1.90,
                'ai_topic' => 'Máy móc', 'ai_severity' => 'Critical',
                'created_at' => '2026-04-28 20:30:00',
            ],
            // ===== 1 sao - Rất tệ =====
            [
                'user'    => 'member.zung@gmail.com',
                'trainer' => null,
                'class'   => null, 'branch' => null,
                'rating'  => 1,
                'email'   => 'Hủy gói mà không được hoàn tiền theo đúng chính sách đã cam kết lúc đăng ký.',
                'comment' => 'Nhân viên giải thích lòng vòng, không rõ ràng. Sẽ khiếu nại lên ban quản lý.',
                'title'   => 'Chính sách hoàn tiền không minh bạch',
                'ai_sentiment' => 'negative', 'ai_score' => 0.80,
                'ai_topic' => 'Dịch vụ', 'ai_severity' => 'Critical',
                'created_at' => '2026-04-02 17:00:00',
            ],
            // ===== Thêm một số phản hồi tích cực tháng 5 =====
            [
                'user'    => 'member.rung@gmail.com',
                'trainer' => 'pt.hung@fitlifegym.vn',
                'class'   => null, 'branch' => null,
                'rating'  => 5,
                'email'   => 'Mới đăng ký tuần này nhưng đã rất ấn tượng với cơ sở Gò Vấp!',
                'comment' => 'PT Hùng đánh giá thể lực rất bài bản, lên kế hoạch tập rõ ràng ngay từ đầu.',
                'title'   => 'Ấn tượng ngay từ buổi đầu tiên',
                'ai_sentiment' => 'positive', 'ai_score' => 4.90,
                'ai_topic' => 'PT', 'ai_severity' => 'Low',
                'created_at' => '2026-05-07 18:00:00',
            ],
            [
                'user'    => 'member.tam@gmail.com',
                'trainer' => 'pt.tuan@fitlifegym.vn',
                'class'   => null, 'branch' => null,
                'rating'  => 5,
                'email'   => 'Cơ sở Bình Thạnh rộng rãi, thoáng mát. PT Tuấn rất có tâm.',
                'comment' => 'Đặc biệt thích hệ thống AI phân tích chỉ số sức khỏe, rất trực quan.',
                'title'   => 'Ứng dụng AI phân tích ấn tượng',
                'ai_sentiment' => 'positive', 'ai_score' => 4.95,
                'ai_topic' => 'Công nghệ AI', 'ai_severity' => 'Low',
                'created_at' => '2026-05-06 10:30:00',
            ],
        ];

        foreach ($feedbacks as $fb) {
            $userId    = $getUser($fb['user']);
            $trainerId = $fb['trainer'] ? $getTrainer($fb['trainer']) : null;
            $classId   = ($fb['class'] && $fb['branch']) ? $getClass($fb['class'], $fb['branch']) : null;

            if (!$userId) continue;

            DB::table('member_feedbacks')->insert([
                'user_id'      => $userId,
                'trainer_id'   => $trainerId,
                'class_id'     => $classId,
                'rating'       => $fb['rating'],
                'email'        => $fb['email'],       // Đây là cột "email" của bảng (nội dung phản hồi gốc)
                'ai_sentiment' => $fb['ai_sentiment'],
                'ai_score'     => $fb['ai_score'],
                'comment'      => $fb['comment'],
                'title'        => $fb['title'],
                'ai_topic'     => $fb['ai_topic'],
                'ai_severity'  => $fb['ai_severity'],
                'created_at'   => $fb['created_at'],
                'updated_at'   => $fb['created_at'],
            ]);
        }
    }
}
