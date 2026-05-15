<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->addPaymentBranchSnapshot();

        $this->addIndexIfMissing('users', 'users_role_branch_state_created_idx', ['role_id', 'branch_id', 'state', 'created_at']);
        $this->addIndexIfMissing('users', 'users_branch_role_idx', ['branch_id', 'role_id']);

        $this->addIndexIfMissing('member_subscriptions', 'member_subs_user_status_end_idx', ['user_id', 'status', 'end_date']);
        $this->addIndexIfMissing('member_subscriptions', 'member_subs_status_end_idx', ['status', 'end_date']);
        $this->addIndexIfMissing('member_subscriptions', 'member_subs_plan_status_idx', ['plan_id', 'status']);

        $this->addIndexIfMissing('payments', 'payments_status_date_idx', ['status', 'payment_date']);
        $this->addIndexIfMissing('payments', 'payments_user_date_idx', ['user_id', 'payment_date']);
        $this->addIndexIfMissing('payments', 'payments_method_date_idx', ['payment_method', 'payment_date']);
        $this->addIndexIfMissing('payments', 'payments_sub_status_idx', ['subscription_id', 'status']);
        $this->addIndexIfMissing('payments', 'payments_payable_idx', ['payable_type', 'payable_id']);
        $this->addIndexIfMissing('payments', 'payments_branch_status_date_idx', ['branch_id', 'status', 'payment_date']);

        $this->addIndexIfMissing('checkins', 'checkins_user_at_idx', ['user_id', 'check_in_at']);
        $this->addIndexIfMissing('checkins', 'checkins_branch_at_idx', ['branch_id', 'check_in_at']);

        $this->addIndexIfMissing('health_metrics', 'health_metrics_user_record_idx', ['user_id', 'record_date']);

        $this->addUniqueIfNoDuplicates('trainers', 'trainers_user_unique', ['user_id']);
        $this->addIndexIfMissing('trainers', 'trainers_branch_idx', ['branch_id']);

        $this->addIndexIfMissing('classes', 'classes_branch_trainer_schedule_idx', ['branch_id', 'trainer_id', 'schedule_date']);
        $this->addUniqueIfNoDuplicates('class_registrations', 'class_regs_user_class_unique', ['user_id', 'class_id']);
        $this->addIndexIfMissing('class_registrations', 'class_regs_class_status_idx', ['class_id', 'status']);

        $this->addIndexIfMissing('pt_contracts', 'pt_contracts_user_status_idx', ['user_id', 'status']);
        $this->addIndexIfMissing('pt_contracts', 'pt_contracts_trainer_status_idx', ['trainer_id', 'status']);
        $this->addIndexIfMissing('pt_contracts', 'pt_contracts_branch_status_idx', ['branch_id', 'status']);

        $this->addIndexIfMissing('pt_bookings', 'pt_bookings_trainer_time_idx', ['trainer_id', 'schedule_time']);
        $this->addIndexIfMissing('pt_bookings', 'pt_bookings_contract_time_idx', ['contract_id', 'schedule_time']);
        $this->addIndexIfMissing('pt_bookings', 'pt_bookings_status_time_idx', ['status', 'schedule_time']);

        $this->addIndexIfMissing('equipment', 'equipment_branch_status_created_idx', ['branch_id', 'status', 'created_at']);
        $this->addIndexIfMissing('equipment_maintenance', 'equipment_maint_equipment_date_idx', ['equipment_id', 'maintenance_date']);
        $this->addIndexIfMissing('equipment_maintenance', 'equipment_maint_periodic_next_idx', ['is_periodic', 'next_maintenance_date']);

        $this->addIndexIfMissing('other_expenses', 'other_expenses_branch_date_idx', ['branch_id', 'expense_date']);

        $this->addIndexIfMissing('member_feedbacks', 'member_feedbacks_trainer_created_idx', ['trainer_id', 'created_at']);
        $this->addIndexIfMissing('member_feedbacks', 'member_feedbacks_class_created_idx', ['class_id', 'created_at']);
        $this->addIndexIfMissing('member_feedbacks', 'member_feedbacks_ai_severity_idx', ['ai_severity', 'created_at']);

        $this->addIndexIfMissing('activity_logs', 'activity_logs_created_idx', ['created_at']);
        $this->addIndexIfMissing('activity_logs', 'activity_logs_user_created_idx', ['user_id', 'created_at']);
        $this->addIndexIfMissing('activity_logs', 'activity_logs_action_created_idx', ['action', 'created_at']);
        $this->addIndexIfMissing('activity_logs', 'activity_logs_target_created_idx', ['target_type', 'target_id', 'created_at']);

        $this->addIndexIfMissing('ai_recommendations', 'ai_recs_user_type_created_idx', ['user_id', 'recommendation_type', 'created_at']);
        $this->addIndexIfMissing('business_reports', 'business_reports_date_idx', ['date_from_summary']);
        $this->addIndexIfMissing('business_reports', 'business_reports_type_date_idx', ['report_type', 'date_from_summary']);
        $this->addIndexIfMissing('system_settings', 'system_settings_group_idx', ['setting_group']);

        $this->addIndexIfMissing('membership_plans', 'membership_plans_status_price_idx', ['status', 'price']);
        $this->addIndexIfMissing('promotions', 'promotions_active_dates_usage_idx', ['is_active', 'start_date', 'end_date', 'current_usage']);
    }

    public function down(): void
    {
        $this->dropPaymentBranchForeign();

        foreach ([
            ['users', 'users_role_branch_state_created_idx'],
            ['users', 'users_branch_role_idx'],
            ['member_subscriptions', 'member_subs_user_status_end_idx'],
            ['member_subscriptions', 'member_subs_status_end_idx'],
            ['member_subscriptions', 'member_subs_plan_status_idx'],
            ['payments', 'payments_status_date_idx'],
            ['payments', 'payments_user_date_idx'],
            ['payments', 'payments_method_date_idx'],
            ['payments', 'payments_sub_status_idx'],
            ['payments', 'payments_payable_idx'],
            ['payments', 'payments_branch_status_date_idx'],
            ['checkins', 'checkins_user_at_idx'],
            ['checkins', 'checkins_branch_at_idx'],
            ['health_metrics', 'health_metrics_user_record_idx'],
            ['trainers', 'trainers_user_unique'],
            ['trainers', 'trainers_branch_idx'],
            ['classes', 'classes_branch_trainer_schedule_idx'],
            ['class_registrations', 'class_regs_user_class_unique'],
            ['class_registrations', 'class_regs_class_status_idx'],
            ['pt_contracts', 'pt_contracts_user_status_idx'],
            ['pt_contracts', 'pt_contracts_trainer_status_idx'],
            ['pt_contracts', 'pt_contracts_branch_status_idx'],
            ['pt_bookings', 'pt_bookings_trainer_time_idx'],
            ['pt_bookings', 'pt_bookings_contract_time_idx'],
            ['pt_bookings', 'pt_bookings_status_time_idx'],
            ['equipment', 'equipment_branch_status_created_idx'],
            ['equipment_maintenance', 'equipment_maint_equipment_date_idx'],
            ['equipment_maintenance', 'equipment_maint_periodic_next_idx'],
            ['other_expenses', 'other_expenses_branch_date_idx'],
            ['member_feedbacks', 'member_feedbacks_trainer_created_idx'],
            ['member_feedbacks', 'member_feedbacks_class_created_idx'],
            ['member_feedbacks', 'member_feedbacks_ai_severity_idx'],
            ['activity_logs', 'activity_logs_created_idx'],
            ['activity_logs', 'activity_logs_user_created_idx'],
            ['activity_logs', 'activity_logs_action_created_idx'],
            ['activity_logs', 'activity_logs_target_created_idx'],
            ['ai_recommendations', 'ai_recs_user_type_created_idx'],
            ['business_reports', 'business_reports_date_idx'],
            ['business_reports', 'business_reports_type_date_idx'],
            ['system_settings', 'system_settings_group_idx'],
            ['membership_plans', 'membership_plans_status_price_idx'],
            ['promotions', 'promotions_active_dates_usage_idx'],
        ] as [$table, $index]) {
            $this->dropIndexIfExists($table, $index);
        }

        if (Schema::hasColumn('payments', 'branch_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('branch_id');
            });
        }
    }

    private function addPaymentBranchSnapshot(): void
    {
        if (!Schema::hasColumn('payments', 'branch_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('user_id');
            });

            DB::statement('
                UPDATE payments
                INNER JOIN users ON users.id = payments.user_id
                SET payments.branch_id = users.branch_id
                WHERE payments.branch_id IS NULL
            ');

            Schema::table('payments', function (Blueprint $table) {
                $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            });
        }
    }

    private function dropPaymentBranchForeign(): void
    {
        if (!Schema::hasColumn('payments', 'branch_id')) {
            return;
        }

        try {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
            });
        } catch (\Throwable) {
            // The foreign key may not exist on partially migrated environments.
        }
    }

    private function addIndexIfMissing(string $table, string $index, array $columns): void
    {
        if ($this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $index) {
            $blueprint->index($columns, $index);
        });
    }

    private function addUniqueIfNoDuplicates(string $table, string $index, array $columns): void
    {
        if ($this->indexExists($table, $index) || $this->hasDuplicateRows($table, $columns)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $index) {
            $blueprint->unique($columns, $index);
        });
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (!$this->indexExists($table, $index)) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
    }

    private function indexExists(string $table, string $index): bool
    {
        return count(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index])) > 0;
    }

    private function hasDuplicateRows(string $table, array $columns): bool
    {
        $columnSql = implode(', ', array_map(fn (string $column) => "`{$column}`", $columns));
        $row = DB::selectOne("
            SELECT COUNT(*) AS duplicates
            FROM (
                SELECT {$columnSql}, COUNT(*) AS row_count
                FROM `{$table}`
                GROUP BY {$columnSql}
                HAVING row_count > 1
                LIMIT 1
            ) AS duplicate_rows
        ");

        return (int) ($row->duplicates ?? 0) > 0;
    }
};
