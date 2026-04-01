<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OtherExpense;
use App\Models\User;

class OtherExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $staffId = User::where('role_id', 3)->where('branch_id', 1)->first()?->id ?? 1;

        $expenseTypes = ['Điện', 'Nước', 'Vệ sinh', 'Internet', 'Văn phòng phẩm', 'Sửa chữa lặt vặt'];
        $faker = \Faker\Factory::create('vi_VN');

        for ($i = 0; $i < 150; $i++) {
            $date = \Carbon\Carbon::now()->subDays(rand(1, 90));
            $type = $faker->randomElement($expenseTypes);
            
            OtherExpense::create([
                'branch_id' => 1,
                'created_by' => $staffId,
                'expense_type' => $type,
                'description' => "Chi phí $type tháng " . $date->month,
                'amount' => rand(5, 50) * 100000,
                'expense_date' => $date->toDateString()
            ]);
        }
    }
}
