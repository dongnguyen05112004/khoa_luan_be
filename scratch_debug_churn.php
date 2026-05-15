<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Carbon\Carbon;

$members = User::whereHas('role', fn($q) => $q->where('role_name', 'member'))
    ->with([
        'memberProfile',
        'activeSubscription',
        'checkins' => fn($q) => $q->where('check_in_at', '>=', now()->subMonths(3)),
    ])
    ->latest()
    ->take(40)
    ->get();

echo "Total members found: " . $members->count() . "\n";

$membersData = $members->map(function ($member) {
    $daysLeft = 0;
    if ($member->activeSubscription?->end_date) {
        $daysLeft = (int) now()->diffInDays(
            Carbon::parse($member->activeSubscription->end_date),
            false
        );
    }

    $checkins = $member->checkins;
    $lastCheckin = $checkins->sortByDesc('check_in_at')->first();
    $daysSinceLastCheckin = $lastCheckin ? now()->diffInDays(Carbon::parse($lastCheckin->check_in_at)) : 999;
    
    $checkinsThisMonth = $checkins->where('check_in_at', '>=', now()->subDays(30))->count();
    $checkinsLastMonth = $checkins->where('check_in_at', '>=', now()->subDays(60))->where('check_in_at', '<', now()->subDays(30))->count();

    echo "User ID: {$member->id} | Days Since Last: {$daysSinceLastCheckin} | This Month: {$checkinsThisMonth} | Last Month: {$checkinsLastMonth} | Days Left: {$daysLeft}\n";

    return [
        'user_id'                 => $member->id,
        'days_since_last_checkin' => $daysSinceLastCheckin,
        'checkins_this_month'     => $checkinsThisMonth,
        'checkins_last_month'     => $checkinsLastMonth,
        'days_until_expiration'   => $daysLeft,
        'goal'                    => $member->memberProfile?->health_notes ?? 'Không rõ mục tiêu',
    ];
});

$jsonInput = json_encode($membersData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
echo "\nJSON Input Length: " . strlen($jsonInput) . " characters\n";
// echo $jsonInput;
