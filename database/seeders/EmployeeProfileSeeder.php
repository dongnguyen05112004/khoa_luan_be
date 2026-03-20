<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeProfile;
use App\Models\User;

class EmployeeProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Users với role admin(1), manager(2), staff(3) → nhân viên
        $staffUsers = User::whereIn('role_id', [1, 2, 3])->get();

        $data = [
            ['hire_date' => '2021-01-15', 'salary' => 25000000],
            ['hire_date' => '2022-03-01', 'salary' => 18000000],
            ['hire_date' => '2023-06-10', 'salary' => 12000000],
        ];

        foreach ($staffUsers as $i => $user) {
            EmployeeProfile::firstOrCreate(
                ['user_id' => $user->id],
                $data[$i] ?? ['hire_date' => '2023-01-01', 'salary' => 10000000]
            );
        }
    }
}
