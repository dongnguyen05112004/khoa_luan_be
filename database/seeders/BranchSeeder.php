<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'id'          => 1,
                'branch_name' => 'FitLife GYM - Quận 1',
                'address'     => '123 Lê Lợi, Phường Bến Thành, Quận 1, TP.HCM',
                'phone'       => '02838221100',
                'capacity'    => 200,
                'created_at'  => '2025-09-01 08:00:00',
                'updated_at'  => '2025-09-01 08:00:00',
            ],
            [
                'id'          => 2,
                'branch_name' => 'FitLife GYM - Bình Thạnh',
                'address'     => '56 Đinh Tiên Hoàng, Phường 3, Quận Bình Thạnh, TP.HCM',
                'phone'       => '02835123456',
                'capacity'    => 150,
                'created_at'  => '2025-09-15 08:00:00',
                'updated_at'  => '2025-09-15 08:00:00',
            ],
            [
                'id'          => 3,
                'branch_name' => 'FitLife GYM - Gò Vấp',
                'address'     => '88 Quang Trung, Phường 10, Quận Gò Vấp, TP.HCM',
                'phone'       => '02836987654',
                'capacity'    => 120,
                'created_at'  => '2025-10-01 08:00:00',
                'updated_at'  => '2025-10-01 08:00:00',
            ],
        ];

        foreach ($branches as $branch) {
            DB::table('branches')->updateOrInsert(['id' => $branch['id']], $branch);
        }
    }
}
