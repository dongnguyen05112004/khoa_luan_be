<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\MemberSubscription;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $subscriptions = MemberSubscription::all();
        if ($subscriptions->isEmpty()) return;

        foreach ($subscriptions as $i => $sub) {
            Payment::firstOrCreate(
                ['invoice_number' => 'INV-2026-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT)],
                [
                    'invoice_number' => 'INV-2026-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'user_id'        => $sub->user_id,
                    'subscription_id'=> $sub->id,
                    'amount'         => $sub->price,
                    'payment_date'   => $sub->start_date,
                    'payment_method' => ['cash', 'transfer', 'momo', 'vnpay'][$i % 4],
                    'status'         => $sub->status === 'active' ? 'paid' : 'paid',
                    'note'              => 'Thanh toán gói tập ' . $sub->plan?->plan_name,
                    'promotion_id'      => $sub->promotion_id,
                    'payment_confirmed' => true,
                ]
            );
        }

        // Thêm 1 giao dịch pending để test
        Payment::firstOrCreate(
            ['invoice_number' => 'INV-2026-PEND'],
            [
                'invoice_number' => 'INV-2026-PEND',
                'user_id'        => $subscriptions->first()->user_id,
                'amount'         => 500000,
                'payment_date'   => now()->toDateString(),
                'payment_method' => 'momo',
                'status'         => 'pending',
                'note'           => 'Đang chờ xác nhận thanh toán',
            ]
        );
    }
}
