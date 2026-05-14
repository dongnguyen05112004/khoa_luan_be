<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\HealthMetric;
use App\Models\Checkin;
use App\Models\Branch;
use Carbon\Carbon;

// ── Tìm user member ──────────────────────────────────────────────────────
$user = User::where('email', 'member1_b1@gmail.com')->first();

if (!$user) {
    echo "❌ Không tìm thấy user member1_b1@gmail.com\n";
    exit(1);
}

echo "✅ Tìm thấy user: {$user->name} (ID: {$user->id})\n\n";

// ── Lấy branch đầu tiên ──────────────────────────────────────────────────
$branch = Branch::first();
if (!$branch) {
    echo "❌ Không tìm thấy Branch nào.\n";
    exit(1);
}

// =========================================================================
// PHẦN 1: HEALTH METRICS (12 tuần = Quarterly view đủ điểm)
// =========================================================================
echo "=== TẠO HEALTH METRICS (12 tuần) ===\n";

$deleted = HealthMetric::where('user_id', $user->id)->delete();
echo "Đã xoá $deleted bản ghi cũ\n";

$startWeight = 92.0;
$startFat    = 34.0;
$startMuscle = 26.0;
$height      = 168.0;

$metrics = [];
for ($week = 0; $week < 12; $week++) {
    $daysAgo    = (11 - $week) * 7;
    $recordDate = Carbon::now()->subDays($daysAgo)->toDateString();

    // Xu hướng cải thiện dần (có noise nhỏ để chart trông thực tế hơn)
    $noise      = (rand(-5, 5)) / 10;
    $weight     = round($startWeight - ($week * 0.6) + $noise, 1);
    $fat        = round($startFat    - ($week * 0.45) + ($noise / 2), 1);
    $muscle     = round($startMuscle + ($week * 0.2)  + abs($noise / 3), 1);
    $bmi        = round($weight / pow($height / 100, 2), 2);

    $metrics[] = [
        'user_id'             => $user->id,
        'record_date'         => $recordDate,
        'weight'              => max(70.0, $weight),
        'height'              => $height,
        'body_fat_percentage' => max(18.0, $fat),
        'muscle_mass_kg'      => $muscle,
        'bmi'                 => $bmi,
        'created_at'          => Carbon::now()->subDays($daysAgo),
        'updated_at'          => Carbon::now()->subDays($daysAgo),
    ];
}

foreach ($metrics as $i => $rec) {
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

echo "\n✅ Đã tạo " . count($metrics) . " bản ghi HealthMetric\n\n";

// =========================================================================
// PHẦN 2: CHECKINS (trải đều 3 tháng)
// =========================================================================
echo "=== TẠO CHECKINS (3 tháng, ~3 buổi/tuần) ===\n";

// Xoá checkins cũ của user này
$deletedCheckins = Checkin::where('user_id', $user->id)->withTrashed()->forceDelete();
echo "Đã xoá $deletedCheckins checkin cũ\n";

$checkinData = [];
$totalDays   = 84; // 12 tuần x 7 ngày

// Tạo checkin ~3 buổi/tuần (Thứ 2, 4, 6 và random thêm Thứ 7)
for ($daysAgo = $totalDays; $daysAgo >= 0; $daysAgo--) {
    $date    = Carbon::now()->subDays($daysAgo);
    $dayOfWeek = $date->dayOfWeek; // 0=Sun, 1=Mon, ..., 6=Sat

    // Tập vào Thứ 2 (1), 3 (3), 5 (5), và ~50% Thứ 7 (6)
    $shouldCheckin = in_array($dayOfWeek, [1, 3, 5]) || ($dayOfWeek == 6 && rand(0, 1));
    if (!$shouldCheckin) continue;

    $checkInHour   = rand(6, 10); // Buổi sáng 6h-10h
    $durationMin   = rand(45, 90); // 45-90 phút
    $checkInAt     = $date->copy()->setTime($checkInHour, rand(0, 59), 0);
    $checkOutAt    = $checkInAt->copy()->addMinutes($durationMin);

    $checkinData[] = [
        'user_id'      => $user->id,
        'branch_id'    => $branch->id,
        'check_in_at'  => $checkInAt,
        'check_out_at' => $checkOutAt,
        'duration'     => $durationMin,
        'schedule_done'=> true,
        'method'       => 'manual',
        'notes'        => null,
        'created_at'   => $checkInAt,
        'updated_at'   => $checkOutAt,
    ];
}

foreach ($checkinData as $rec) {
    Checkin::create($rec);
}

echo "✅ Đã tạo " . count($checkinData) . " checkin\n\n";

// =========================================================================
// TỔNG KẾT
// =========================================================================
echo "=============================================================\n";
echo "🎉 SEED HOÀN TẤT cho user: {$user->email}\n";
echo "=============================================================\n";
echo "  → HealthMetrics : " . count($metrics)     . " bản ghi (12 tuần)\n";
echo "  → Checkins      : " . count($checkinData) . " buổi  (3 tháng)\n\n";
echo "  Xem biểu đồ tại:\n";
echo "  ▶ Weekly    → ~2-3 điểm metrics + ~3 buổi checkin\n";
echo "  ▶ Monthly   → ~4-5 điểm metrics + ~13 buổi checkin\n";
echo "  ▶ Quarterly → ~12 điểm metrics  + ~37 buổi checkin\n";
