<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('member_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('trainer_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->integer('rating')->nullable();
            $table->text('email')->nullable();
            $table->text('ai_sentiment')->nullable();
            $table->decimal('ai_score', 4, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('trainer_id')->references('id')->on('trainers')->onDelete('set null');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_feedbacks');
    }
};
