<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        /*
        |---------------------------------------------------------------
        | 1. equipment: đổi status từ enum → varchar(20), default 'active'
        |    ('active' = đang sử dụng, 'maintenance' = bảo trì, 'broken' = hỏng)
        |---------------------------------------------------------------
        */
        DB::statement("ALTER TABLE equipment MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'");

        // Chuyển đổi giá trị cũ 'good' → 'active'
        DB::statement("UPDATE equipment SET status = 'active' WHERE status = 'good'");

        /*
        |---------------------------------------------------------------
        | 2. equipment_maintenance: thêm trường lịch bảo trì định kỳ
        |---------------------------------------------------------------
        */
        Schema::table('equipment_maintenance', function (Blueprint $table) {
            $table->boolean('is_periodic')->default(false)->after('cost')
                  ->comment('true = lịch bảo trì định kỳ');
            $table->unsignedSmallInteger('interval_days')->nullable()->after('is_periodic')
                  ->comment('Chu kỳ bảo trì (ngày), VD: 30, 90, 180');
            $table->date('next_maintenance_date')->nullable()->after('interval_days')
                  ->comment('Ngày bảo trì tiếp theo (tính từ maintenance_date + interval_days)');
        });
    }

    public function down(): void
    {
        // Đổi lại status về enum
        DB::statement("UPDATE equipment SET status = 'good' WHERE status = 'active'");
        DB::statement("ALTER TABLE equipment MODIFY COLUMN status ENUM('good','maintenance','broken') NOT NULL DEFAULT 'good'");

        Schema::table('equipment_maintenance', function (Blueprint $table) {
            $table->dropColumn(['is_periodic', 'interval_days', 'next_maintenance_date']);
        });
    }
};
