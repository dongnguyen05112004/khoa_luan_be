<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique()->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->string('payable_type', 50)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 50)->default('cash');
            $table->enum('status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->text('note')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('member_subscriptions')->onDelete('set null');
            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
