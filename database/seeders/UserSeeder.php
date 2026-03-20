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
            // Members
            [
                'name'        => 'Hội Viên 1',
                'full_name'   => 'Nguyễn Văn An',
                'email'       => 'member1@gmail.com',
                'password'    => Hash::make('password'),
                'role_id'     => 5,
                'branch_id'   => 1,
                'phone'       => '0905000001',
                'gender'      => 'male',
                'card_number' => 'MBR001',
                'state'       => 'active',
            ],
            [
                'name'        => 'Hội Viên 2',
                'full_name'   => 'Trần Thị Bình',
                'email'       => 'member2@gmail.com',
                'password'    => Hash::make('password'),
                'role_id'     => 5,
                'branch_id'   => 1,
                'phone'       => '0905000002',
                'gender'      => 'female',
                'card_number' => 'MBR002',
                'state'       => 'active',
            ],
            [
                'name'        => 'Hội Viên 3',
                'full_name'   => 'Lê Văn Cường',
                'email'       => 'member3@gmail.com',
                'password'    => Hash::make('password'),
                'role_id'     => 5,
                'branch_id'   => 2,
                'phone'       => '0905000003',
                'gender'      => 'male',
                'card_number' => 'MBR003',
                'state'       => 'active',
            ],
            [
                'name'        => 'Hội Viên 4',
                'full_name'   => 'Phạm Thị Dung',
                'email'       => 'member4@gmail.com',
                'password'    => Hash::make('password'),
                'role_id'     => 5,
                'branch_id'   => 2,
                'phone'       => '0905000004',
                'gender'      => 'female',
                'card_number' => 'MBR004',
                'state'       => 'active',
            ],
            [
                'name'        => 'Hội Viên 5',
                'full_name'   => 'Hoàng Văn Em',
                'email'       => 'member5@gmail.com',
                'password'    => Hash::make('password'),
                'role_id'     => 5,
                'branch_id'   => 3,
                'phone'       => '0905000005',
                'gender'      => 'male',
                'card_number' => 'MBR005',
                'state'       => 'active',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);
        }
    }
}
