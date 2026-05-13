<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * EquipmentMaintenanceSeeder (được gọi là EquipmentSeeder phần 2 - file riêng)
 * Thực ra class này là thêm vào bảng equipment_maintenance
 *
 * Cột: id, equipment_id, technician_id, maintenance_date, description, cost,
 *       is_periodic, interval_days, next_maintenance_date, created_at, updated_at
 *
 * Giải thích: file EquipmentSeeder.php chứa class EquipmentSeeder (thiết bị),
 * Bảo trì được đặt trong file này. Tên class phải khớp tên file trong DatabaseSeeder.
 * Do đó PtPhiTinSeeder.php sẽ được dùng cho EquipmentMaintenance (thay thế file cũ).
 */
class PtPhiTinSeeder extends Seeder
{
    public function run(): void
    {
        $mgr1Id  = DB::table('users')->where('email', 'manager.q1@fitlifegym.vn')->value('id');
        $mgr2Id  = DB::table('users')->where('email', 'manager.bt@fitlifegym.vn')->value('id');
        $mgr3Id  = DB::table('users')->where('email', 'manager.gv@fitlifegym.vn')->value('id');
        $adminId = DB::table('users')->where('email', 'admin@fitlifegym.vn')->value('id');

        // Helper: lấy equipment_id theo serial_number
        $getEqId = fn($serial) => DB::table('equipment')->where('serial_number', $serial)->value('id');

        $records = [
            // ===== QUẬN 1 =====
            // Định kỳ 90 ngày - Máy chạy bộ
            [
                'equipment_id'        => $getEqId('TG-RUN-001'),
                'technician_id'       => $mgr1Id,
                'maintenance_date'    => '2025-10-20',
                'description'         => 'Bảo dưỡng định kỳ máy chạy bộ Technogym: tra dầu băng chuyền, kiểm tra bo mạch, vệ sinh quạt tản nhiệt.',
                'cost'                => 800000,
                'is_periodic'         => true,
                'interval_days'       => 90,
                'next_maintenance_date'=> '2026-01-18',
            ],
            [
                'equipment_id'        => $getEqId('TG-RUN-002'),
                'technician_id'       => $mgr1Id,
                'maintenance_date'    => '2025-10-20',
                'description'         => 'Bảo dưỡng định kỳ: thay băng chuyền, tra dầu, test tốc độ.',
                'cost'                => 1200000,
                'is_periodic'         => true,
                'interval_days'       => 90,
                'next_maintenance_date'=> '2026-01-18',
            ],
            [
                'equipment_id'        => $getEqId('LF-T5-002'),
                'technician_id'       => $mgr1Id,
                'maintenance_date'    => '2025-11-05',
                'description'         => 'Máy chạy bộ Life Fitness T5 bị lỗi màn hình, gửi trả nhà sản xuất sửa chữa.',
                'cost'                => 2500000,
                'is_periodic'         => false,
                'interval_days'       => null,
                'next_maintenance_date'=> null,
            ],
            [
                'equipment_id'        => $getEqId('LF-T5-002'),
                'technician_id'       => $mgr1Id,
                'maintenance_date'    => '2026-01-18',
                'description'         => 'Bảo dưỡng định kỳ Q1/2026: tra dầu, thay pads, kiểm tra dây curren.',
                'cost'                => 800000,
                'is_periodic'         => true,
                'interval_days'       => 90,
                'next_maintenance_date'=> '2026-04-18',
            ],
            [
                'equipment_id'        => $getEqId('TG-RUN-001'),
                'technician_id'       => $mgr1Id,
                'maintenance_date'    => '2026-01-20',
                'description'         => 'Bảo dưỡng định kỳ Q1/2026 - Technogym Run 500 #1.',
                'cost'                => 800000,
                'is_periodic'         => true,
                'interval_days'       => 90,
                'next_maintenance_date'=> '2026-04-20',
            ],
            [
                'equipment_id'        => $getEqId('PWR-RCK-001'),
                'technician_id'       => $mgr1Id,
                'maintenance_date'    => '2025-12-10',
                'description'         => 'Kiểm tra và siết lại toàn bộ bu-lông Power Rack, bôi trơn thanh trượt.',
                'cost'                => 300000,
                'is_periodic'         => true,
                'interval_days'       => 180,
                'next_maintenance_date'=> '2026-06-10',
            ],
            [
                'equipment_id'        => $getEqId('MX-U50-001'),
                'technician_id'       => $mgr1Id,
                'maintenance_date'    => '2026-02-14',
                'description'         => 'Thay thế bàn đạp xe đạp bị mòn, vệ sinh tổng thể.',
                'cost'                => 650000,
                'is_periodic'         => true,
                'interval_days'       => 90,
                'next_maintenance_date'=> '2026-05-15',
            ],
            // Sự cố bất thường Q1
            [
                'equipment_id'        => $getEqId('CBL-CO-001'),
                'technician_id'       => $mgr1Id,
                'maintenance_date'    => '2026-03-08',
                'description'         => 'Dây cáp cable crossover bị đứt cần thay gấp. Đã mua thay thế.',
                'cost'                => 1200000,
                'is_periodic'         => false,
                'interval_days'       => null,
                'next_maintenance_date'=> null,
            ],

            // ===== BÌNH THẠNH =====
            [
                'equipment_id'        => $getEqId('TG-RUN-003'),
                'technician_id'       => $mgr2Id,
                'maintenance_date'    => '2025-12-18',
                'description'         => 'Bảo dưỡng định kỳ 90 ngày - Technogym Run #3: tra dầu, kiểm tra mô-tơ.',
                'cost'                => 800000,
                'is_periodic'         => true,
                'interval_days'       => 90,
                'next_maintenance_date'=> '2026-03-18',
            ],
            [
                'equipment_id'        => $getEqId('LGP-002'),
                'technician_id'       => $mgr2Id,
                'maintenance_date'    => '2026-02-20',
                'description'         => 'Máy leg press bị kẹt thanh trượt, tra dầu và thay lót đệm.',
                'cost'                => 850000,
                'is_periodic'         => false,
                'interval_days'       => null,
                'next_maintenance_date'=> null,
            ],
            [
                'equipment_id'        => $getEqId('TG-RUN-003'),
                'technician_id'       => $mgr2Id,
                'maintenance_date'    => '2026-03-20',
                'description'         => 'Bảo dưỡng định kỳ Q1/2026 - Technogym Run #3.',
                'cost'                => 800000,
                'is_periodic'         => true,
                'interval_days'       => 90,
                'next_maintenance_date'=> '2026-06-20',
            ],
            [
                'equipment_id'        => $getEqId('MX-U50-003'),
                'technician_id'       => $mgr2Id,
                'maintenance_date'    => '2026-04-05',
                'description'         => 'Vệ sinh và bảo dưỡng xe đạp Matrix định kỳ.',
                'cost'                => 400000,
                'is_periodic'         => true,
                'interval_days'       => 90,
                'next_maintenance_date'=> '2026-07-05',
            ],

            // ===== GÒ VẤP =====
            [
                'equipment_id'        => $getEqId('LF-T5-003'),
                'technician_id'       => $mgr3Id,
                'maintenance_date'    => '2026-01-10',
                'description'         => 'Bảo dưỡng lần đầu sau 3 tháng khai trương: tra dầu, kiểm tra an toàn điện.',
                'cost'                => 750000,
                'is_periodic'         => true,
                'interval_days'       => 90,
                'next_maintenance_date'=> '2026-04-10',
            ],
            [
                'equipment_id'        => $getEqId('LGP-003'),
                'technician_id'       => $mgr3Id,
                'maintenance_date'    => '2026-02-25',
                'description'         => 'Máy leg press compact bị hỏng khớp, đã liên hệ nhà cung cấp nhưng không có linh kiện thay thế.',
                'cost'                => 0,
                'is_periodic'         => false,
                'interval_days'       => null,
                'next_maintenance_date'=> null,
            ],
            [
                'equipment_id'        => $getEqId('LF-T5-004'),
                'technician_id'       => $mgr3Id,
                'maintenance_date'    => '2026-01-10',
                'description'         => 'Bảo dưỡng định kỳ lần đầu máy Life Fitness T5 #4.',
                'cost'                => 750000,
                'is_periodic'         => true,
                'interval_days'       => 90,
                'next_maintenance_date'=> '2026-04-10',
            ],
            [
                'equipment_id'        => $getEqId('LF-T5-003'),
                'technician_id'       => $mgr3Id,
                'maintenance_date'    => '2026-04-10',
                'description'         => 'Bảo dưỡng định kỳ Q2 - Tra dầu, kiểm tra băng tải, vệ sinh quạt.',
                'cost'                => 750000,
                'is_periodic'         => true,
                'interval_days'       => 90,
                'next_maintenance_date'=> '2026-07-10',
            ],
        ];

        foreach ($records as &$r) {
            $r['created_at'] = $r['maintenance_date'] . ' 09:00:00';
            $r['updated_at'] = now();
        }

        foreach (array_chunk($records, 50) as $chunk) {
            DB::table('equipment_maintenance')->insert($chunk);
        }
    }
}
