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
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('severity', 20)->default('info')->after('action');
            // Thêm loại đối tượng và ID đối tượng bị tác động (Target Object)
            $table->string('target_type', 50)->nullable()->after('severity');
            $table->string('target_id', 50)->nullable()->after('target_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            //
        });
    }
};
