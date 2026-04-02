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

        $equipmentNames = ['Máy chạy bộ Treadmill', 'Máy đạp xe Spin Bike', 'Bộ tạ đĩa Olympic', 'Máy kéo cáp Cable Cross', 'Ghế tập bụng AB Bench', 'Squat Rack Pro', 'Máy ép ngực Pec Deck', 'Dumbbell Rack'];

        for ($i = 1; $i <= 50; $i++) {
            $purchaseDate = \Carbon\Carbon::now()->subDays(rand(30, 180));
            $created = Equipment::firstOrCreate(
                [
                    'equipment_name' => $faker->randomElement($equipmentNames) . ' ' . $i,
                    'branch_id' => 1
                ],
                [
                    'serial_number' => 'EQ-B1-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'branch_id' => 1,
                    'purchase_date' => $purchaseDate->toDateString(),
                    'status' => $faker->randomElement(['good', 'good', 'good', 'maintenance', 'broken'])
                ]
            );

            if (in_array($created->status, ['maintenance', 'broken']) && $technicianId) {
                // Generate a random maintenance date after purchase date
                $maintenanceDate = (clone $purchaseDate)->addDays(rand(5, 30));
                if ($maintenanceDate->isFuture()) {
                    $maintenanceDate = \Carbon\Carbon::yesterday();
                }

                EquipmentMaintenance::firstOrCreate(
                    ['equipment_id' => $created->id, 'maintenance_date' => $maintenanceDate->toDateString()],
                    [
                        'equipment_id'    => $created->id,
                        'technician_id'   => $technicianId,
                        'maintenance_date'=> $maintenanceDate->toDateString(),
                        'description'     => $faker->sentence(8),
                        'cost'            => rand(5, 50) * 100000,
                    ]
                );
            }
        }
    }
}
