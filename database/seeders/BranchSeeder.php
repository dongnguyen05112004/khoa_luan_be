<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'branch_name' => 'Chi nhánh Quận 1',
                'address'     => '123 Nguyễn Huệ, Quận 1, TP.HCM',
                'phone'       => '028-3823-1234',
                'capacity'    => 200,
            ],
            [
                'branch_name' => 'Chi nhánh Quận 3',
                'address'     => '45 Lê Văn Sỹ, Quận 3, TP.HCM',
                'phone'       => '028-3832-5678',
                'capacity'    => 150,
            ],
            [
                'branch_name' => 'Chi nhánh Bình Thạnh',
                'address'     => '78 Đinh Bộ Lĩnh, Bình Thạnh, TP.HCM',
                'phone'       => '028-3511-9012',
                'capacity'    => 180,
            ],
            [
                'branch_name' => 'Chi nhánh Gò Vấp',
                'address'     => '99 Quang Trung, Gò Vấp, TP.HCM',
                'phone'       => '028-3995-3456',
                'capacity'    => 120,
            ],
        ];

        foreach ($branches as $b) {
            Branch::firstOrCreate(['branch_name' => $b['branch_name']], $b);
        }
    }
}
