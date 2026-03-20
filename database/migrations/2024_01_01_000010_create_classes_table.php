<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('class_name', 150);
            $table->unsignedBigInteger('trainer_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->integer('max_members')->default(20);
            $table->decimal('class_cost', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('trainer_id')->references('id')->on('trainers')->onDelete('set null');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
