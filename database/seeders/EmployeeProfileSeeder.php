<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * EmployeeProfileSeeder
 * Tạo profile cho tất cả nhân viên (admin, manager, staff, trainer)
 * Cột: id, user_id, hire_date, salary, position, department, created_at, updated_at
 */
class EmployeeProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy user_id theo email
        $get = fn($email) => DB::table('users')->where('email', $email)->value('id');

        $profiles = [
            // ADMIN
            [
                'user_id'    => $get('admin@fitlifegym.vn'),
                'hire_date'  => '2024-01-01',
                'salary'     => 25000000.00,
                'position'   => 'System Administrator',
                'department' => 'IT',
            ],
            // QUẢN LÝ
            [
                'user_id'    => $get('manager.q1@fitlifegym.vn'),
                'hire_date'  => '2024-03-15',
                'salary'     => 20000000.00,
                'position'   => 'Quản lý chi nhánh',
                'department' => 'Quản lý',
            ],
            [
                'user_id'    => $get('manager.bt@fitlifegym.vn'),
                'hire_date'  => '2024-09-01',
                'salary'     => 18000000.00,
                'position'   => 'Quản lý chi nhánh',
                'department' => 'Quản lý',
            ],
            [
                'user_id'    => $get('manager.gv@fitlifegym.vn'),
                'hire_date'  => '2025-10-01',
                'salary'     => 18000000.00,
                'position'   => 'Quản lý chi nhánh',
                'department' => 'Quản lý',
            ],
            // NHÂN VIÊN LỄ TÂN
            [
                'user_id'    => $get('staff.mai@fitlifegym.vn'),
                'hire_date'  => '2024-06-01',
                'salary'     => 9500000.00,
                'position'   => 'Lễ tân',
                'department' => 'Dịch vụ khách hàng',
            ],
            [
                'user_id'    => $get('staff.duc@fitlifegym.vn'),
                'hire_date'  => '2025-01-10',
                'salary'     => 9000000.00,
                'position'   => 'Lễ tân',
                'department' => 'Dịch vụ khách hàng',
            ],
            [
                'user_id'    => $get('staff.linh@fitlifegym.vn'),
                'hire_date'  => '2024-09-15',
                'salary'     => 9500000.00,
                'position'   => 'Lễ tân',
                'department' => 'Dịch vụ khách hàng',
            ],
            [
                'user_id'    => $get('staff.tien@fitlifegym.vn'),
                'hire_date'  => '2025-02-01',
                'salary'     => 9000000.00,
                'position'   => 'Lễ tân',
                'department' => 'Dịch vụ khách hàng',
            ],
            [
                'user_id'    => $get('staff.phuong@fitlifegym.vn'),
                'hire_date'  => '2025-10-10',
                'salary'     => 9000000.00,
                'position'   => 'Lễ tân',
                'department' => 'Dịch vụ khách hàng',
            ],
            // HUẤN LUYỆN VIÊN PT
            [
                'user_id'    => $get('pt.minh@fitlifegym.vn'),
                'hire_date'  => '2024-02-01',
                'salary'     => 15000000.00,
                'position'   => 'Huấn luyện viên cá nhân',
                'department' => 'Đào tạo & Huấn luyện',
            ],
            [
                'user_id'    => $get('pt.hoa@fitlifegym.vn'),
                'hire_date'  => '2024-05-15',
                'salary'     => 13000000.00,
                'position'   => 'Huấn luyện viên cá nhân',
                'department' => 'Đào tạo & Huấn luyện',
            ],
            [
                'user_id'    => $get('pt.tuan@fitlifegym.vn'),
                'hire_date'  => '2024-09-01',
                'salary'     => 14000000.00,
                'position'   => 'Huấn luyện viên cá nhân',
                'department' => 'Đào tạo & Huấn luyện',
            ],
            [
                'user_id'    => $get('pt.lan@fitlifegym.vn'),
                'hire_date'  => '2025-01-05',
                'salary'     => 13000000.00,
                'position'   => 'Huấn luyện viên cá nhân',
                'department' => 'Đào tạo & Huấn luyện',
            ],
            [
                'user_id'    => $get('pt.hung@fitlifegym.vn'),
                'hire_date'  => '2025-10-05',
                'salary'     => 12000000.00,
                'position'   => 'Huấn luyện viên cá nhân',
                'department' => 'Đào tạo & Huấn luyện',
            ],
            [
                'user_id'    => $get('pt.thao@fitlifegym.vn'),
                'hire_date'  => '2025-11-01',
                'salary'     => 12000000.00,
                'position'   => 'Huấn luyện viên cá nhân',
                'department' => 'Đào tạo & Huấn luyện',
            ],
        ];

        foreach ($profiles as $profile) {
            if (!$profile['user_id']) continue;
            DB::table('employee_profiles')->updateOrInsert(
                ['user_id' => $profile['user_id']],
                array_merge($profile, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
