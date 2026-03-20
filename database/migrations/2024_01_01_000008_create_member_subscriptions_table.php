<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('member_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price', 12, 2)->default(0);
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('membership_plans')->onDelete('cascade');
            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_subscriptions');
    }
};
