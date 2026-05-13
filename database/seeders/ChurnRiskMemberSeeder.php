<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * ChurnRiskMemberSeeder
 * Tạo ~20 hội viên có nguy cơ rời bỏ phòng gym theo 6 nhóm lý do thực tế:
 *  A - Không check-in >14 ngày (lười tập, mất động lực)
 *  B - Sắp hết hạn gói, chưa gia hạn (nhạy cảm giá / so sánh đối thủ)
 *  C - Check-in giảm đột ngột >70% (bận việc / chuyển ca làm)
 *  D - Phản hồi tiêu cực (PT không phù hợp / cơ sở vật chất kém)
 *  E - Chấn thương / sức khỏe (BMI cao, tiến độ kém)
 *  F - Đã hủy gói nhưng chưa chính thức báo nghỉ
 */
class ChurnRiskMemberSeeder extends Seeder
{
    public function run(): void
    {
        $roleId   = DB::table('roles')->where('role_name', 'member')->value('id') ?? 5;
        $ptId     = DB::table('trainers')->first()?->id ?? 1;
        $classId  = DB::table('classes')->first()?->id ?? 1;

        // =====================================================================
        // DANH SÁCH THÀNH VIÊN NGUY CƠ
        // Format: [full_name, email, phone, gender, branch_id, nhóm, health_notes]
        // =====================================================================
        $riskMembers = [
            // --- Nhóm A: Không check-in >14 ngày ---
            ['Trần Đức Minh',    'risk.minh.duc@gmail.com',   '0911001001', 'male',   1, 'A', 'Mục tiêu giảm 8kg trong 3 tháng. Thường xuyên viện cớ bận việc.'],
            ['Nguyễn Thị Phương','risk.phuong.nt@gmail.com',  '0911001002', 'female', 2, 'A', 'Muốn giảm mỡ bụng. Hay bỏ buổi tập vào thứ 6.'],
            ['Lê Văn Khởi',      'risk.khoi.lv@gmail.com',    '0911001003', 'male',   3, 'A', 'Tăng cơ toàn thân. Hay nghỉ tập khi trời mưa.'],

            // --- Nhóm B: Sắp hết hạn, chưa gia hạn ---
            ['Võ Thị Hằng',      'risk.hang.vt@gmail.com',    '0911002001', 'female', 1, 'B', 'Giảm 5kg trước hè. Đang cân nhắc chuyển sang phòng gym gần nhà.'],
            ['Phạm Quốc Toàn',   'risk.toan.pq@gmail.com',    '0911002002', 'male',   2, 'B', 'Tập cardio duy trì sức khỏe. Thấy giá tháng sau tăng, chưa quyết định gia hạn.'],
            ['Bùi Thị Xuân',     'risk.xuan.bt@gmail.com',    '0911002003', 'female', 3, 'B', 'Muốn cải thiện vóc dáng trước đám cưới tháng 7. Chưa chắc tiếp tục.'],

            // --- Nhóm C: Check-in giảm đột ngột ---
            ['Đặng Văn Hùng',    'risk.hung.dv@gmail.com',    '0911003001', 'male',   1, 'C', 'Tăng cơ tay vai. Vừa đổi ca làm việc sang ca tối, khó sắp xếp lịch.'],
            ['Hoàng Thị Lan',    'risk.lan.ht@gmail.com',     '0911003002', 'female', 2, 'C', 'Giảm cân, giữ dáng. Mới sinh con thứ 2, bận chăm con nhỏ.'],
            ['Ngô Văn Sơn',      'risk.son.nv@gmail.com',     '0911003003', 'male',   3, 'C', 'Tập toàn thân 4 ngày/tuần. Dự án công việc Q2 áp lực, hay bỏ buổi.'],

            // --- Nhóm D: Phản hồi tiêu cực ---
            ['Trịnh Thị Mai',    'risk.mai.tt@gmail.com',     '0911004001', 'female', 1, 'D', 'Giảm mỡ, tăng cơ. Không hài lòng với PT được phân công, muốn đổi người hướng dẫn.'],
            ['Đinh Quang Huy',   'risk.huy.dq@gmail.com',     '0911004002', 'male',   2, 'D', 'Tập cardio và sức mạnh. Phàn nàn máy treadmill hay hỏng, phòng tắm không sạch.'],
            ['Phan Thị Thu',     'risk.thu.pt@gmail.com',     '0911004003', 'female', 3, 'D', 'Yoga và giảm cân. Thất vọng vì lớp yoga bị hủy 3 lần liên tiếp do thiếu người.'],

            // --- Nhóm E: Chấn thương / tiến độ kém ---
            ['Lý Văn Đạt',       'risk.dat.lv@gmail.com',     '0911005001', 'male',   1, 'E', 'Tăng cơ. Bị đau đầu gối khi squat nặng 2 tuần trước, đang nghỉ tập phục hồi.'],
            ['Cao Thị Ngọc',     'risk.ngoc.ct@gmail.com',    '0911005002', 'female', 2, 'E', 'Giảm 10kg. Sau 3 tháng chỉ giảm 1.5kg, cảm thấy không có tiến triển, nản lòng.'],
            ['Vũ Minh Đức',      'risk.duc.vm@gmail.com',     '0911005003', 'male',   3, 'E', 'Phục hồi sau phẫu thuật vai. BS yêu cầu nghỉ tập thêm 1 tháng.'],

            // --- Nhóm F: Đã hủy tinh thần ---
            ['Lương Thị Hoa',    'risk.hoa.lt@gmail.com',     '0911006001', 'female', 1, 'F', 'Giữ dáng sau sinh. Vừa chuyển công tác ra Hà Nội, không thể tiếp tục.'],
            ['Tô Văn Bình',      'risk.binh.tv@gmail.com',    '0911006002', 'male',   2, 'F', 'Giảm cân và tăng cơ. Cảm thấy không khí tập không vui, hay bị người khác chiếm máy.'],
            ['Dương Thị Kim',    'risk.kim.dt@gmail.com',     '0911006003', 'female', 3, 'F', 'Yoga và thiền. Phòng gym quá ồn, không phù hợp với phong cách tập của mình.'],
            ['Huỳnh Văn Tài',    'risk.tai.hv@gmail.com',     '0911006004', 'male',   1, 'F', 'Tập thể hình. Thấy giá PT quá đắt so với thu nhập hiện tại, không kham nổi.'],
            ['Mạc Thị Tuyết',    'risk.tuyet.mt@gmail.com',   '0911006005', 'female', 2, 'F', 'Chạy bộ và zumba. Bạn tập cùng đã nghỉ, không có động lực đến một mình.'],
        ];

        // Tạo user
        foreach ($riskMembers as $m) {
            [$fullName, $email, $phone, $gender, $branchId, $group, $healthNotes] = $m;
            $user = DB::table('users')->where('email', $email)->first();
            if (!$user) {
                DB::table('users')->insert([
                    'name'        => 'hội viên',
                    'full_name'   => $fullName,
                    'email'       => $email,
                    'password'    => Hash::make('password'),
                    'role_id'     => $roleId,
                    'branch_id'   => $branchId,
                    'phone'       => $phone,
                    'gender'      => $gender,
                    'state'       => 'active',
                    'created_at'  => now()->subMonths(rand(1,4)),
                    'updated_at'  => now(),
                ]);
            }

            $userId = DB::table('users')->where('email', $email)->value('id');
            if (!$userId) continue;

            // Member profile
            if (!DB::table('member_profiles')->where('user_id', $userId)->exists()) {
                DB::table('member_profiles')->insert([
                    'user_id'      => $userId,
                    'health_notes' => $healthNotes,
                    'join_date'    => now()->subMonths(rand(1,4))->toDateString(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            // Subscription theo nhóm
            $this->createSubscription($userId, $group);

            // Check-in theo nhóm
            $this->createCheckins($userId, $branchId, $group);

            // Health metrics theo nhóm
            $this->createHealthMetrics($userId, $gender, $group);

            // Feedback theo nhóm D
            if ($group === 'D') {
                $this->createNegativeFeedback($userId, $ptId, $classId, $group, $fullName);
            }
        }
    }

    // =========================================================================
    private function createSubscription(int $userId, string $group): void
    {
        if (DB::table('member_subscriptions')->where('user_id', $userId)->exists()) return;

        $today = now()->toDateString();

        $configs = [
            'A' => ['plan_id' => 2, 'start' => now()->subDays(60)->toDateString(), 'days' => 90,  'status' => 'active',    'reason' => null],
            'B' => ['plan_id' => 1, 'start' => now()->subDays(28)->toDateString(), 'days' => 30,  'status' => 'active',    'reason' => null], // hết hạn trong 2 ngày
            'C' => ['plan_id' => 2, 'start' => now()->subDays(45)->toDateString(), 'days' => 90,  'status' => 'active',    'reason' => null],
            'D' => ['plan_id' => 2, 'start' => now()->subDays(30)->toDateString(), 'days' => 90,  'status' => 'active',    'reason' => null],
            'E' => ['plan_id' => 3, 'start' => now()->subDays(75)->toDateString(), 'days' => 180, 'status' => 'active',    'reason' => null],
            'F' => ['plan_id' => 2, 'start' => now()->subDays(50)->toDateString(), 'days' => 90,  'status' => 'cancelled', 'reason' => $this->cancelReasons[$group] ?? 'Lý do cá nhân.'],
        ];

        $cfg     = $configs[$group];
        $endDate = date('Y-m-d', strtotime($cfg['start'] . " +{$cfg['days']} days"));

        $prices = [1 => 500000, 2 => 1300000, 3 => 2400000];

        DB::table('member_subscriptions')->insert([
            'user_id'      => $userId,
            'plan_id'      => $cfg['plan_id'],
            'start_date'   => $cfg['start'],
            'end_date'     => $endDate,
            'price'        => $prices[$cfg['plan_id']] ?? 1300000,
            'status'       => $cfg['status'],
            'cancel_reason'=> $cfg['reason'],
            'created_at'   => $cfg['start'] . ' 10:00:00',
            'updated_at'   => now(),
        ]);
    }

    private array $cancelReasons = [
        'F' => 'Không còn phù hợp với lịch sinh hoạt cá nhân hiện tại.',
    ];

    // =========================================================================
    private function createCheckins(int $userId, int $branchId, string $group): void
    {
        if (DB::table('checkins')->where('user_id', $userId)->exists()) return;

        $inserts = [];
        $now     = now();

        // Check-in patterns theo nhóm
        switch ($group) {
            case 'A': // Bình thường 4 tuần đầu, sau đó mất tích >14 ngày
                for ($i = 42; $i >= 16; $i -= rand(2, 3)) {
                    $inserts[] = $this->makeCheckin($userId, $branchId, $now->copy()->subDays($i));
                }
                break;

            case 'B': // Vẫn tập bình thường, gói sắp hết
                for ($i = 28; $i >= 1; $i -= 2) {
                    $inserts[] = $this->makeCheckin($userId, $branchId, $now->copy()->subDays($i));
                }
                break;

            case 'C': // Tháng trước 12 buổi, tháng này chỉ 1-2 buổi
                for ($i = 60; $i >= 31; $i -= rand(4, 6)) {
                    $inserts[] = $this->makeCheckin($userId, $branchId, $now->copy()->subDays($i));
                }
                // Tháng này chỉ 1-2 lần rồi biến mất
                $inserts[] = $this->makeCheckin($userId, $branchId, $now->copy()->subDays(28));
                $inserts[] = $this->makeCheckin($userId, $branchId, $now->copy()->subDays(20));
                break;

            case 'D': // Giảm dần, thỉnh thoảng bỏ nhiều
                for ($i = 30; $i >= 1; $i -= rand(4, 8)) {
                    $inserts[] = $this->makeCheckin($userId, $branchId, $now->copy()->subDays($i));
                }
                break;

            case 'E': // Bình thường rồi dừng hẳn (chấn thương)
                for ($i = 70; $i >= 15; $i -= 3) {
                    $inserts[] = $this->makeCheckin($userId, $branchId, $now->copy()->subDays($i));
                }
                break;

            case 'F': // Thưa thớt, giảm dần
                for ($i = 50; $i >= 1; $i -= rand(7, 12)) {
                    $inserts[] = $this->makeCheckin($userId, $branchId, $now->copy()->subDays($i));
                }
                break;
        }

        if ($inserts) {
            foreach (array_chunk($inserts, 50) as $chunk) {
                DB::table('checkins')->insert($chunk);
            }
        }
    }

    private function makeCheckin(int $userId, int $branchId, $dt): array
    {
        $hour      = rand(6, 21);
        $checkIn   = $dt->copy()->setHour($hour)->setMinute(rand(0, 59));
        $checkOut  = $checkIn->copy()->addMinutes(rand(45, 120));
        return [
            'user_id'      => $userId,
            'branch_id'    => $branchId,
            'check_in_at'  => $checkIn->toDateTimeString(),
            'check_out_at' => $checkOut->toDateTimeString(),
            'method'       => 'qr',
            'created_at'   => $checkIn->toDateTimeString(),
            'updated_at'   => $checkIn->toDateTimeString(),
        ];
    }

    // =========================================================================
    private function createHealthMetrics(int $userId, string $gender, string $group): void
    {
        if (DB::table('health_metrics')->where('user_id', $userId)->exists()) return;

        // Số đo ban đầu theo nhóm (thực tế người Việt trung bình)
        $baseWeight = $gender === 'male' ? rand(72, 88) : rand(57, 68);
        $baseHeight = $gender === 'male' ? rand(167, 176) : rand(155, 163);
        $baseFat    = $gender === 'male' ? rand(20, 28)  : rand(25, 33);
        $baseMuscle = $gender === 'male' ? rand(34, 42)  : rand(25, 31);

        // Nhóm E: BMI cao hơn, tiến độ kém
        if ($group === 'E') {
            $baseWeight = $gender === 'male' ? rand(85, 98) : rand(68, 78);
            $baseFat    = $gender === 'male' ? rand(28, 35) : rand(32, 40);
        }

        $bmi = round($baseWeight / (($baseHeight / 100) ** 2), 1);

        $inserts = [];
        // Đo 1 lần ban đầu (~2 tháng trước)
        $inserts[] = [
            'user_id'              => $userId,
            'record_date'          => now()->subDays(60)->toDateString(),
            'weight'               => $baseWeight,
            'height'               => $baseHeight,
            'body_fat_percentage'  => $baseFat,
            'muscle_mass_kg'       => $baseMuscle,
            'bmi'                  => $bmi,
            'created_at'           => now()->subDays(60),
            'updated_at'           => now()->subDays(60),
        ];

        // Đo lần 2 (~30 ngày sau) — tiến độ kém (nhóm A, C, E, F) hoặc bình thường (B, D)
        $weightChange  = in_array($group, ['B', 'D']) ? rand(-2, -1) : rand(0, 1); // kém hoặc không đổi
        $fatChange     = in_array($group, ['B', 'D']) ? rand(-2, -1) : rand(0, 2);
        $muscleChange  = in_array($group, ['B', 'D']) ? rand(0, 1)   : rand(-1, 0);
        $newWeight  = $baseWeight + $weightChange;
        $newBmi     = round($newWeight / (($baseHeight / 100) ** 2), 1);

        $inserts[] = [
            'user_id'              => $userId,
            'record_date'          => now()->subDays(30)->toDateString(),
            'weight'               => $newWeight,
            'height'               => $baseHeight,
            'body_fat_percentage'  => max(10, $baseFat + $fatChange),
            'muscle_mass_kg'       => max(20, $baseMuscle + $muscleChange),
            'bmi'                  => $newBmi,
            'created_at'           => now()->subDays(30),
            'updated_at'           => now()->subDays(30),
        ];

        DB::table('health_metrics')->insert($inserts);
    }

    // =========================================================================
    private function createNegativeFeedback(int $userId, int $ptId, int $classId, string $group, string $fullName): void
    {
        if (DB::table('member_feedbacks')->where('user_id', $userId)->exists()) return;

        $feedbacks = [
            ['rating' => 2, 'comment' => 'PT không sát sao, chỉ đứng nhìn chứ không sửa động tác cho mình. Cảm giác lãng phí tiền.', 'ai_sentiment' => 'Negative', 'ai_topic' => 'Trainer Quality', 'ai_severity' => 'High'],
            ['rating' => 1, 'comment' => 'Máy chạy bộ hỏng liên tục, phòng tắm bẩn và có mùi. Nhân viên không xử lý khi phản ánh.', 'ai_sentiment' => 'Negative', 'ai_topic' => 'Facility', 'ai_severity' => 'Critical'],
            ['rating' => 2, 'comment' => 'Lớp yoga bị hủy 3 lần liên tiếp không báo trước. Mất thời gian đi đến rồi về tay không.', 'ai_sentiment' => 'Negative', 'ai_topic' => 'Class Management', 'ai_severity' => 'High'],
        ];

        $fb = $feedbacks[array_rand($feedbacks)];

        DB::table('member_feedbacks')->insert([
            'user_id'      => $userId,
            'trainer_id'   => $ptId,
            'class_id'     => $classId,
            'rating'       => $fb['rating'],
            'comment'      => $fb['comment'],
            'ai_sentiment' => $fb['ai_sentiment'],
            'ai_score'     => $fb['rating'] * 20.0,
            'ai_topic'     => $fb['ai_topic'],
            'ai_severity'  => $fb['ai_severity'],
            'created_at'   => now()->subDays(rand(5, 20)),
            'updated_at'   => now(),
        ]);
    }
}
