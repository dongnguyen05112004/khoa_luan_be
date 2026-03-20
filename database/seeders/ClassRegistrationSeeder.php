<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassRegistration;
use App\Models\GymClass;
use App\Models\User;

class ClassRegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $members = User::where('role_id', 5)->get();
        $classes = GymClass::all();
        if ($members->isEmpty() || $classes->isEmpty()) return;

        $registrations = [
            ['user_id' => $members->get(0)?->id, 'class_id' => $classes->get(0)?->id, 'registration_date' => '2026-01-15', 'status' => 'registered'],
            ['user_id' => $members->get(0)?->id, 'class_id' => $classes->get(4)?->id ?? $classes->first()->id, 'registration_date' => '2026-02-01', 'status' => 'completed'],
            ['user_id' => $members->get(1)?->id, 'class_id' => $classes->get(1)?->id ?? $classes->first()->id, 'registration_date' => '2026-01-20', 'status' => 'registered'],
            ['user_id' => $members->get(2)?->id, 'class_id' => $classes->get(2)?->id ?? $classes->first()->id, 'registration_date' => '2026-02-05', 'status' => 'registered'],
            ['user_id' => $members->get(3)?->id, 'class_id' => $classes->get(0)?->id, 'registration_date' => '2026-01-15', 'status' => 'cancelled'],
        ];

        foreach ($registrations as $reg) {
            if ($reg['user_id'] && $reg['class_id']) {
                ClassRegistration::firstOrCreate(
                    ['user_id' => $reg['user_id'], 'class_id' => $reg['class_id']],
                    $reg
                );
            }
        }
    }
}
