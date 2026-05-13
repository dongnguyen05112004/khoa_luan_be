<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * BusinessReportSeeder
 * Báo cáo kinh doanh hàng tháng (10/2025 - 04/2026)
 * Cột: id, id_report, report_type, date_from_summary, date_from, amount,
 *       raw_data_summary, ai_diagnosis, ai_forecast, ai_suggestions, created_by
 */
class BusinessReportSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')->where('email', 'admin@fitlifegym.vn')->value('id');

        // Dữ liệu doanh thu thực tế theo tháng (tổng 3 chi nhánh)
        $monthlyReports = [
            [
                'month'   => '2025-10',
                'revenue' => 85000000,
                'members' => 42,
                'newM'    => 18,
                'cancelM' => 2,
                'checkins'=> 380,
                'trend'   => 'Tháng khai trương Gò Vấp, doanh thu tăng đột biến từ chiến dịch GOVAP2025.',
                'forecast'=> 'Tháng 11 dự kiến doanh thu đạt 90-95tr, Black Friday sẽ thúc đẩy mạnh.',
                'suggest' => "1. Tiếp tục duy trì chiến dịch GOVAP2025\n2. Chuẩn bị sớm cho Black Friday\n3. Tuyển thêm 1 PT cho Gò Vấp",
            ],
            [
                'month'   => '2025-11',
                'revenue' => 112000000,
                'members' => 58,
                'newM'    => 22,
                'cancelM' => 4,
                'checkins'=> 520,
                'trend'   => 'Black Friday (BLACKFRI25) đạt 143/150 lượt dùng - thành công nhất trong năm. Doanh thu tăng 31.7% so với tháng trước.',
                'forecast'=> 'Tháng 12 có Giáng Sinh và Năm Mới - dự báo doanh thu 100-115tr.',
                'suggest' => "1. Tổng kết chiến dịch Black Friday để rút kinh nghiệm\n2. Chuẩn bị gói XMAS2025 ngay đầu tháng 12\n3. Tăng cường nhân sự lễ tân dịp cuối năm",
            ],
            [
                'month'   => '2025-12',
                'revenue' => 105000000,
                'members' => 67,
                'newM'    => 16,
                'cancelM' => 5,
                'checkins'=> 490,
                'trend'   => 'Tháng 12 doanh thu giảm nhẹ 6.25% so với tháng 11 do dịp Giáng Sinh nhiều người về quê. Tuy nhiên vẫn tăng 23.5% so với tháng 10.',
                'forecast'=> 'Tháng 1 sẽ có sụt giảm nhẹ do Tết nhưng chiến dịch HAPPY2026 sẽ bù đắp.',
                'suggest' => "1. Triển khai HAPPY2026 từ ngày 01/01\n2. Thông báo lịch nghỉ Tết rõ ràng cho hội viên\n3. Chuẩn bị gói TET2026 sớm",
            ],
            [
                'month'   => '2026-01',
                'revenue' => 98000000,
                'members' => 74,
                'newM'    => 20,
                'cancelM' => 7,
                'checkins'=> 450,
                'trend'   => 'Tháng 1 doanh thu giảm 6.7% do nghỉ Tết 10 ngày. Chiến dịch HAPPY2026 thu hút 178 lượt sử dụng - hiệu quả tốt.',
                'forecast'=> 'Tháng 2 có Tết Nguyên Đán - dự báo doanh thu 80-90tr. Sau Tết thường có làn sóng đăng ký mới.',
                'suggest' => "1. Gửi SMS/Zalo nhắc hội viên tập lại sau Tết\n2. Triển khai ưu đãi TET2026 ngay từ 27/01\n3. Tổ chức mini event \"Tập Gym Ngày Đầu Năm\"",
            ],
            [
                'month'   => '2026-02',
                'revenue' => 88000000,
                'members' => 76,
                'newM'    => 14,
                'cancelM' => 6,
                'checkins'=> 420,
                'trend'   => 'Tháng Tết (02/2026) doanh thu đúng như dự báo giảm 10.2%. Sau Tết thấy làn sóng hội viên quay lại, check-in bắt đầu hồi phục từ ngày 12/02.',
                'forecast'=> 'Tháng 3 (8/3) dự báo tăng mạnh nhờ chiến dịch WOMEN0803, đặc biệt hội viên nữ.',
                'suggest' => "1. Đẩy mạnh marketing cho ngày 8/3\n2. Tặng quà nhỏ (nước uống, đồ tập) cho hội viên nữ\n3. Tổ chức buổi workshop dinh dưỡng miễn phí",
            ],
            [
                'month'   => '2026-03',
                'revenue' => 118000000,
                'members' => 82,
                'newM'    => 18,
                'cancelM' => 4,
                'checkins'=> 560,
                'trend'   => 'Tháng 3 tăng trưởng mạnh nhất từ trước đến nay (+34.1% vs tháng 2). Chiến dịch 8/3 và hội viên nữ đăng ký tăng vọt. Gò Vấp đặc biệt tăng mạnh.',
                'forecast'=> 'Tháng 4 giữ momentum, chiến dịch REFER2026 kỳ vọng thu hút hội viên mới qua giới thiệu.',
                'suggest' => "1. Phân tích kỹ nhóm khách hàng nữ để cá nhân hóa\n2. Triển khai program REFER2026 với hoa hồng rõ ràng\n3. Đầu tư thêm thiết bị cho Gò Vấp",
            ],
            [
                'month'   => '2026-04',
                'revenue' => 110000000,
                'members' => 86,
                'newM'    => 15,
                'cancelM' => 5,
                'checkins'=> 530,
                'trend'   => 'Tháng 4 doanh thu giảm nhẹ 6.8% sau peak tháng 3. Chiến dịch REFER2026 thu hút 134 lượt - đang diễn ra tốt. Số hội viên mới tuy ít hơn nhưng chất lượng cao hơn (tỷ lệ ký PT contract 40%).',
                'forecast'=> 'Tháng 5 (SUMMER2026) kỳ vọng doanh thu 120-130tr. Xu hướng tập gym hè tăng mạnh.',
                'suggest' => "1. Triển khai SUMMER2026 từ 01/05 với push notification\n2. Tổ chức outdoor workout event để thu hút hội viên mới\n3. Chuẩn bị tăng cường nhân sự hè",
            ],
        ];

        foreach ($monthlyReports as $idx => $r) {
            $yearMonth  = $r['month'];
            $reportDate = date('Y-m-t', strtotime($yearMonth . '-01'));
            $idReport   = 'RPT-' . str_replace('-', '', $yearMonth);

            $rawData = json_encode([
                'total_revenue'  => $r['revenue'],
                'total_members'  => $r['members'],
                'new_members'    => $r['newM'],
                'cancelled'      => $r['cancelM'],
                'total_checkins' => $r['checkins'],
                'period'         => $yearMonth,
            ]);

            DB::table('business_reports')->updateOrInsert(
                ['id_report' => $idReport],
                [
                    'id_report'         => $idReport,
                    'report_type'       => 'Monthly',
                    'date_from_summary' => $reportDate,
                    'date_from'         => $yearMonth . '-01',
                    'amount'            => $r['revenue'],
                    'raw_data_summary'  => $rawData,
                    'ai_diagnosis'      => $r['trend'],
                    'ai_forecast'       => $r['forecast'],
                    'ai_suggestions'    => $r['suggest'],
                    'created_by'        => $adminId,
                    'created_at'        => $reportDate . ' 23:59:00',
                    'updated_at'        => now(),
                ]
            );
        }
    }
}
