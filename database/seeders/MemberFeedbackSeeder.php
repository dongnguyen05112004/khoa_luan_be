<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MemberFeedback;
use App\Models\User;
use App\Models\Trainer;
use App\Models\GymClass;

class MemberFeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $members  = User::whereHas('role', fn($q) => $q->where('role_name', 'member'))->get();
        $trainers = Trainer::all();
        $classes  = GymClass::all();
        
        if ($members->isEmpty()) return;

        // Xóa dữ liệu cũ trước khi gen mới
        MemberFeedback::truncate();

        $feedbacks = [
            // Tích cực
            ['topic' => 'Vệ sinh', 'severity' => 'Low', 'rating' => 5, 'comment' => 'Phòng tập rất sạch sẽ, nhân viên vệ sinh dọn dẹp liên tục nên cảm thấy rất thoải mái.'],
            ['topic' => 'Máy móc', 'severity' => 'Low', 'rating' => 5, 'comment' => 'Dàn máy mới về tập rất sướng, đặc biệt là máy chạy bộ có màn hình cảm ứng xịn xò.'],
            ['topic' => 'PT', 'severity' => 'Low', 'rating' => 5, 'comment' => 'HLV hướng dẫn rất nhiệt tình, sửa tư vấn kỹ lưỡng giúp mình tránh chấn thương.'],
            ['topic' => 'Dịch vụ', 'severity' => 'Low', 'rating' => 5, 'comment' => 'Lễ tân niềm nở, luôn chào hỏi khách hàng rất lịch sự.'],
            ['topic' => 'Không gian', 'severity' => 'Low', 'rating' => 4, 'comment' => 'Phòng tập thoáng mát, âm nhạc vừa phải không quá ồn ào.'],
            
            // Trung lập
            ['topic' => 'Giá cả', 'severity' => 'Medium', 'rating' => 3, 'comment' => 'Giá gói tập hơi cao so với mặt bằng chung nhưng chất lượng cũng tạm ổn.'],
            ['topic' => 'Máy móc', 'severity' => 'Medium', 'rating' => 3, 'comment' => 'Một số máy tạ thỉnh thoảng hơi kêu, cần được bảo trì thường xuyên hơn.'],
            ['topic' => 'Vệ sinh', 'severity' => 'Medium', 'rating' => 3, 'comment' => 'Khu vực nhà tắm đôi khi hơi ướt, cần lau dọn khô ráo hơn.'],
            
            // Tiêu cực / Cần chú ý
            ['topic' => 'Máy móc', 'severity' => 'High', 'rating' => 2, 'comment' => 'Máy chạy bộ số 3 bị hỏng cả tuần nay mà chưa thấy sửa, rất bất tiện.'],
            ['topic' => 'PT', 'severity' => 'High', 'rating' => 2, 'comment' => 'HLV tập trung dùng điện thoại quá nhiều khi đang hướng dẫn khách tập.'],
            ['topic' => 'Dịch vụ', 'severity' => 'High', 'rating' => 1, 'comment' => 'Thái độ của một bạn nhân viên trực ca tối rất khó chịu với khách hàng.'],
            ['topic' => 'Vệ sinh', 'severity' => 'Critical', 'rating' => 1, 'comment' => 'Nhà vệ sinh nam bốc mùi rất nặng, không thể chấp nhận được ở một phòng tập cao cấp.'],
            ['topic' => 'Máy móc', 'severity' => 'Critical', 'rating' => 1, 'comment' => 'Cáp máy kéo xô bị sờn rách, rất nguy hiểm cho người tập nếu bị đứt.'],
            ['topic' => 'Dịch vụ', 'severity' => 'High', 'rating' => 2, 'comment' => 'Đăng ký PT nhưng thường xuyên bị hủy lịch mà không báo trước.'],
        ];

        for ($i = 0; $i < 100; $i++) {
            $sample = $feedbacks[array_rand($feedbacks)];
            
            $isTrainer = rand(0, 1) === 0;
            $rating = $sample['rating'];
            
            $aiSentiment = $rating >= 4 ? 'Positive' : ($rating === 3 ? 'Neutral' : 'Negative');
            $aiScore = $rating >= 4 ? (rand(70, 99) / 100) : ($rating === 3 ? (rand(40, 60) / 100) : (rand(10, 30) / 100));

            MemberFeedback::create([
                'user_id'      => $members->random()->id,
                'trainer_id'   => ($isTrainer && $trainers->isNotEmpty()) ? $trainers->random()->id : null,
                'class_id'     => (!$isTrainer && $classes->isNotEmpty()) ? $classes->random()->id : null,
                'rating'       => $rating,
                'title'        => 'Góp ý về ' . $sample['topic'],
                'comment'      => $sample['comment'],
                'ai_sentiment' => $aiSentiment,
                'ai_score'     => $aiScore,
                'ai_topic'     => $sample['topic'],
                'ai_severity'  => $sample['severity'],
                'created_at'   => \Carbon\Carbon::now()->subDays(rand(1, 60)),
            ]);
        }
    }
}
