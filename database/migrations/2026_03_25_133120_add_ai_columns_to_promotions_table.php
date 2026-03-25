<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
        $table->integer('current_usage')->default(0)->after('usage_limit')->comment('Số lượt đã sử dụng để tính tỷ lệ chuyển đổi');
        $table->boolean('is_active')->default(true)->after('end_date');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
        $table->dropColumn(['current_usage', 'is_active']);
    });
    }
};
