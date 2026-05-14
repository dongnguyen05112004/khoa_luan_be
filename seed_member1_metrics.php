<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\HealthMetric;
use Carbon\Carbon;

// Tìm user member1_b1@gmail.com
$user = User::where('email', 'member1_b1@gmail.com')->first();

if (!$user) {
    echo "Không tìm thấy user member1_b1@gmail.com\n";
    exit(1);
}

echo "Tìm thấy user: {$user->name} (ID: {$user->id})\n";

// Xoá các bản ghi cũ của user này
$deleted = HealthMetric::where('user_id', $user->id)->delete();
echo "Đã xoá $deleted bản ghi cũ\n";

// Dữ liệu ban đầu (3 tháng trước): béo phì đang cải thiện
$startWeight  = 98.0;
$startFat     = 37.0;
$startMuscle  = 24.0;
$height       = 165.0;

// Tạo 12 bản ghi trải đều trong 3 tháng (mỗi tuần 1 bản)
$records = [];
for ($week = 0; $week < 12; $week++) {
    $daysAgo = (11 - $week) * 7; // Tuần 0 = 77 ngày trước, Tuần 11 = hôm nay
    $recordDate = Carbon::now()->subDays($daysAgo)->toDateString();

    // Tiến trình cải thiện dần theo thời gian
    $weight  = round($startWeight  - ($week * 0.5)  + (rand(-3, 3) / 10), 1); // Giảm ~0.5kg/tuần
    $fat     = round($startFat     - ($week * 0.4)  + (rand(-2, 2) / 10), 1); // Giảm ~0.4%/tuần
    $muscle  = round($startMuscle  + ($week * 0.15) + (rand(0, 2) / 10), 1);  // Tăng ~0.15kg/tuần
    $bmi     = round($weight / (($height / 100) ** 2), 2);

    $records[] = [
        'user_id'              => $user->id,
        'record_date'          => $recordDate,
        'weight'               => $weight,
        'height'               => $height,
        'body_fat_percentage'  => max(20.0, $fat),
        'muscle_mass_kg'       => $muscle,
        'bmi'                  => $bmi,
        'created_at'           => Carbon::now()->subDays($daysAgo),
        'updated_at'           => Carbon::now()->subDays($daysAgo),
    ];
}

// Insert tất cả
foreach ($records as $i => $rec) {
    HealthMetric::create($rec);
    echo sprintf(
        "Tuần %2d | %s | Cân: %5.1fkg | Mỡ: %4.1f%% | Cơ: %4.1fkg | BMI: %5.2f\n",
        $i + 1,
        $rec['record_date'],
        $rec['weight'],
        $rec['body_fat_percentage'],
        $rec['muscle_mass_kg'],
        $rec['bmi']
    );
}

echo "\n✅ Đã tạo " . count($records) . " bản ghi HealthMetric cho {$user->email}\n";
echo "   → Weekly view:    sẽ thấy ~1-2 điểm gần nhất\n";
echo "   → Monthly view:   sẽ thấy ~4 điểm (1 tháng)\n";
echo "   → Quarterly view: sẽ thấy ~12 điểm (3 tháng)\n";
