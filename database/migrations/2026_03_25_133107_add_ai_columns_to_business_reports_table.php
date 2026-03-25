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
        Schema::table('business_reports', function (Blueprint $table) {
        // Thêm loại báo cáo (Monthly, Quarterly, Equipment, Staff...)
        $table->string('report_type', 50)->nullable()->after('id_report');

        // Lưu dữ liệu thô dạng JSON string trước khi gửi qua API
        $table->longText('raw_data_summary')->nullable()->after('amount');

        // Cột ai_diagnosis đã có, ta thêm forecast và suggestions
        $table->text('ai_forecast')->nullable()->after('ai_diagnosis')->comment('Dự báo của AI cho tháng tới');
        $table->text('ai_suggestions')->nullable()->after('ai_forecast')->comment('Các gợi ý chiến lược kinh doanh');

        // Ai là người tạo báo cáo này
        $table->unsignedBigInteger('created_by')->nullable()->after('ai_suggestions');
        $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_reports', function (Blueprint $table) {
        $table->dropForeign(['created_by']);
        $table->dropColumn(['report_type', 'raw_data_summary', 'ai_forecast', 'ai_suggestions', 'created_by']);
    });
    }
};
