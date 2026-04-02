<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessReport;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use App\Models\User;

class BusinessReportSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $month = \Carbon\Carbon::now()->subMonths($i);
            
            BusinessReport::create([
                'id_report'         => 'RPT-' . $month->format('Y-m'),
                'date_from_summary' => $month->copy()->endOfMonth()->toDateString(),
                'date_from'         => $month->copy()->startOfMonth()->toDateString(),
                'amount'            => rand(30, 100) * 1000000,
                'ai_diagnosis'      => 'Báo cáo doanh thu tháng ' . $month->format('m/Y') . '. Nhìn chung tương đối biến động.',
                'created_at'        => $month->copy()->endOfMonth(),
                'updated_at'        => $month->copy()->endOfMonth(),
            ]);
        }
    }
}
