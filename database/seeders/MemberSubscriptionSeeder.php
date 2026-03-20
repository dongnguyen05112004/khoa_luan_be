<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MemberSubscription;
use App\Models\User;
use App\Models\MembershipPlan;

class MemberSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $members = User::where('role_id', 5)->get();
        $plans   = MembershipPlan::all();
        if ($members->isEmpty() || $plans->isEmpty()) return;

        $subscriptions = [
            // member1 - Gói 3 tháng
            [
                'user_id'    => $members->get(0)?->id,
                'plan_id'    => $plans->get(1)?->id ?? $plans->first()->id,
                'start_date' => '2026-01-10',
                'end_date'   => '2026-04-10',
                'price'      => 1200000,
                'status'     => 'active',
            ],
            // member2 - Gói 1 tháng (sắp hết hạn - test cảnh báo)
            [
                'user_id'    => $members->get(1)?->id,
                'plan_id'    => $plans->get(0)?->id ?? $plans->first()->id,
                'start_date' => '2026-02-20',
                'end_date'   => '2026-03-22',
                'price'      => 500000,
                'status'     => 'active',
            ],
            // member3 - Gói 6 tháng
            [
                'user_id'    => $members->get(2)?->id,
                'plan_id'    => $plans->get(2)?->id ?? $plans->first()->id,
                'start_date' => '2025-10-01',
                'end_date'   => '2026-04-01',
                'price'      => 2000000,
                'status'     => 'active',
                'promotion_id' => 1,
            ],
            // member4 - Gói 12 tháng VIP
            [
                'user_id'    => $members->get(3)?->id,
                'plan_id'    => $plans->get(3)?->id ?? $plans->first()->id,
                'start_date' => '2026-01-01',
                'end_date'   => '2026-12-31',
                'price'      => 3500000,
                'status'     => 'active',
            ],
            // member5 - Gói hết hạn
            [
                'user_id'    => $members->get(4)?->id,
                'plan_id'    => $plans->get(0)?->id ?? $plans->first()->id,
                'start_date' => '2025-12-01',
                'end_date'   => '2025-12-31',
                'price'      => 500000,
                'status'     => 'expired',
            ],
        ];

        foreach ($subscriptions as $sub) {
            if ($sub['user_id'] && $sub['plan_id']) {
                MemberSubscription::firstOrCreate(
                    ['user_id' => $sub['user_id'], 'plan_id' => $sub['plan_id'], 'start_date' => $sub['start_date']],
                    $sub
                );
            }
        }
    }
}
