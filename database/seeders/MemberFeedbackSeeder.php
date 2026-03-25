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
        $members  = User::where('role_id', 5)->get();
        $trainers = Trainer::all();
        $classes  = GymClass::all();
        if ($members->isEmpty()) return;

        $feedbacks = [
            [
                'user_id'      => $members->get(0)?->id,
                'trainer_id'   => $trainers->first()?->id,
                'class_id'     => null,
                'rating'       => 5,
                'title'        => 'Huấn luyện viên tuyệt vời',
                'comment'      => 'PT rất tận tâm, hướng dẫn chi tiết và động viên tốt. Cực kỳ hài lòng!',
                'ai_sentiment' => 'positive',
                'ai_score'     => 0.95,
            ],
            [
                'user_id'      => $members->get(1)?->id,
                'trainer_id'   => null,
                'class_id'     => $classes->first()?->id,
                'rating'       => 4,
                'title'        => 'Lớp Yoga thư giãn',
                'comment'      => 'Lớp Yoga rất thư giãn, nhạc nền phù hợp, chỉ hơi đông một chút.',
                'ai_sentiment' => 'positive',
                'ai_score'     => 0.78,
            ],
            [
                'user_id'      => $members->get(2)?->id,
                'trainer_id'   => $trainers->get(1)?->id,
                'class_id'     => null,
                'rating'       => 3,
                'title'        => 'Cần cải thiện đúng giờ',
                'comment'      => 'Huấn luyện viên ổn nhưng hay đến muộn, cần cải thiện đúng giờ hơn.',
                'ai_sentiment' => 'neutral',
                'ai_score'     => 0.45,
            ],
            [
                'user_id'      => $members->get(3)?->id,
                'trainer_id'   => null,
                'class_id'     => $classes->get(1)?->id,
                'rating'       => 5,
                'title'        => 'Lớp Zumba cực vui!',
                'comment'      => 'Lớp Zumba cực kỳ vui và năng động! Sẽ tiếp tục đăng ký.',
                'ai_sentiment' => 'positive',
                'ai_score'     => 0.98,
            ],
            [
                'user_id'      => $members->get(4)?->id,
                'trainer_id'   => null,
                'class_id'     => null,
                'rating'       => 2,
                'title'        => 'Cơ sở vật chất cần cải thiện',
                'comment'      => 'Máy lạnh bị hỏng từ tuần trước vẫn chưa được sửa, rất bức bối khi tập.',
                'ai_sentiment' => 'negative',
                'ai_score'     => 0.15,
            ],
        ];

        foreach ($feedbacks as $fb) {
            if ($fb['user_id']) {
                MemberFeedback::create($fb);
            }
        }
    }
}
