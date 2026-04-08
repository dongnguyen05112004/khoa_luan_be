<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\EmployeeProfileController;
use App\Http\Controllers\Api\MemberProfileController;
use App\Http\Controllers\Api\MembershipPlanController;
use App\Http\Controllers\Api\MemberSubscriptionController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\TrainerController;
use App\Http\Controllers\Api\GymClassController;
use App\Http\Controllers\Api\ClassRegistrationController;
use App\Http\Controllers\Api\CheckinController;
use App\Http\Controllers\Api\PtContractController;
use App\Http\Controllers\Api\PtBookingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\EquipmentMaintenanceController;
use App\Http\Controllers\Api\OtherExpenseController;
use App\Http\Controllers\Api\HealthMetricController;
use App\Http\Controllers\Api\MemberFeedbackController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AiRecommendationController;
use App\Http\Controllers\Api\BusinessReportController;
use App\Http\Controllers\Api\SystemSettingController;
use App\Models\User;
/*
|--------------------------------------------------------------------------
| 1. XÁC THỰC (PUBLIC - không cần đăng nhập)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| 2. PROTECTED ROUTES (cần đăng nhập - auth:sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*--- Tài khoản cá nhân ---*/
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
    Route::put('/me',      [AuthController::class, 'updateMe']);

    /*==========================================================
    | 1. QUẢN LÝ TÀI KHOẢN & PHÂN QUYỀN
    ==========================================================*/

    // Roles
    Route::resource('roles', RoleController::class);

    // Users
    Route::get('/users/admin-get-user', [UserController::class, 'adminGetUser']);
    Route::resource('users', UserController::class);
    Route::get('/users/{id}/subscriptions',   [UserController::class, 'subscriptions']);
    Route::get('/users/{id}/checkins',        [UserController::class, 'checkins']);
    Route::get('/users/{id}/health-metrics',  [UserController::class, 'healthMetrics']);
    Route::get('/users/{id}/admin-get-user',  [UserController::class, 'adminGetUser']);


    // Employee Profiles
    Route::resource('employee-profiles', EmployeeProfileController::class);

    // Member Profiles
    Route::resource('member-profiles', MemberProfileController::class);

    /*==========================================================
    | 2. CHI NHÁNH
    ==========================================================*/
    Route::resource('branches', BranchController::class);

    /*==========================================================
    | 3. QUẢN LÝ HỘI VIÊN (Membership)
    ==========================================================*/

    // Membership Plans (Gói tập)
    Route::resource('membership-plans', MembershipPlanController::class);

    // Member Subscriptions (Đăng ký gói tập)
    Route::resource('member-subscriptions', MemberSubscriptionController::class);

    // Promotions (Khuyến mãi)
    Route::resource('promotions', PromotionController::class);

    /*==========================================================
    | 4. QUẢN LÝ NHÂN VIÊN & HUẤN LUYỆN VIÊN
    ==========================================================*/

    // Trainers (Huấn luyện viên)
    Route::resource('trainers', TrainerController::class);
    Route::get('/trainers/{id}/schedule',  [TrainerController::class, 'schedule']);

    /*==========================================================
    | 5. QUẢN LÝ LỊCH TẬP & ĐẶT LỊCH
    ==========================================================*/

    // Classes (Lớp học)
    Route::resource('classes', GymClassController::class);

    // Class Registrations (Đăng ký lớp)
    Route::resource('class-registrations', ClassRegistrationController::class);

    // PT Contracts (Hợp đồng PT)
    Route::resource('pt-contracts', PtContractController::class);

    // PT Bookings (Đặt lịch PT)
    Route::resource('pt-bookings', PtBookingController::class);

    /*==========================================================
    | 6. CHECK-IN & THEO DÕI HOẠT ĐỘNG
    ==========================================================*/

    // Checkins
    Route::resource('checkins', CheckinController::class);

    // Health Metrics (Chỉ số sức khỏe)
    Route::resource('health-metrics', HealthMetricController::class);

    /*==========================================================
    | 7. THANH TOÁN
    ==========================================================*/
    Route::resource('payments', PaymentController::class);

    /*==========================================================
    | 8. QUẢN LÝ THIẾT BỊ
    ==========================================================*/

    // Equipment (Thiết bị)

    Route::get('/equipment',                      [EquipmentController::class, 'index']);
    Route::post('/equipment',                     [EquipmentController::class, 'store']);
    Route::get('/equipment/{id}',                 [EquipmentController::class, 'show']);
    Route::put('/equipment/{id}',                 [EquipmentController::class, 'update']);
    Route::patch('/equipment/{id}/status',        [EquipmentController::class, 'updateStatus']); // Cập nhật trạng thái
    Route::delete('/equipment/{id}',              [EquipmentController::class, 'destroy']);

    // Equipment Maintenance (Bảo trì thiết bị)
    Route::get('/equipment-maintenance/due-soon', [EquipmentMaintenanceController::class, 'dueSoon']); // Lịch sắp đến hạn
    Route::get('/equipment-maintenance',          [EquipmentMaintenanceController::class, 'index']);
    Route::post('/equipment-maintenance',         [EquipmentMaintenanceController::class, 'store']);
    Route::get('/equipment-maintenance/{id}',     [EquipmentMaintenanceController::class, 'show']);
    Route::put('/equipment-maintenance/{id}',     [EquipmentMaintenanceController::class, 'update']);
    Route::delete('/equipment-maintenance/{id}',  [EquipmentMaintenanceController::class, 'destroy']);


    // Other Expenses (Chi phí khác)
    Route::resource('other-expenses', OtherExpenseController::class);

    /*==========================================================
    | 9. BÁO CÁO & PHÂN TÍCH
    ==========================================================*/
    Route::resource('business-reports', BusinessReportController::class);

    /*==========================================================
    | 10. PHẢN HỒI KHÁCH HÀNG
    ==========================================================*/
    Route::resource('member-feedbacks', MemberFeedbackController::class);

    /*==========================================================
    | 11 & 12. GỢI Ý AI & CÁ NHÂN HÓA
    ==========================================================*/
    Route::resource('ai-recommendations', AiRecommendationController::class);

    /*==========================================================
    | NHẬT KÝ HOẠT ĐỘNG & CÀI ĐẶT HỆ THỐNG
    ==========================================================*/

    // Activity Logs
    Route::resource('activity-logs', ActivityLogController::class, ['only' => ['index', 'store', 'show', 'destroy']]);

    // System Settings
    Route::resource('system-settings', SystemSettingController::class);
});
