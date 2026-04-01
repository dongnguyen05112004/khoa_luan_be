<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // role_id: 1=admin, 2=manager, 3=staff, 4=trainer, 5=member
        // branch_id: 1=Q1, 2=Q3, 3=BinhThanh, 4=GoVap

        $users = [
            // Admin
            [
                'name'        => 'Admin System',
                'full_name'   => 'Nguyễn Quản Trị',
                'email'       => 'admin@gym.vn',
                'password'    => Hash::make('password'),
                'role_id'     => 1,
                'branch_id'   => 1,
                'phone'       => '0901111111',
                'gender'      => 'male',
                'card_number' => 'ADM001',
                'state'       => 'active',
            ],
            // Manager
            [
                'name'        => 'Manager Q1',
                'full_name'   => 'Trần Quản Lý',
                'email'       => 'manager@gym.vn',
                'password'    => Hash::make('password'),
                'role_id'     => 2,
                'branch_id'   => 1,
                'phone'       => '0902222222',
                'gender'      => 'female',
                'card_number' => 'MGR001',
                'state'       => 'active',
            ],
            // Staff
            [
                'name'        => 'Staff Nguyen',
                'full_name'   => 'Nguyễn Nhân Viên',
                'email'       => 'staff@gym.vn',
                'password'    => Hash::make('password'),
                'role_id'     => 3,
                'branch_id'   => 1,
                'phone'       => '0903333333',
                'gender'      => 'male',
                'card_number' => 'STF001',
                'state'       => 'active',
            ],
            // Trainer 1
            [
                'name'        => 'PT Minh',
                'full_name'   => 'Lê Văn Minh',
                'email'       => 'pt.minh@gym.vn',
                'password'    => Hash::make('password'),
                'role_id'     => 4,
                'branch_id'   => 1,
                'phone'       => '0904444441',
                'gender'      => 'male',
                'card_number' => 'PT001',
                'state'       => 'active',
            ],
            // Trainer 2
            [
                'name'        => 'PT Lan',
                'full_name'   => 'Phạm Thị Lan',
                'email'       => 'pt.lan@gym.vn',
                'password'    => Hash::make('password'),
                'role_id'     => 4,
                'branch_id'   => 2,
                'phone'       => '0904444442',
                'gender'      => 'female',
                'card_number' => 'PT002',
                'state'       => 'active',
            ],
            // Trainer 3
            [
                'name'        => 'PT Hùng',
                'full_name'   => 'Võ Văn Hùng',
                'email'       => 'pt.hung@gym.vn',
                'password'    => Hash::make('password'),
                'role_id'     => 4,
                'branch_id'   => 3,
                'phone'       => '0904444443',
                'gender'      => 'male',
                'card_number' => 'PT003',
                'state'       => 'active',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);
        }

        // Tạo 300 Hội viên cho Cơ sở 1 bằng Faker
        $faker = \Faker\Factory::create('vi_VN');
        for ($i = 1; $i <= 300; $i++) {
            $email = "member{$i}_b1@gmail.com";
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name'        => "Hội Viên {$i}",
                    'full_name'   => $faker->name,
                    'password'    => Hash::make('password'),
                    'role_id'     => 5,
                    'branch_id'   => 1,
                    'phone'       => "09" . $faker->numerify('########'),
                    'gender'      => $faker->randomElement(['male', 'female']),
                    'card_number' => 'MBR1' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'state'       => 'active',
                ]
            );
        }
    }
}
