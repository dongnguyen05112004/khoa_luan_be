<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('equipment_name', 150);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->date('purchase_date')->nullable();
            $table->enum('status', ['good', 'maintenance', 'broken'])->default('good');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
