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
        $members = User::where('role_id', 5)->where('branch_id', 1)->get();
        $plans   = MembershipPlan::all();
        if ($members->isEmpty() || $plans->isEmpty()) return;

        foreach ($members as $member) {
            $plan = $plans->random();
            $startDate = \Carbon\Carbon::now()->subDays(rand(10, 100));
            $months = 1;
            if (str_contains(strtolower($plan->plan_name), '3 tháng')) $months = 3;
            elseif (str_contains(strtolower($plan->plan_name), '6 tháng')) $months = 6;
            elseif (str_contains(strtolower($plan->plan_name), '1 năm')) $months = 12;
            
            $endDate = (clone $startDate)->addMonths($months);

            MemberSubscription::create([
                'user_id'    => $member->id,
                'plan_id'    => $plan->id,
                'start_date' => $startDate->toDateString(),
                'end_date'   => $endDate->toDateString(),
                'price'      => $plan->price,
                'status'     => $endDate->isPast() ? 'expired' : 'active',
                'promotion_id' => rand(0, 3) == 0 ? 1 : null,
            ]);
        }
    }
}
