<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\HealthMetric;
use App\Models\MemberProfile;

$members = User::whereHas('role', function($q) { $q->where('role_name', 'member'); })->limit(4)->get();

if ($members->count() < 4) {
    echo "Need at least 4 members. Found " . $members->count() . "\n";
    exit;
}

$profiles = [
    [
        'type' => 'Béo phì (Obese)',
        'weight' => 95.0,
        'height' => 165.0,
        'fat' => 35.0,
        'muscle' => 25.0,
        'goal' => 'Giảm cân, giảm mỡ thừa'
    ],
    [
        'type' => 'Gầy ốm / Suy dinh dưỡng (Underweight)',
        'weight' => 45.0,
        'height' => 165.0,
        'fat' => 10.0,
        'muscle' => 15.0,
        'goal' => 'Tăng cân, cải thiện sức khỏe'
    ],
    [
        'type' => 'Thể hình chuẩn (Athletic/Muscular)',
        'weight' => 75.0,
        'height' => 175.0,
        'fat' => 12.0,
        'muscle' => 40.0,
        'goal' => 'Duy trì vóc dáng, tăng cơ bắp'
    ],
    [
        'type' => 'Hơi mập (Overweight)',
        'weight' => 80.0,
        'height' => 168.0,
        'fat' => 28.0,
        'muscle' => 30.0,
        'goal' => 'Giảm mỡ bụng, săn chắc cơ thể'
    ],
];

foreach ($members as $index => $user) {
    $profileData = $profiles[$index];
    
    // Tạo 2 bản ghi để có thể test cả "so sánh tiến độ"
    HealthMetric::where('user_id', $user->id)->delete();
    
    // Bản ghi 1 (cách đây 1 tháng)
    HealthMetric::create([
        'user_id' => $user->id,
        'record_date' => now()->subMonth(),
        'weight' => $profileData['weight'] + ($index == 0 ? 3 : ($index == 1 ? -2 : 0)), // Béo thì nặng hơn xíu ở tháng trước
        'height' => $profileData['height'],
        'body_fat_percentage' => $profileData['fat'] + 2,
        'muscle_mass_kg' => $profileData['muscle'] - 1,
        'bmi' => round(($profileData['weight'] + 2) / pow($profileData['height'] / 100, 2), 2)
    ]);

    // Bản ghi 2 (Hiện tại)
    HealthMetric::create([
        'user_id' => $user->id,
        'record_date' => now(),
        'weight' => $profileData['weight'],
        'height' => $profileData['height'],
        'body_fat_percentage' => $profileData['fat'],
        'muscle_mass_kg' => $profileData['muscle'],
        'bmi' => round($profileData['weight'] / pow($profileData['height'] / 100, 2), 2)
    ]);
    
    // Cập nhật mục tiêu
    MemberProfile::updateOrCreate(
        ['user_id' => $user->id],
        ['health_notes' => $profileData['goal']]
    );

    echo "Đã tạo hồ sơ: {$profileData['type']} cho User ID {$user->id} ({$user->name})\n";
}
