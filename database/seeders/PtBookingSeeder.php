<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PtBooking;
use App\Models\PtContract;
use App\Models\Trainer;

class PtBookingSeeder extends Seeder
{
    public function run(): void
    {
        $contracts = PtContract::with('trainer')->get();
        if ($contracts->isEmpty()) return;

        $faker = \Faker\Factory::create('vi_VN');
        
        foreach ($contracts as $contract) {
            // Generate completed bookings for all used_sessions
            for ($i = 0; $i < $contract->used_sessions; $i++) {
                $scheduleTime = \Carbon\Carbon::parse($contract->start_date)->addDays($i * rand(2, 4))->setTime(rand(7, 18), 0, 0);
                if ($scheduleTime->isFuture()) $scheduleTime = \Carbon\Carbon::now()->subDays(1); // Ensure past
                
                PtBooking::create([
                    'contract_id'   => $contract->id,
                    'trainer_id'    => $contract->trainer_id,
                    'schedule_time' => $scheduleTime,
                    'status'        => 'done',
                    'notes'         => $faker->sentence(5),
                ]);
            }
            
            // Create 1 future booking if still active
            if ($contract->status === 'active' && $contract->used_sessions < $contract->total_sessions) {
                 PtBooking::create([
                    'contract_id'   => $contract->id,
                    'trainer_id'    => $contract->trainer_id,
                    'schedule_time' => \Carbon\Carbon::now()->addDays(2)->setTime(rand(7, 18), 0, 0),
                    'status'        => 'confirmed',
                    'notes'         => 'Đã lên lịch',
                ]);
            }
        }
    }
}
