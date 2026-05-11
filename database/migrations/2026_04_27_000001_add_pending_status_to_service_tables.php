<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm trạng thái 'pending' (Chờ thanh toán) vào:
 *   - member_subscriptions.status
 *   - pt_contracts.status
 *
 * Phục vụ tính năng "Mua dịch vụ" của hội viên:
 * Khi hội viên đăng ký gói, hợp đồng được tạo với status = 'pending'
 * và chờ nhân viên lễ tân xác nhận thanh toán để chuyển sang 'active'.
 */
return new class extends Migration {
    public function up(): void
    {
        // MySQL: thay đổi ENUM bằng cách ALTER COLUMN
        DB::statement("ALTER TABLE member_subscriptions MODIFY COLUMN status ENUM('active','expired','cancelled','pending') NOT NULL DEFAULT 'active'");
        DB::statement("ALTER TABLE pt_contracts MODIFY COLUMN status ENUM('active','completed','cancelled','pending') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        // Chuyển các bản ghi đang là 'pending' sang 'cancelled' trước khi xóa enum 'pending'
        DB::table('member_subscriptions')->where('status', 'pending')->update(['status' => 'cancelled']);
        DB::table('pt_contracts')->where('status', 'pending')->update(['status' => 'cancelled']);

        // Rollback: xóa 'pending' khỏi định nghĩa ENUM
        DB::statement("ALTER TABLE member_subscriptions MODIFY COLUMN status ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active'");
        DB::statement("ALTER TABLE pt_contracts MODIFY COLUMN status ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active'");
    }
};
