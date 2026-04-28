<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Trainer;
use App\Models\PtContract;
use App\Models\PtBooking;
use App\Models\HealthMetric;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PtPhiTinSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo user PT Phi Tin
        $ptUser = User::updateOrCreate(
            ['email' => 'phitin@gym.vn'],
            [
                'name'        => 'Phi Tin',
                'full_name'   => 'PT Phi Tin',
                'email'       => 'phitin@gym.vn',
                'password'    => Hash::make('123456'),
                'role_id'     => 4,      // trainer
                'branch_id'   => 1,
                'phone'       => '0909999888',
                'gender'      => 'male',
                'card_number' => 'PT999',
                'state'       => 'active',
            ]
        );

        // 2. Tạo trainer record
        $trainer = Trainer::updateOrCreate(
            ['user_id' => $ptUser->id],
            [
                'branch_id'      => 1,
                'specialization' => 'Tăng cơ, Giảm mỡ, Thể hình toàn thân',
                'experience'     => 4,
                'description'    => 'PT chuyên nghiệp với 4 năm kinh nghiệm huấn luyện thể hình.',
                'pt_rate'        => 300000,
                'max_sessions'   => 20,
            ]
        );

        // 3. Tạo 5 hội viên test
        $memberData = [
            ['name' => 'Trần Thu Hà',       'email' => 'thuha.test@gmail.com',   'gender' => 'female', 'goal' => 'Giảm cân'],
            ['name' => 'Lê Hoàng Nam',      'email' => 'hoangnam.test@gmail.com','gender' => 'male',   'goal' => 'Tăng cơ'],
            ['name' => 'Nguyễn Thúy Chi',   'email' => 'thuychi.test@gmail.com', 'gender' => 'female', 'goal' => 'Duy trì vóc dáng'],
            ['name' => 'Phạm Văn Đức',      'email' => 'vanduc.test@gmail.com',  'gender' => 'male',   'goal' => 'Tăng sức mạnh'],
            ['name' => 'Hoàng Minh Phúc',   'email' => 'minhphuc.test@gmail.com','gender' => 'male',   'goal' => 'Phục hồi thể lực'],
        ];

        $members = [];
        foreach ($memberData as $i => $md) {
            $member = User::updateOrCreate(
                ['email' => $md['email']],
                [
                    'name'        => $md['name'],
                    'full_name'   => $md['name'],
                    'password'    => Hash::make('123456'),
                    'role_id'     => 5,
                    'branch_id'   => 1,
                    'phone'       => '090888' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'gender'      => $md['gender'],
                    'card_number' => 'TST' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'state'       => 'active',
                ]
            );
            $members[] = array_merge(['user' => $member], $md);
        }

        // 4. Tạo PT Contract + Bookings + Health Metrics cho mỗi hội viên
        $sessionConfigs = [
            ['total' => 12, 'used' => 5,  'future' => 2],
            ['total' => 16, 'used' => 8,  'future' => 1],
            ['total' => 8,  'used' => 3,  'future' => 1],
            ['total' => 20, 'used' => 10, 'future' => 2],
            ['total' => 10, 'used' => 2,  'future' => 1],
        ];

        foreach ($members as $idx => $m) {
            $member = $m['user'];
            $cfg    = $sessionConfigs[$idx];

            $startDate = Carbon::now()->subDays(rand(30, 60));
            $endDate   = (clone $startDate)->addMonths(3);

            // Xóa contract cũ nếu có (để tránh trùng)
            PtContract::where('user_id', $member->id)
                      ->where('trainer_id', $trainer->id)
                      ->delete();

            $contract = PtContract::create([
                'user_id'        => $member->id,
                'trainer_id'     => $trainer->id,
                'branch_id'      => 1,
                'total_sessions' => $cfg['total'],
                'used_sessions'  => $cfg['used'],
                'start_date'     => $startDate->toDateString(),
                'end_date'       => $endDate->toDateString(),
                'price'          => $cfg['total'] * 300000,
                'status'         => 'active',
            ]);

            // 4a. Tạo các buổi đã hoàn thành (done)
            for ($i = 0; $i < $cfg['used']; $i++) {
                $schedTime = (clone $startDate)->addDays($i * 3)->setTime(rand(7, 17), 0, 0);
                PtBooking::create([
                    'contract_id'   => $contract->id,
                    'trainer_id'    => $trainer->id,
                    'schedule_time' => $schedTime,
                    'status'        => 'done',
                    'notes'         => 'Buổi tập ' . ($i + 1) . ' hoàn thành tốt.',
                ]);
            }

            // 4b. Tạo các buổi tương lai (confirmed)
            for ($i = 0; $i < $cfg['future']; $i++) {
                $futureTime = Carbon::now()->addDays(($i + 1) * 3)->setTime(rand(7, 17), 0, 0);
                PtBooking::create([
                    'contract_id'   => $contract->id,
                    'trainer_id'    => $trainer->id,
                    'schedule_time' => $futureTime,
                    'status'        => 'confirmed',
                    'notes'         => 'Đã lên lịch - buổi ' . ($cfg['used'] + $i + 1),
                ]);
            }

            // 4c. Tạo health metrics (6 tháng gần nhất)
            $baseWeight  = [65, 80, 55, 90, 72][$idx];
            $baseHeight  = [165, 175, 158, 180, 170][$idx];
            $baseFat     = [25, 22, 28, 30, 20][$idx];
            $baseMuscle  = [35, 40, 30, 38, 42][$idx];

            // Xóa metrics cũ của member này
            HealthMetric::where('user_id', $member->id)->delete();

            for ($m2 = 5; $m2 >= 0; $m2--) {
                $recordDate = Carbon::now()->subMonths($m2)->startOfMonth()->addDays(rand(1, 5));
                $weight     = round($baseWeight - ($m2 * 0.5) + (rand(-3, 3) / 10), 1);
                $fat        = round($baseFat - ($m2 * 0.3) + (rand(-2, 2) / 10), 1);
                $muscle     = round($baseMuscle + ($m2 * 0.2) + (rand(-1, 2) / 10), 1);
                $bmi        = round($weight / (($baseHeight / 100) ** 2), 1);

                HealthMetric::create([
                    'user_id'             => $member->id,
                    'record_date'         => $recordDate->toDateString(),
                    'weight'              => $weight,
                    'height'              => $baseHeight,
                    'body_fat_percentage' => $fat,
                    'muscle_mass_kg'      => $muscle,
                    'bmi'                 => $bmi,
                ]);
            }
        }

        echo "\n✅ Đã tạo tài khoản PT Phi Tin thành công!\n";
        echo "   📧 Email   : phitin@gym.vn\n";
        echo "   🔑 Password: 123456\n";
        echo "   👥 Hội viên: " . count($members) . " người\n";
        echo "   📋 Hợp đồng: " . count($members) . " hợp đồng active\n";
        echo "   📅 Lịch tập: đã tạo buổi done + confirmed cho mỗi hội viên\n";
        echo "   📊 Chỉ số sức khỏe: 6 tháng dữ liệu cho mỗi hội viên\n\n";
    }
}
