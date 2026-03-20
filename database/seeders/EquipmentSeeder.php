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
            ['equipment_name' => 'Máy chạy bộ Treadmill A1', 'branch_id' => 1, 'purchase_date' => '2023-01-15', 'status' => 'good'],
            ['equipment_name' => 'Máy chạy bộ Treadmill A2', 'branch_id' => 1, 'purchase_date' => '2023-01-15', 'status' => 'good'],
            ['equipment_name' => 'Máy đạp xe Spin Bike B1',  'branch_id' => 1, 'purchase_date' => '2023-03-20', 'status' => 'maintenance'],
            ['equipment_name' => 'Bộ tạ đĩa Olympic Set',    'branch_id' => 1, 'purchase_date' => '2022-06-10', 'status' => 'good'],
            ['equipment_name' => 'Máy kéo cáp Cable Cross',  'branch_id' => 2, 'purchase_date' => '2023-05-01', 'status' => 'good'],
            ['equipment_name' => 'Ghế tập bụng AB Bench',    'branch_id' => 2, 'purchase_date' => '2022-09-15', 'status' => 'broken'],
            ['equipment_name' => 'Máy chạy bộ Treadmill B1', 'branch_id' => 3, 'purchase_date' => '2024-01-10', 'status' => 'good'],
            ['equipment_name' => 'Squat Rack Pro',            'branch_id' => 3, 'purchase_date' => '2023-07-20', 'status' => 'good'],
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
