<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\EquipmentMaintenance;
use App\Models\User;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $technicianId = User::where('role_id', 3)->first()?->id;

        $equipment = [
            ['equipment_name' => 'Máy chạy bộ Treadmill A1', 'serial_number' => 'TRD-A1-001', 'branch_id' => 1, 'purchase_date' => '2023-01-15', 'status' => 'good'],
            ['equipment_name' => 'Máy chạy bộ Treadmill A2', 'serial_number' => 'TRD-A2-002', 'branch_id' => 1, 'purchase_date' => '2023-01-15', 'status' => 'good'],
            ['equipment_name' => 'Máy đạp xe Spin Bike B1',  'serial_number' => 'SPB-B1-003', 'branch_id' => 1, 'purchase_date' => '2023-03-20', 'status' => 'maintenance'],
            ['equipment_name' => 'Bộ tạ đĩa Olympic Set',    'serial_number' => 'WTS-OL-004', 'branch_id' => 1, 'purchase_date' => '2022-06-10', 'status' => 'good'],
            ['equipment_name' => 'Máy kéo cáp Cable Cross',  'serial_number' => 'CBL-CR-005', 'branch_id' => 2, 'purchase_date' => '2023-05-01', 'status' => 'good'],
            ['equipment_name' => 'Ghế tập bụng AB Bench',    'serial_number' => 'ABB-AB-006', 'branch_id' => 2, 'purchase_date' => '2022-09-15', 'status' => 'broken'],
            ['equipment_name' => 'Máy chạy bộ Treadmill B1', 'serial_number' => 'TRD-B1-007', 'branch_id' => 3, 'purchase_date' => '2024-01-10', 'status' => 'good'],
            ['equipment_name' => 'Squat Rack Pro',            'serial_number' => 'SQR-PR-008', 'branch_id' => 3, 'purchase_date' => '2023-07-20', 'status' => 'good'],
        ];

        foreach ($equipment as $eq) {
            $created = Equipment::firstOrCreate(
                ['equipment_name' => $eq['equipment_name'], 'branch_id' => $eq['branch_id']],
                $eq
            );

            // Tạo bảo trì nếu status là maintenance hoặc broken
            if (in_array($eq['status'], ['maintenance', 'broken']) && $technicianId) {
                EquipmentMaintenance::firstOrCreate(
                    ['equipment_id' => $created->id, 'maintenance_date' => '2026-03-10'],
                    [
                        'equipment_id'    => $created->id,
                        'technician_id'   => $technicianId,
                        'maintenance_date'=> '2026-03-10',
                        'description'     => 'Kiểm tra và sửa chữa định kỳ',
                        'cost'            => 500000,
                    ]
                );
            }
        }
    }
}
