<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Thứ tự theo phụ thuộc khóa ngoại (bảng cha trước, bảng con sau)
     */
    public function run(): void
    {
        $this->call([
            // 1. Bảng độc lập (không phụ thuộc bảng khác)
            RoleSeeder::class,
            BranchSeeder::class,
            PromotionSeeder::class,
            MembershipPlanSeeder::class,

            // 2. Users (phụ thuộc: roles, branches)
            UserSeeder::class,

            // 3. Profiles (phụ thuộc: users)
            EmployeeProfileSeeder::class,
            MemberProfileSeeder::class,

            // 4. Trainers (phụ thuộc: users, branches)
            TrainerSeeder::class,

            // 5. Classes (phụ thuộc: trainers, branches)
            GymClassSeeder::class,

            // 6. Subscriptions (phụ thuộc: users, membership_plans, promotions)
            MemberSubscriptionSeeder::class,

            // 7. Class Registrations (phụ thuộc: users, classes)
            ClassRegistrationSeeder::class,

            // 8. Check-ins (phụ thuộc: users, branches)
            CheckinSeeder::class,

            // 9. PT (phụ thuộc: users, trainers)
            PtContractSeeder::class,
            PtBookingSeeder::class,

            // 10. Payments (phụ thuộc: users, member_subscriptions, promotions)
            PaymentSeeder::class,

            // 11. Equipment + maintenance (phụ thuộc: branches, users)
            EquipmentSeeder::class,

            // 12. Other Expenses (phụ thuộc: branches, users)
            OtherExpenseSeeder::class,

            // 13. Health Metrics (phụ thuộc: users)
            HealthMetricSeeder::class,

            // 14. Member Feedbacks (phụ thuộc: users, trainers, classes)
            MemberFeedbackSeeder::class,

            // 15. AI Recommendations (phụ thuộc: users)
            AiRecommendationSeeder::class,

            // 16. Business Reports & Settings
            BusinessReportSeeder::class,
            SystemSettingSeeder::class,

            // 17. Activity Logs (phụ thuộc: users)
            ActivityLogSeeder::class,
        ]);
    }
}
