<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Promotions: Các chương trình khuyến mãi 5-7 tháng gần đây (10/2025 - 05/2026)
 * Cột: id, title, code, description, discount, start_date, end_date,
 *       usage_limit, current_usage, is_active, created_at, updated_at
 */
class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $promotions = [
            // Tháng 10/2025 - Khai trương cơ sở Gò Vấp
            [
                'id'            => 1,
                'title'         => 'Khai Trương Gò Vấp - Giảm 30%',
                'code'          => 'GOVAP2025',
                'description'   => 'Ưu đãi đặc biệt mừng khai trương cơ sở Gò Vấp. Giảm 30% tất cả gói tập.',
                'discount'      => 30.00,
                'start_date'    => '2025-10-01',
                'end_date'      => '2025-10-31',
                'usage_limit'   => 100,
                'current_usage' => 87,
                'is_active'     => false,
                'created_at'    => '2025-09-28 09:00:00',
                'updated_at'    => '2025-11-01 09:00:00',
            ],
            // Tháng 11/2025 - Black Friday
            [
                'id'            => 2,
                'title'         => 'Black Friday - Giảm 25%',
                'code'          => 'BLACKFRI25',
                'description'   => 'Sale Black Friday - Giảm 25% tất cả gói tập 3 tháng và 6 tháng.',
                'discount'      => 25.00,
                'start_date'    => '2025-11-25',
                'end_date'      => '2025-11-30',
                'usage_limit'   => 150,
                'current_usage' => 143,
                'is_active'     => false,
                'created_at'    => '2025-11-20 08:00:00',
                'updated_at'    => '2025-12-01 08:00:00',
            ],
            // Tháng 12/2025 - Giáng Sinh
            [
                'id'            => 3,
                'title'         => 'Giáng Sinh & Năm Mới - Tặng 1 tháng',
                'code'          => 'XMAS2025',
                'description'   => 'Đăng ký gói 6 tháng tặng thêm 1 tháng miễn phí dịp Giáng Sinh.',
                'discount'      => 15.00,
                'start_date'    => '2025-12-20',
                'end_date'      => '2025-12-31',
                'usage_limit'   => 80,
                'current_usage' => 72,
                'is_active'     => false,
                'created_at'    => '2025-12-15 08:00:00',
                'updated_at'    => '2026-01-02 08:00:00',
            ],
            // Tháng 1/2026 - Tết Dương Lịch / Đầu năm mới
            [
                'id'            => 4,
                'title'         => 'Năm Mới 2026 - Giảm 20%',
                'code'          => 'HAPPY2026',
                'description'   => 'Chào đón năm mới 2026, giảm 20% tất cả gói tập.',
                'discount'      => 20.00,
                'start_date'    => '2026-01-01',
                'end_date'      => '2026-01-15',
                'usage_limit'   => 200,
                'current_usage' => 178,
                'is_active'     => false,
                'created_at'    => '2025-12-28 10:00:00',
                'updated_at'    => '2026-01-16 10:00:00',
            ],
            // Tháng 2/2026 - Tết Nguyên Đán
            [
                'id'            => 5,
                'title'         => 'Tết Bính Ngọ - Giảm 15% Gói 3 Tháng',
                'code'          => 'TET2026',
                'description'   => 'Mừng Xuân mới - Giảm 15% cho gói tập 3 tháng. Áp dụng từ 27/01 đến 15/02.',
                'discount'      => 15.00,
                'start_date'    => '2026-01-27',
                'end_date'      => '2026-02-15',
                'usage_limit'   => 120,
                'current_usage' => 105,
                'is_active'     => false,
                'created_at'    => '2026-01-20 09:00:00',
                'updated_at'    => '2026-02-16 09:00:00',
            ],
            // Tháng 3/2026 - Ngày 8/3
            [
                'id'            => 6,
                'title'         => 'Ngày Phụ Nữ 8/3 - Giảm 20%',
                'code'          => 'WOMEN0803',
                'description'   => 'Ưu đãi dành riêng cho chị em nhân ngày 8/3, giảm 20% toàn bộ gói.',
                'discount'      => 20.00,
                'start_date'    => '2026-03-01',
                'end_date'      => '2026-03-15',
                'usage_limit'   => 100,
                'current_usage' => 88,
                'is_active'     => false,
                'created_at'    => '2026-02-25 09:00:00',
                'updated_at'    => '2026-03-16 09:00:00',
            ],
            // Tháng 4/2026 - Giới thiệu hội viên mới
            [
                'id'            => 7,
                'title'         => 'Giới Thiệu Bạn Bè - Giảm 10%',
                'code'          => 'REFER2026',
                'description'   => 'Hội viên giới thiệu bạn mới đăng ký, cả hai cùng được giảm 10%.',
                'discount'      => 10.00,
                'start_date'    => '2026-04-01',
                'end_date'      => '2026-04-30',
                'usage_limit'   => 200,
                'current_usage' => 134,
                'is_active'     => false,
                'created_at'    => '2026-03-28 09:00:00',
                'updated_at'    => '2026-05-01 09:00:00',
            ],
            // Tháng 5/2026 - Đang diễn ra
            [
                'id'            => 8,
                'title'         => 'Tháng 5 - Hè Khỏe Đẹp Giảm 20%',
                'code'          => 'SUMMER2026',
                'description'   => 'Chào đón mùa hè! Giảm 20% tất cả gói tập trong tháng 5.',
                'discount'      => 20.00,
                'start_date'    => '2026-05-01',
                'end_date'      => '2026-05-31',
                'usage_limit'   => 300,
                'current_usage' => 47,
                'is_active'     => true,
                'created_at'    => '2026-04-28 09:00:00',
                'updated_at'    => '2026-05-13 09:00:00',
            ],
            [
                'id'            => 9,
                'title'         => 'Tháng 6 - Bùng nổ cơ bắp Giảm 10%',
                'code'          => 'MUSCLE',
                'description'   => 'Chào đón mùa hè! Giảm 10% tất cả gói tập trong tháng 6.',
                'discount'      => 10.00,
                'start_date'    => '2026-06-01',
                'end_date'      => '2026-06-30',
                'usage_limit'   => 100,
                'current_usage' => 0,
                'is_active'     => true,
                'created_at'    => '2026-05-10 09:00:00',
                'updated_at'    => '2026-05-29 17:30:00',   
            ],
        ];

        foreach ($promotions as $promo) {
            DB::table('promotions')->updateOrInsert(['id' => $promo['id']], $promo);
        }
    }
}
