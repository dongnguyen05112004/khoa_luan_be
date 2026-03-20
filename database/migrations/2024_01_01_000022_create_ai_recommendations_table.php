<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('recommendation_type', 100)->nullable();
            $table->text('ai_diagnosis')->nullable();
            $table->text('title')->nullable();
            $table->text('ai_suggestions')->nullable();
            $table->boolean('is_system_created')->default(false);
            $table->dateTime('created_at_custom')->nullable();
            $table->dateTime('ai_next')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
    }
};
