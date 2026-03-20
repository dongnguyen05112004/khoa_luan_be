<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            $table->unsignedBigInteger('role_id')->nullable()->after('branch_id');
            $table->string('full_name', 150)->nullable()->after('role_id');
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('gender', 10)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->string('card_number', 50)->nullable()->unique();
            $table->string('e_number', 50)->nullable();
            $table->enum('state', ['active', 'inactive', 'banned'])->default('active');
            $table->softDeletes();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn(['branch_id', 'role_id', 'full_name', 'phone', 'gender', 'avatar', 'card_number', 'e_number', 'state', 'deleted_at']);
        });
    }
};
