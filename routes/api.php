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
    Route::get('/roles',          [RoleController::class, 'index']);
    Route::post('/roles',         [RoleController::class, 'store']);
    Route::get('/roles/{id}',     [RoleController::class, 'show']);
    Route::put('/roles/{id}',     [RoleController::class, 'update']);
    Route::delete('/roles/{id}',  [RoleController::class, 'destroy']);

    // Users
    Route::get('/users',              [UserController::class, 'index']);
    Route::post('/users',             [UserController::class, 'store']);
    Route::get('/users/{id}',         [UserController::class, 'show']);
    Route::put('/users/{id}',         [UserController::class, 'update']);
    Route::delete('/users/{id}',      [UserController::class, 'destroy']);
    Route::get('/users/{id}/subscriptions',   [UserController::class, 'subscriptions']);
    Route::get('/users/{id}/checkins',        [UserController::class, 'checkins']);
    Route::get('/users/{id}/health-metrics',  [UserController::class, 'healthMetrics']);

    // Employee Profiles
    Route::get('/employee-profiles',          [EmployeeProfileController::class, 'index']);
    Route::post('/employee-profiles',         [EmployeeProfileController::class, 'store']);
    Route::get('/employee-profiles/{id}',     [EmployeeProfileController::class, 'show']);
    Route::put('/employee-profiles/{id}',     [EmployeeProfileController::class, 'update']);
    Route::delete('/employee-profiles/{id}',  [EmployeeProfileController::class, 'destroy']);

    // Member Profiles
    Route::get('/member-profiles',          [MemberProfileController::class, 'index']);
    Route::post('/member-profiles',         [MemberProfileController::class, 'store']);
    Route::get('/member-profiles/{id}',     [MemberProfileController::class, 'show']);
    Route::put('/member-profiles/{id}',     [MemberProfileController::class, 'update']);
    Route::delete('/member-profiles/{id}',  [MemberProfileController::class, 'destroy']);

    /*==========================================================
    | 2. CHI NHÁNH
    ==========================================================*/
    Route::get('/branches',          [BranchController::class, 'index']);
    Route::post('/branches',         [BranchController::class, 'store']);
    Route::get('/branches/{id}',     [BranchController::class, 'show']);
    Route::put('/branches/{id}',     [BranchController::class, 'update']);
    Route::delete('/branches/{id}',  [BranchController::class, 'destroy']);

    /*==========================================================
    | 3. QUẢN LÝ HỘI VIÊN (Membership)
    ==========================================================*/

    // Membership Plans (Gói tập)
    Route::get('/membership-plans',          [MembershipPlanController::class, 'index']);
    Route::post('/membership-plans',         [MembershipPlanController::class, 'store']);
    Route::get('/membership-plans/{id}',     [MembershipPlanController::class, 'show']);
    Route::put('/membership-plans/{id}',     [MembershipPlanController::class, 'update']);
    Route::delete('/membership-plans/{id}',  [MembershipPlanController::class, 'destroy']);

    // Member Subscriptions (Đăng ký gói tập)
    Route::get('/member-subscriptions',          [MemberSubscriptionController::class, 'index']);
    Route::post('/member-subscriptions',         [MemberSubscriptionController::class, 'store']);
    Route::get('/member-subscriptions/{id}',     [MemberSubscriptionController::class, 'show']);
    Route::put('/member-subscriptions/{id}',     [MemberSubscriptionController::class, 'update']);
    Route::delete('/member-subscriptions/{id}',  [MemberSubscriptionController::class, 'destroy']);

    // Promotions (Khuyến mãi)
    Route::get('/promotions',          [PromotionController::class, 'index']);
    Route::post('/promotions',         [PromotionController::class, 'store']);
    Route::get('/promotions/{id}',     [PromotionController::class, 'show']);
    Route::put('/promotions/{id}',     [PromotionController::class, 'update']);
    Route::delete('/promotions/{id}',  [PromotionController::class, 'destroy']);

    /*==========================================================
    | 4. QUẢN LÝ NHÂN VIÊN & HUẤN LUYỆN VIÊN
    ==========================================================*/

    // Trainers (Huấn luyện viên)
    Route::get('/trainers',                [TrainerController::class, 'index']);
    Route::post('/trainers',               [TrainerController::class, 'store']);
    Route::get('/trainers/{id}',           [TrainerController::class, 'show']);
    Route::put('/trainers/{id}',           [TrainerController::class, 'update']);
    Route::delete('/trainers/{id}',        [TrainerController::class, 'destroy']);
    Route::get('/trainers/{id}/schedule',  [TrainerController::class, 'schedule']);

    /*==========================================================
    | 5. QUẢN LÝ LỊCH TẬP & ĐẶT LỊCH
    ==========================================================*/

    // Classes (Lớp học)
    Route::get('/classes',          [GymClassController::class, 'index']);
    Route::post('/classes',         [GymClassController::class, 'store']);
    Route::get('/classes/{id}',     [GymClassController::class, 'show']);
    Route::put('/classes/{id}',     [GymClassController::class, 'update']);
    Route::delete('/classes/{id}',  [GymClassController::class, 'destroy']);

    // Class Registrations (Đăng ký lớp)
    Route::get('/class-registrations',          [ClassRegistrationController::class, 'index']);
    Route::post('/class-registrations',         [ClassRegistrationController::class, 'store']);
    Route::get('/class-registrations/{id}',     [ClassRegistrationController::class, 'show']);
    Route::put('/class-registrations/{id}',     [ClassRegistrationController::class, 'update']);
    Route::delete('/class-registrations/{id}',  [ClassRegistrationController::class, 'destroy']);

    // PT Contracts (Hợp đồng PT)
    Route::get('/pt-contracts',          [PtContractController::class, 'index']);
    Route::post('/pt-contracts',         [PtContractController::class, 'store']);
    Route::get('/pt-contracts/{id}',     [PtContractController::class, 'show']);
    Route::put('/pt-contracts/{id}',     [PtContractController::class, 'update']);
    Route::delete('/pt-contracts/{id}',  [PtContractController::class, 'destroy']);

    // PT Bookings (Đặt lịch PT)
    Route::get('/pt-bookings',          [PtBookingController::class, 'index']);
    Route::post('/pt-bookings',         [PtBookingController::class, 'store']);
    Route::get('/pt-bookings/{id}',     [PtBookingController::class, 'show']);
    Route::put('/pt-bookings/{id}',     [PtBookingController::class, 'update']);
    Route::delete('/pt-bookings/{id}',  [PtBookingController::class, 'destroy']);

    /*==========================================================
    | 6. CHECK-IN & THEO DÕI HOẠT ĐỘNG
    ==========================================================*/

    // Checkins
    Route::get('/checkins',          [CheckinController::class, 'index']);
    Route::post('/checkins',         [CheckinController::class, 'store']);
    Route::get('/checkins/{id}',     [CheckinController::class, 'show']);
    Route::put('/checkins/{id}',     [CheckinController::class, 'update']);
    Route::delete('/checkins/{id}',  [CheckinController::class, 'destroy']);

    // Health Metrics (Chỉ số sức khỏe)
    Route::get('/health-metrics',          [HealthMetricController::class, 'index']);
    Route::post('/health-metrics',         [HealthMetricController::class, 'store']);
    Route::get('/health-metrics/{id}',     [HealthMetricController::class, 'show']);
    Route::put('/health-metrics/{id}',     [HealthMetricController::class, 'update']);
    Route::delete('/health-metrics/{id}',  [HealthMetricController::class, 'destroy']);

    /*==========================================================
    | 7. THANH TOÁN
    ==========================================================*/
    Route::get('/payments',          [PaymentController::class, 'index']);
    Route::post('/payments',         [PaymentController::class, 'store']);
    Route::get('/payments/{id}',     [PaymentController::class, 'show']);
    Route::put('/payments/{id}',     [PaymentController::class, 'update']);
    Route::delete('/payments/{id}',  [PaymentController::class, 'destroy']);

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
    Route::get('/other-expenses',          [OtherExpenseController::class, 'index']);
    Route::post('/other-expenses',         [OtherExpenseController::class, 'store']);
    Route::get('/other-expenses/{id}',     [OtherExpenseController::class, 'show']);
    Route::put('/other-expenses/{id}',     [OtherExpenseController::class, 'update']);
    Route::delete('/other-expenses/{id}',  [OtherExpenseController::class, 'destroy']);

    /*==========================================================
    | 9. BÁO CÁO & PHÂN TÍCH
    ==========================================================*/
    Route::get('/business-reports',          [BusinessReportController::class, 'index']);
    Route::post('/business-reports',         [BusinessReportController::class, 'store']);
    Route::get('/business-reports/{id}',     [BusinessReportController::class, 'show']);
    Route::put('/business-reports/{id}',     [BusinessReportController::class, 'update']);
    Route::delete('/business-reports/{id}',  [BusinessReportController::class, 'destroy']);

    /*==========================================================
    | 10. PHẢN HỒI KHÁCH HÀNG
    ==========================================================*/
    Route::get('/member-feedbacks',          [MemberFeedbackController::class, 'index']);
    Route::post('/member-feedbacks',         [MemberFeedbackController::class, 'store']);
    Route::get('/member-feedbacks/{id}',     [MemberFeedbackController::class, 'show']);
    Route::put('/member-feedbacks/{id}',     [MemberFeedbackController::class, 'update']);
    Route::delete('/member-feedbacks/{id}',  [MemberFeedbackController::class, 'destroy']);

    /*==========================================================
    | 11 & 12. GỢI Ý AI & CÁ NHÂN HÓA
    ==========================================================*/
    Route::get('/ai-recommendations',          [AiRecommendationController::class, 'index']);
    Route::post('/ai-recommendations',         [AiRecommendationController::class, 'store']);
    Route::get('/ai-recommendations/{id}',     [AiRecommendationController::class, 'show']);
    Route::put('/ai-recommendations/{id}',     [AiRecommendationController::class, 'update']);
    Route::delete('/ai-recommendations/{id}',  [AiRecommendationController::class, 'destroy']);

    /*==========================================================
    | NHẬT KÝ HOẠT ĐỘNG & CÀI ĐẶT HỆ THỐNG
    ==========================================================*/

    // Activity Logs
    Route::get('/activity-logs',          [ActivityLogController::class, 'index']);
    Route::post('/activity-logs',         [ActivityLogController::class, 'store']);
    Route::get('/activity-logs/{id}',     [ActivityLogController::class, 'show']);
    Route::delete('/activity-logs/{id}',  [ActivityLogController::class, 'destroy']);

    // System Settings
    Route::get('/system-settings',          [SystemSettingController::class, 'index']);
    Route::post('/system-settings',         [SystemSettingController::class, 'store']);
    Route::get('/system-settings/{id}',     [SystemSettingController::class, 'show']);
    Route::put('/system-settings/{id}',     [SystemSettingController::class, 'update']);
    Route::delete('/system-settings/{id}',  [SystemSettingController::class, 'destroy']);
});
