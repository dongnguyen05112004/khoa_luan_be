<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_reports', function (Blueprint $table) {
            $table->id();
            $table->string('id_report', 50)->nullable();
            $table->date('date_from_summary');
            $table->string('date_from', 50)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('ai_diagnosis')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_reports');
    }
};
