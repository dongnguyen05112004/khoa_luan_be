<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            // serial_number: mã định danh duy nhất (nếu chưa có)
            if (!Schema::hasColumn('equipment', 'serial_number')) {
                $table->string('serial_number', 100)->nullable()->unique()->after('equipment_name')
                      ->comment('Mã serial / mã thiết bị');
            }

            // Các field bổ sung cho giao diện quản lý
            if (!Schema::hasColumn('equipment', 'location')) {
                $table->string('location', 150)->nullable()->after('serial_number')
                      ->comment('Khu vực / vị trí đặt thiết bị (VD: Cardio A, Tầng 2)');
            }
            if (!Schema::hasColumn('equipment', 'type')) {
                $table->string('type', 50)->nullable()->after('location')
                      ->comment('Loại thiết bị (Cardio, Strength, Flexibility, Other)');
            }
            if (!Schema::hasColumn('equipment', 'brand')) {
                $table->string('brand', 100)->nullable()->after('type')
                      ->comment('Thương hiệu / hãng sản xuất');
            }
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['serial_number', 'location', 'type', 'brand']);
        });
    }
};
