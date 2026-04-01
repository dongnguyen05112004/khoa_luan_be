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
        $members = User::where('role_id', 5)->where('branch_id', 1)->get();
        $classes = GymClass::where('branch_id', 1)->get();
        if ($members->isEmpty() || $classes->isEmpty()) return;

        foreach ($classes as $class) {
            // Register 10-40 members per class
            $registeredMembers = $members->random(rand(10, min(40, $members->count())));
            foreach ($registeredMembers as $member) {
                // Determine registration date 1-3 days before class
                $registrationDate = \Carbon\Carbon::parse($class->schedule_date)->subDays(rand(1, 3));
                
                ClassRegistration::create([
                    'user_id' => $member->id,
                    'class_id' => $class->id,
                    'registration_date' => $registrationDate->toDateString(),
                    'status' => rand(1, 10) > 8 ? 'cancelled' : (\Carbon\Carbon::parse($class->schedule_date)->isPast() ? 'completed' : 'registered'),
                ]);
            }
        }
    }
}
