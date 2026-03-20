<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->dateTime('check_in_at');
            $table->dateTime('check_out_at')->nullable();
            $table->integer('duration')->nullable()->comment('minutes');
            $table->boolean('schedule_done')->default(false);
            $table->enum('method', ['qr', 'face', 'manual'])->default('manual');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};
