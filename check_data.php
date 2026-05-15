<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$memberCount = User::whereHas('role', function($q) { $q->where('role_name', 'member'); })->count();
$missingProfile = User::whereHas('role', function($q) { $q->where('role_name', 'member'); })
    ->whereDoesntHave('memberProfile')->count();
$missingSub = User::whereHas('role', function($q) { $q->where('role_name', 'member'); })
    ->whereDoesntHave('activeSubscription')->count();
$noCheckin30d = User::whereHas('role', function($q) { $q->where('role_name', 'member'); })
    ->whereDoesntHave('checkins', function($q) { $q->where('check_in_at', '>=', now()->subDays(30)); })->count();

echo "Total Members: $memberCount\n";
echo "Missing Profile: $missingProfile\n";
echo "Missing Active Subscription: $missingSub\n";
echo "No Check-in (last 30d): $noCheckin30d\n";
