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
        Schema::table('member_feedbacks', function (Blueprint $table) {
        $table->string('ai_topic', 100)->nullable()->after('ai_sentiment')->comment('Nhãn chủ đề AI tự gắn: Vệ sinh, PT, Máy móc...');
        $table->enum('ai_severity', ['Low', 'Medium', 'High', 'Critical'])->nullable()->after('ai_topic')->comment('Mức độ khẩn cấp');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_feedbacks', function (Blueprint $table) {
        $table->dropColumn(['ai_topic', 'ai_severity']);
    });
    }
};
