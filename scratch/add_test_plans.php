<?php

use App\Models\MembershipPlan;

// Fix paths to point to project root
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$plans = [
    [
        'plan_name' => 'Gói Test 5.000đ',
        'duration_days' => 1,
        'price' => 5000,
        'description' => 'Gói tập thử nghiệm chức năng thanh toán',
        'status' => 'active'
    ],
    [
        'plan_name' => 'Gói Test 7.000đ',
        'duration_days' => 1,
        'price' => 7000,
        'description' => 'Gói tập thử nghiệm chức năng thanh toán',
        'status' => 'active'
    ]
];

foreach ($plans as $p) {
    MembershipPlan::updateOrCreate(
        ['plan_name' => $p['plan_name']],
        $p
    );
    echo "Added/Updated plan: {$p['plan_name']}\n";
}
