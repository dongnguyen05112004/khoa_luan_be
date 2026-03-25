<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promotion;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $promotions = [
            [
                'title'       => 'Khai Trương Tháng 4',
                'code'        => 'KHAI-TRUONG-T4',
                'description' => 'Giảm 20% tất cả gói tập nhân dịp khai trương chi nhánh mới',
                'discount'    => 20.00,
                'start_date'  => '2026-04-01',
                'end_date'    => '2026-04-30',
                'usage_limit' => 100,
            ],
            [
                'title'       => 'Combo Mùa Hè',
                'code'        => 'SUMMER2026',
                'description' => 'Đăng ký gói 3 tháng giảm 15%',
                'discount'    => 15.00,
                'start_date'  => '2026-06-01',
                'end_date'    => '2026-08-31',
                'usage_limit' => 50,
            ],
            [
                'title'       => 'Ưu Đãi Sinh Nhật',
                'code'        => 'BIRTHDAY10',
                'description' => 'Giảm 10% cho hội viên có sinh nhật trong tháng',
                'discount'    => 10.00,
                'start_date'  => '2026-01-01',
                'end_date'    => '2026-12-31',
                'usage_limit' => null,
            ],
            [
                'title'       => 'Flash Sale Cuối Tuần',
                'code'        => 'FLASH30',
                'description' => 'Giảm 30% gói 1 tháng vào cuối tuần',
                'discount'    => 30.00,
                'start_date'  => '2026-03-20',
                'end_date'    => '2026-03-22',
                'usage_limit' => 20,
            ],
        ];

        foreach ($promotions as $promo) {
            Promotion::firstOrCreate(['title' => $promo['title']], $promo);
        }
    }
}
