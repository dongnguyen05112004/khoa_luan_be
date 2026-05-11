<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Them cot is_active va current_usage vao bang promotions
     * de ho tro validate ma khuyen mai va dem luot su dung
     */
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            if (!Schema::hasColumn('promotions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('usage_limit');
            }
            if (!Schema::hasColumn('promotions', 'current_usage')) {
                $table->integer('current_usage')->default(0)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'current_usage']);
        });
    }
};
