<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * EquipmentMaintenanceSeeder (file: OtherExpenseSeeder.php - giữ nguyên tên class để tránh thay đổi)
 * Thực ra đây là OtherExpenseSeeder
 *
 * OtherExpenseSeeder - Chi phí vận hành hàng tháng
 * Cột: id, branch_id, created_by, expense_type, description, amount, expense_date
 *
 * Các loại chi phí: electricity, water, rent, marketing, cleaning, internet, insurance, misc
 */
class OtherExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $adminId   = DB::table('users')->where('email', 'admin@fitlifegym.vn')->value('id');
        $mgr1Id    = DB::table('users')->where('email', 'manager.q1@fitlifegym.vn')->value('id');
        $mgr2Id    = DB::table('users')->where('email', 'manager.bt@fitlifegym.vn')->value('id');
        $mgr3Id    = DB::table('users')->where('email', 'manager.gv@fitlifegym.vn')->value('id');

        // Tháng 10/2025 đến 05/2026 - chi phí thực tế hàng tháng
        $months = [
            '2025-10', '2025-11', '2025-12',
            '2026-01', '2026-02', '2026-03', '2026-04', '2026-05',
        ];

        $expenses = [];

        foreach ($months as $month) {
            $lastDay = date('Y-m-t', strtotime($month . '-01'));

            // ===== CHI NHÁNH QUẬN 1 =====
            $expenses[] = ['branch_id' => 1, 'created_by' => $mgr1Id, 'expense_type' => 'electricity', 'description' => "Tiền điện tháng {$month} - Q1", 'amount' => rand(8500000, 11000000), 'expense_date' => $lastDay];
            $expenses[] = ['branch_id' => 1, 'created_by' => $mgr1Id, 'expense_type' => 'water',       'description' => "Tiền nước tháng {$month} - Q1", 'amount' => rand(1200000, 1800000), 'expense_date' => $lastDay];
            $expenses[] = ['branch_id' => 1, 'created_by' => $mgr1Id, 'expense_type' => 'rent',        'description' => "Tiền thuê mặt bằng tháng {$month} - Q1", 'amount' => 45000000, 'expense_date' => substr($month, 0, 7) . '-05'];
            $expenses[] = ['branch_id' => 1, 'created_by' => $mgr1Id, 'expense_type' => 'cleaning',    'description' => "Chi phí dọn dẹp vệ sinh tháng {$month} - Q1", 'amount' => rand(2500000, 3500000), 'expense_date' => $lastDay];
            $expenses[] = ['branch_id' => 1, 'created_by' => $mgr1Id, 'expense_type' => 'internet',    'description' => "Internet + điện thoại tháng {$month} - Q1", 'amount' => rand(800000, 1200000), 'expense_date' => substr($month, 0, 7) . '-10'];
            if (in_array($month, ['2025-10', '2025-12', '2026-03'])) {
                $expenses[] = ['branch_id' => 1, 'created_by' => $mgr1Id, 'expense_type' => 'marketing', 'description' => "Chi phí quảng cáo Facebook/Google - Q1", 'amount' => rand(5000000, 8000000), 'expense_date' => substr($month, 0, 7) . '-15'];
            }
            $expenses[] = ['branch_id' => 1, 'created_by' => $mgr1Id, 'expense_type' => 'insurance',   'description' => "Bảo hiểm thiết bị tháng {$month} - Q1", 'amount' => 1500000, 'expense_date' => substr($month, 0, 7) . '-01'];

            // ===== CHI NHÁNH BÌNH THẠNH =====
            $expenses[] = ['branch_id' => 2, 'created_by' => $mgr2Id, 'expense_type' => 'electricity', 'description' => "Tiền điện tháng {$month} - Bình Thạnh", 'amount' => rand(6500000, 8500000), 'expense_date' => $lastDay];
            $expenses[] = ['branch_id' => 2, 'created_by' => $mgr2Id, 'expense_type' => 'water',       'description' => "Tiền nước tháng {$month} - Bình Thạnh", 'amount' => rand(900000, 1400000), 'expense_date' => $lastDay];
            $expenses[] = ['branch_id' => 2, 'created_by' => $mgr2Id, 'expense_type' => 'rent',        'description' => "Tiền thuê mặt bằng tháng {$month} - Bình Thạnh", 'amount' => 32000000, 'expense_date' => substr($month, 0, 7) . '-05'];
            $expenses[] = ['branch_id' => 2, 'created_by' => $mgr2Id, 'expense_type' => 'cleaning',    'description' => "Vệ sinh thiết bị và phòng tập tháng {$month} - BT", 'amount' => rand(1800000, 2500000), 'expense_date' => $lastDay];
            $expenses[] = ['branch_id' => 2, 'created_by' => $mgr2Id, 'expense_type' => 'internet',    'description' => "Internet tháng {$month} - Bình Thạnh", 'amount' => rand(500000, 900000), 'expense_date' => substr($month, 0, 7) . '-10'];
            if (in_array($month, ['2025-11', '2026-01', '2026-04'])) {
                $expenses[] = ['branch_id' => 2, 'created_by' => $mgr2Id, 'expense_type' => 'marketing', 'description' => "Tờ rơi + quảng cáo địa phương - Bình Thạnh", 'amount' => rand(3000000, 5000000), 'expense_date' => substr($month, 0, 7) . '-15'];
            }

            // ===== CHI NHÁNH GÒ VẤP (mới khai trương 10/2025) =====
            if ($month >= '2025-10') {
                $isNewBranch = $month === '2025-10';
                $expenses[] = ['branch_id' => 3, 'created_by' => $mgr3Id, 'expense_type' => 'electricity', 'description' => "Tiền điện tháng {$month} - Gò Vấp", 'amount' => rand(4500000, 6500000), 'expense_date' => $lastDay];
                $expenses[] = ['branch_id' => 3, 'created_by' => $mgr3Id, 'expense_type' => 'water',       'description' => "Tiền nước tháng {$month} - Gò Vấp", 'amount' => rand(700000, 1100000), 'expense_date' => $lastDay];
                $expenses[] = ['branch_id' => 3, 'created_by' => $mgr3Id, 'expense_type' => 'rent',        'description' => "Tiền thuê mặt bằng tháng {$month} - Gò Vấp", 'amount' => 25000000, 'expense_date' => substr($month, 0, 7) . '-05'];
                $expenses[] = ['branch_id' => 3, 'created_by' => $mgr3Id, 'expense_type' => 'cleaning',    'description' => "Vệ sinh tháng {$month} - Gò Vấp", 'amount' => rand(1500000, 2200000), 'expense_date' => $lastDay];
                if ($isNewBranch) {
                    $expenses[] = ['branch_id' => 3, 'created_by' => $adminId, 'expense_type' => 'marketing', 'description' => 'Chi phí khai trương + băng rôn + truyền thông - Gò Vấp', 'amount' => 15000000, 'expense_date' => '2025-10-01'];
                    $expenses[] = ['branch_id' => 3, 'created_by' => $adminId, 'expense_type' => 'misc',       'description' => 'Mua sắm dụng cụ vệ sinh, cây xanh trang trí', 'amount' => 3500000, 'expense_date' => '2025-09-28'];
                }
            }

            // Chi phí chung toàn hệ thống (do admin quản lý)
            if (in_array($month, ['2025-10', '2026-01', '2026-04'])) {
                $expenses[] = ['branch_id' => 1, 'created_by' => $adminId, 'expense_type' => 'misc', 'description' => "Chi phí phần mềm quản lý gym (server, license) - {$month}", 'amount' => 2000000, 'expense_date' => substr($month, 0, 7) . '-01'];
            }
        }

        // Thêm chi phí lẻ bất thường
        $adhoc = [
            ['branch_id' => 1, 'created_by' => $mgr1Id, 'expense_type' => 'misc', 'description' => 'Sửa chữa hệ thống điều hòa phòng cardio', 'amount' => 4200000, 'expense_date' => '2025-11-12'],
            ['branch_id' => 2, 'created_by' => $mgr2Id, 'expense_type' => 'misc', 'description' => 'Thay mới gương phòng tập (vỡ do va đập)', 'amount' => 2800000, 'expense_date' => '2025-12-08'],
            ['branch_id' => 1, 'created_by' => $mgr1Id, 'expense_type' => 'misc', 'description' => 'Mua bổ sung khóa tủ cá nhân cho hội viên', 'amount' => 1500000, 'expense_date' => '2026-01-18'],
            ['branch_id' => 3, 'created_by' => $mgr3Id, 'expense_type' => 'misc', 'description' => 'Sơn lại khu vực tập (bong tróc sau mưa)', 'amount' => 3500000, 'expense_date' => '2026-02-14'],
            ['branch_id' => 2, 'created_by' => $mgr2Id, 'expense_type' => 'misc', 'description' => 'Chi phí tổ chức sự kiện 8/3 tại cơ sở', 'amount' => 5000000, 'expense_date' => '2026-03-08'],
            ['branch_id' => 1, 'created_by' => $mgr1Id, 'expense_type' => 'marketing', 'description' => 'Hợp đồng influencer review gym Q1', 'amount' => 8000000, 'expense_date' => '2026-04-05'],
        ];
        $expenses = array_merge($expenses, $adhoc);

        foreach ($expenses as &$e) {
            $e['created_at'] = $e['expense_date'] . ' 10:00:00';
            $e['updated_at'] = now();
        }

        foreach (array_chunk($expenses, 100) as $chunk) {
            DB::table('other_expenses')->insert($chunk);
        }
    }
}
