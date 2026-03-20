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
        // Business Reports
        $reports = [
            [
                'id_report'         => 'RPT-2026-01',
                'date_from_summary' => '2026-01-31',
                'date_from'         => '2026-01-01',
                'amount'            => 45000000,
                'ai_diagnosis'      => 'Doanh thu tháng 1 tăng 12% so với cùng kỳ năm trước. Gói 3 tháng bán chạy nhất.',
            ],
            [
                'id_report'         => 'RPT-2026-02',
                'date_from_summary' => '2026-02-28',
                'date_from'         => '2026-02-01',
                'amount'            => 52000000,
                'ai_diagnosis'      => 'Doanh thu tháng 2 tăng mạnh 18% nhờ chương trình Valentine. Hội viên mới tăng 25 người.',
            ],
            [
                'id_report'         => 'RPT-2026-03',
                'date_from_summary' => '2026-03-18',
                'date_from'         => '2026-03-01',
                'amount'            => 28000000,
                'ai_diagnosis'      => 'Doanh thu đầu tháng 3 ổn định, dự báo cả tháng đạt 55 triệu dựa trên xu hướng.',
            ],
        ];

        foreach ($reports as $r) {
            BusinessReport::firstOrCreate(['id_report' => $r['id_report']], $r);
        }
    }
}
