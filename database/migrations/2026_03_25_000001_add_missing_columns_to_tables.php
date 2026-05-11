<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. trainers: thêm experience, description
        Schema::table('trainers', function (Blueprint $table) {
            $table->tinyInteger('experience')->nullable()->after('specialization');
            $table->text('description')->nullable()->after('experience');
        });

        // 2. classes: thêm schedule_date
        Schema::table('classes', function (Blueprint $table) {
            $table->dateTime('schedule_date')->nullable()->after('description');
        });

        // 3. member_feedbacks: thêm comment, title (cột 'email' bị đặt sai tên, giữ lại để tương thích)
        Schema::table('member_feedbacks', function (Blueprint $table) {
            $table->text('comment')->nullable()->after('rating');
            $table->string('title', 200)->nullable()->after('comment');
        });

        // 4. member_profiles: thêm profile_picture, membership_type
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->string('profile_picture', 255)->nullable()->after('join_date');
            $table->string('membership_type', 50)->nullable()->after('profile_picture');
        });

        // 5. employee_profiles: thêm position, department
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->string('position', 100)->nullable()->after('hire_date');
            $table->string('department', 100)->nullable()->after('position');
        });

        // 6. health_metrics: thêm bmi
        Schema::table('health_metrics', function (Blueprint $table) {
            $table->decimal('bmi', 5, 2)->nullable()->after('body_fat_percentage');
        });

        // 7. equipment: thêm serial_number
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('serial_number', 100)->nullable()->unique()->after('equipment_name');
        });

        // 8. membership_plans: thêm value
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->decimal('value', 12, 2)->nullable()->after('price');
        });

        // 9. promotions: thêm code
        Schema::table('promotions', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->unique()->after('title');
        });

        // 10. payments: thêm payment_confirmed
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('payment_confirmed')->default(false)->after('status');
        });

        // 11. pt_contracts: thêm branch_id (FK → branches)
        Schema::table('pt_contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('trainer_id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });

        // 12. checkins: thêm notes
        Schema::table('checkins', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('method');
        });
    }

    public function down(): void
    {
        Schema::table('trainers', function (Blueprint $table) {
            $cols = array_filter(['experience', 'description'], fn($c) => Schema::hasColumn('trainers', $c));
            if ($cols) $table->dropColumn(array_values($cols));
        });

        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'schedule_date')) $table->dropColumn('schedule_date');
        });

        Schema::table('member_feedbacks', function (Blueprint $table) {
            $cols = array_filter(['comment', 'title'], fn($c) => Schema::hasColumn('member_feedbacks', $c));
            if ($cols) $table->dropColumn(array_values($cols));
        });

        Schema::table('member_profiles', function (Blueprint $table) {
            $cols = array_filter(['profile_picture', 'membership_type'], fn($c) => Schema::hasColumn('member_profiles', $c));
            if ($cols) $table->dropColumn(array_values($cols));
        });

        Schema::table('employee_profiles', function (Blueprint $table) {
            $cols = array_filter(['position', 'department'], fn($c) => Schema::hasColumn('employee_profiles', $c));
            if ($cols) $table->dropColumn(array_values($cols));
        });

        Schema::table('health_metrics', function (Blueprint $table) {
            if (Schema::hasColumn('health_metrics', 'bmi')) $table->dropColumn('bmi');
        });

        Schema::table('equipment', function (Blueprint $table) {
            if (Schema::hasColumn('equipment', 'serial_number')) {
                try { $table->dropUnique(['serial_number']); } catch (\Exception $e) {}
                $table->dropColumn('serial_number');
            }
        });

        Schema::table('membership_plans', function (Blueprint $table) {
            if (Schema::hasColumn('membership_plans', 'value')) $table->dropColumn('value');
        });

        Schema::table('promotions', function (Blueprint $table) {
            if (Schema::hasColumn('promotions', 'code')) {
                try { $table->dropUnique(['code']); } catch (\Exception $e) {}
                $table->dropColumn('code');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'payment_confirmed')) $table->dropColumn('payment_confirmed');
        });

        Schema::table('pt_contracts', function (Blueprint $table) {
            if (Schema::hasColumn('pt_contracts', 'branch_id')) {
                try { $table->dropForeign(['branch_id']); } catch (\Exception $e) {}
                $table->dropColumn('branch_id');
            }
        });

        Schema::table('checkins', function (Blueprint $table) {
            if (Schema::hasColumn('checkins', 'notes')) $table->dropColumn('notes');
        });
    }
};
