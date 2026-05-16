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
        Schema::table('ai_recommendations', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('ai_suggestions');
        });
        
        // Update existing ones to be active so they still show up
        \Illuminate\Support\Facades\DB::table('ai_recommendations')->update(['is_active' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_recommendations', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
