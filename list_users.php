<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (\App\Models\User::with('role')->get()->groupBy('role_id') as $users) {
    $user = $users->first();
    $roleName = $user->role ? $user->role->role_name : 'none';
    echo "{$user->email} - role: {$roleName} - pass: 12345678\n";
}
