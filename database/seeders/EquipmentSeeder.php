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
        $faker = \Faker\Factory::create('vi_VN');

        $equipmentList = [
            ['name' => 'Máy chạy bộ Treadmill',  'type' => 'Cardio',   'brand' => 'LifeFitness'],
            ['name' => 'Máy đạp xe Spin Bike',    'type' => 'Cardio',   'brand' => 'Matrix'],
            ['name' => 'Bộ tạ đĩa Olympic',       'type' => 'Strength', 'brand' => 'Hammer'],
            ['name' => 'Máy kéo cáp Cable Cross', 'type' => 'Strength', 'brand' => 'Technogym'],
            ['name' => 'Ghế tập bụng AB Bench',   'type' => 'Strength', 'brand' => 'Body-Solid'],
            ['name' => 'Squat Rack Pro',           'type' => 'Strength', 'brand' => 'Rogue'],
            ['name' => 'Máy ép ngực Pec Deck',    'type' => 'Strength', 'brand' => 'Cybex'],
            ['name' => 'Dumbbell Rack',            'type' => 'Strength', 'brand' => 'Bodycraft'],
            ['name' => 'Máy elip Elliptical',      'type' => 'Cardio',   'brand' => 'Precor'],
            ['name' => 'Máy rowing',               'type' => 'Cardio',   'brand' => 'Concept2'],
        ];

        $locations = ['Khu Cardio A', 'Khu Cardio B', 'Khu Strength A', 'Khu Strength B', 'Tầng 1', 'Tầng 2'];
        $statuses  = ['active', 'active', 'active', 'maintenance', 'broken'];

        for ($i = 1; $i <= 50; $i++) {
            $serial       = 'EQ-B1-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $item         = $equipmentList[($i - 1) % count($equipmentList)];
            $purchaseDate = \Carbon\Carbon::now()->subDays(rand(30, 500));
            $status       = $faker->randomElement($statuses);

            // Dùng serial_number làm unique key để tránh trùng lặp
            $equipment = Equipment::firstOrCreate(
                ['serial_number' => $serial],
                [
                    'equipment_name' => $item['name'] . ' ' . $i,
                    'type'           => $item['type'],
                    'brand'          => $item['brand'],
                    'location'       => $faker->randomElement($locations),
                    'branch_id'      => 1,
                    'purchase_date'  => $purchaseDate->toDateString(),
                    'status'         => $status,
                ]
            );

            // Tạo lịch bảo trì nếu thiết bị maintenance/broken
            if (in_array($equipment->status, ['maintenance', 'broken']) && $technicianId) {
                $maintenanceDate = (clone $purchaseDate)->addDays(rand(5, 30));
                if ($maintenanceDate->isFuture()) {
                    $maintenanceDate = \Carbon\Carbon::yesterday();
                }

                EquipmentMaintenance::firstOrCreate(
                    [
                        'equipment_id'     => $equipment->id,
                        'maintenance_date' => $maintenanceDate->toDateString(),
                    ],
                    [
                        'technician_id' => $technicianId,
                        'description'   => $faker->sentence(8),
                        'cost'          => rand(5, 50) * 100000,
                        'is_periodic'   => false,
                    ]
                );
            }
        }
    }
}
