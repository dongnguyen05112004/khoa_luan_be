<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pt_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('trainer_id');
            $table->integer('total_sessions');
            $table->integer('used_sessions')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price', 12, 2)->default(0);
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('trainer_id')->references('id')->on('trainers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pt_contracts');
    }
};
