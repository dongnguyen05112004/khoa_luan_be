<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Thêm cột cancel_reason vào bảng member_subscriptions
     * để lưu lý do hủy hợp đồng khi gọi API POST /api/contracts/{id}/cancel
     */
    public function up(): void
    {
        Schema::table('member_subscriptions', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('member_subscriptions', function (Blueprint $table) {
            $table->dropColumn('cancel_reason');
        });
    }
};
