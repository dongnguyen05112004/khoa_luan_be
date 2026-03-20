<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OtherExpense;
use App\Models\User;

class OtherExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $staffId = User::where('role_id', 3)->first()?->id;

        $expenses = [
            ['branch_id' => 1, 'created_by' => $staffId, 'expense_type' => 'Điện', 'description' => 'Tiền điện tháng 2/2026',  'amount' => 8500000,  'expense_date' => '2026-03-05'],
            ['branch_id' => 1, 'created_by' => $staffId, 'expense_type' => 'Nước', 'description' => 'Tiền nước tháng 2/2026',  'amount' => 1200000,  'expense_date' => '2026-03-05'],
            ['branch_id' => 1, 'created_by' => $staffId, 'expense_type' => 'Vệ sinh', 'description' => 'Chi phí vệ sinh tháng 2', 'amount' => 3000000, 'expense_date' => '2026-03-01'],
            ['branch_id' => 2, 'created_by' => $staffId, 'expense_type' => 'Điện', 'description' => 'Tiền điện tháng 2/2026',  'amount' => 6800000,  'expense_date' => '2026-03-05'],
            ['branch_id' => 2, 'created_by' => $staffId, 'expense_type' => 'Văn phòng phẩm', 'description' => 'Mua văn phòng phẩm', 'amount' => 500000, 'expense_date' => '2026-03-10'],
            ['branch_id' => 3, 'created_by' => $staffId, 'expense_type' => 'Điện', 'description' => 'Tiền điện tháng 2/2026',  'amount' => 7200000,  'expense_date' => '2026-03-05'],
        ];

        foreach ($expenses as $e) {
            OtherExpense::firstOrCreate(
                ['branch_id' => $e['branch_id'], 'expense_type' => $e['expense_type'], 'expense_date' => $e['expense_date']],
                $e
            );
        }
    }
}
