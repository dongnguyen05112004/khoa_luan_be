<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AiRecommendation;
use App\Models\User;

class AiRecommendationSeeder extends Seeder
{
    public function run(): void
    {
        $members = User::where('role_id', 5)->get();
        if ($members->isEmpty()) return;

        $recs = [
            [
                'user_id'             => $members->get(0)?->id,
                'recommendation_type' => 'workout_plan',
                'title'               => 'Lộ trình giảm mỡ 8 tuần cho nam',
                'ai_diagnosis'        => 'Hội viên có chỉ số mỡ cơ thể 22.5%, cần tập trung vào cardio và cơ lõi để đạt mục tiêu 18%.',
                'ai_suggestions'      => '4 buổi cardio/tuần, 2 buổi sức mạnh. Hạn chế carb sau 18:00. Uống đủ 2L nước/ngày.',
                'is_system_created'   => true,
                'ai_next'             => now()->addDays(7),
            ],
            [
                'user_id'             => $members->get(1)?->id,
                'recommendation_type' => 'nutrition',
                'title'               => 'Chế độ ăn hỗ trợ tập Yoga',
                'ai_diagnosis'        => 'Hội viên tập Yoga, cần chế độ dinh dưỡng hỗ trợ dẻo dai và phục hồi cơ bắp.',
                'ai_suggestions'      => 'Tăng protein thực vật, bổ sung Omega-3. Ăn nhẹ trước tập 1 tiếng. Thêm trái cây việt quất hàng ngày.',
                'is_system_created'   => false,
                'ai_next'             => now()->addDays(14),
            ],
            [
                'user_id'             => $members->get(2)?->id,
                'recommendation_type' => 'upgrade_plan',
                'title'               => 'Đề xuất nâng cấp gói tập VIP',
                'ai_diagnosis'        => 'Hội viên check-in đều đặn 4-5 lần/tuần với tần suất cao, gói hiện tại có thể chưa đủ đáp ứng.',
                'ai_suggestions'      => 'Nâng cấp lên gói 12 tháng VIP để tiết kiệm 20% và được sử dụng locker cá nhân + 2 buổi PT/tháng.',
                'is_system_created'   => true,
                'ai_next'             => now()->addDays(3),
            ],
        ];

        foreach ($recs as $rec) {
            if ($rec['user_id']) {
                AiRecommendation::create($rec);
            }
        }
    }
}
