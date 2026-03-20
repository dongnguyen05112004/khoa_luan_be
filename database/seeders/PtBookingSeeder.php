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
        $contracts = PtContract::where('status', 'active')->with('trainer')->get();
        if ($contracts->isEmpty()) return;

        foreach ($contracts as $contract) {
            $bookings = [
                [
                    'contract_id'   => $contract->id,
                    'trainer_id'    => $contract->trainer_id,
                    'schedule_time' => '2026-03-20 08:00:00',
                    'status'        => 'confirmed',
                    'notes'         => 'Buổi tập upper body',
                ],
                [
                    'contract_id'   => $contract->id,
                    'trainer_id'    => $contract->trainer_id,
                    'schedule_time' => '2026-03-22 09:00:00',
                    'status'        => 'pending',
                    'notes'         => 'Buổi tập lower body',
                ],
            ];
            foreach ($bookings as $booking) {
                PtBooking::firstOrCreate(
                    ['contract_id' => $booking['contract_id'], 'schedule_time' => $booking['schedule_time']],
                    $booking
                );
            }
        }
    }
}
