<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * EquipmentSeeder
 * Thiết bị gym thực tế tại 3 chi nhánh
 * Cột: id, equipment_name, serial_number, location, type, brand,
 *       branch_id, purchase_date, status, created_at, updated_at
 */
class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $equipment = [
            // ========== CHI NHÁNH QUẬN 1 ==========
            // Cardio
            ['Máy chạy bộ Technogym Run 500',  'TG-RUN-001', 'Khu Cardio A',    'Cardio',    'Technogym',   1, '2024-01-15', 'active'],
            ['Máy chạy bộ Technogym Run 500',  'TG-RUN-002', 'Khu Cardio A',    'Cardio',    'Technogym',   1, '2024-01-15', 'active'],
            ['Máy chạy bộ Life Fitness T5',    'LF-T5-001',  'Khu Cardio A',    'Cardio',    'Life Fitness', 1, '2024-03-10', 'active'],
            ['Máy chạy bộ Life Fitness T5',    'LF-T5-002',  'Khu Cardio A',    'Cardio',    'Life Fitness', 1, '2024-03-10', 'maintenance'],
            ['Xe đạp tập Matrix U50',           'MX-U50-001', 'Khu Cardio B',    'Cardio',    'Matrix',      1, '2024-02-20', 'active'],
            ['Xe đạp tập Matrix U50',           'MX-U50-002', 'Khu Cardio B',    'Cardio',    'Matrix',      1, '2024-02-20', 'active'],
            ['Máy đạp leo (Elliptical) Precor', 'PC-ELL-001', 'Khu Cardio B',    'Cardio',    'Precor',      1, '2024-04-05', 'active'],
            ['Máy Rowing Concept2 Model D',    'C2-ROW-001', 'Khu Cardio B',    'Cardio',    'Concept2',    1, '2024-06-10', 'active'],
            // Strength
            ['Bộ tạ đơn Hex 5-50kg',           'HEX-DB-Q1',  'Khu Tạ Tự Do',   'Strength',  'No Brand',    1, '2024-01-20', 'active'],
            ['Ghế tập ngực Scott Bench',        'SCT-BN-001', 'Khu Tạ Tự Do',   'Strength',  'Impulse',     1, '2024-01-20', 'active'],
            ['Khung Power Rack Hammer',         'PWR-RCK-001','Khu Tạ Tự Do',   'Strength',  'Hammer',      1, '2024-02-01', 'active'],
            ['Máy kéo lưng (Lat Pulldown)',     'LAT-PD-001', 'Khu Máy Cable',  'Strength',  'Life Fitness', 1, '2024-02-01', 'active'],
            ['Máy đẩy ngực (Cable Crossover)',  'CBL-CO-001', 'Khu Máy Cable',  'Strength',  'Life Fitness', 1, '2024-02-01', 'active'],
            ['Máy leg press 45 độ',             'LGP-001',    'Khu Chân',       'Strength',  'Matrix',      1, '2024-03-15', 'active'],
            ['Máy squat Smith Machine',         'SMT-001',    'Khu Chân',       'Strength',  'Technogym',   1, '2024-03-15', 'active'],
            // Flexibility
            ['Thảm yoga PVC 6mm (20 tấm)',      'YGA-MAT-Q1', 'Phòng Yoga',     'Flexibility','Lifetop',    1, '2024-05-01', 'active'],
            ['Gương toàn thân 2x1.5m',          'MRR-Q1-001', 'Phòng Yoga',     'Other',     'Nội địa',     1, '2024-05-01', 'active'],

            // ========== CHI NHÁNH BÌNH THẠNH ==========
            ['Máy chạy bộ Technogym Run 500',  'TG-RUN-003', 'Khu Cardio',     'Cardio',    'Technogym',   2, '2024-09-20', 'active'],
            ['Máy chạy bộ Technogym Run 500',  'TG-RUN-004', 'Khu Cardio',     'Cardio',    'Technogym',   2, '2024-09-20', 'active'],
            ['Xe đạp tập Matrix U50',           'MX-U50-003', 'Khu Cardio',     'Cardio',    'Matrix',      2, '2024-09-20', 'active'],
            ['Máy đạp leo Precor EFX 635',      'PC-EFX-001', 'Khu Cardio',     'Cardio',    'Precor',      2, '2024-10-05', 'active'],
            ['Bộ tạ đơn Hex 5-40kg',            'HEX-DB-BT',  'Khu Tạ Tự Do',  'Strength',  'No Brand',    2, '2024-09-25', 'active'],
            ['Khung Power Rack Inspire',         'PWR-RCK-002','Khu Tạ Tự Do',  'Strength',  'Inspire',     2, '2024-09-25', 'active'],
            ['Máy kéo lưng Cable',              'LAT-PD-002', 'Khu Máy',       'Strength',  'Matrix',      2, '2024-10-01', 'active'],
            ['Máy leg press Matrix',             'LGP-002',    'Khu Chân',      'Strength',  'Matrix',      2, '2024-10-01', 'maintenance'],
            ['Thảm Yoga (15 tấm)',              'YGA-MAT-BT', 'Phòng Nhóm',    'Flexibility','Lifetop',    2, '2024-10-10', 'active'],
            ['Loa âm thanh Zumba JBL PRX815',  'JBL-PRX-001','Phòng Nhóm',    'Other',     'JBL',         2, '2024-10-10', 'active'],

            // ========== CHI NHÁNH GÒ VẤP ==========
            ['Máy chạy bộ Life Fitness T5',    'LF-T5-003',  'Khu Cardio',     'Cardio',    'Life Fitness', 3, '2025-10-05', 'active'],
            ['Máy chạy bộ Life Fitness T5',    'LF-T5-004',  'Khu Cardio',     'Cardio',    'Life Fitness', 3, '2025-10-05', 'active'],
            ['Xe đạp tập Keiser M3i',           'KS-M3I-001', 'Khu Cardio',     'Cardio',    'Keiser',      3, '2025-10-10', 'active'],
            ['Bộ tạ đơn 5-30kg',               'HEX-DB-GV',  'Khu Tạ Tự Do',  'Strength',  'No Brand',    3, '2025-10-05', 'active'],
            ['Rack tập đa năng Inspire FT2',    'INSP-FT2-001','Khu Tạ Tự Do', 'Strength',  'Inspire',     3, '2025-10-08', 'active'],
            ['Máy leg press compact',            'LGP-003',    'Khu Máy',       'Strength',  'Nội địa',     3, '2025-10-08', 'broken'],
            ['Thảm Yoga (10 tấm)',              'YGA-MAT-GV', 'Phòng Nhóm',    'Flexibility','Lifetop',    3, '2025-10-12', 'active'],
            ['Bóng pilates 65cm (10 quả)',      'PIL-BL-GV',  'Phòng Phục Hồi','Flexibility','No Brand',   3, '2025-10-12', 'active'],
        ];

        foreach ($equipment as $idx => [$name, $serial, $location, $type, $brand, $branchId, $purchaseDate, $status]) {
            DB::table('equipment')->updateOrInsert(
                ['serial_number' => $serial],
                [
                    'equipment_name' => $name,
                    'serial_number'  => $serial,
                    'location'       => $location,
                    'type'           => $type,
                    'brand'          => $brand,
                    'branch_id'      => $branchId,
                    'purchase_date'  => $purchaseDate,
                    'status'         => $status,
                    'created_at'     => $purchaseDate . ' 08:00:00',
                    'updated_at'     => now(),
                ]
            );
        }
    }
}
