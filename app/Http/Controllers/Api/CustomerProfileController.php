<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CustomerProfileController extends Controller
{
    /**
     * Lấy thông tin hồ sơ của khách hàng (hội viên) đang đăng nhập
     * GET /api/customer/profile
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Load các relationships cần thiết cho trang hồ sơ cá nhân
        $user->load([
            'memberProfile', 
            'activeSubscription.plan', 
            'healthMetrics' => function($query) {
                $query->orderBy('record_date', 'desc')->take(2); // Lấy 2 bản ghi gần nhất để so sánh
            },
            'aiRecommendations' => function($query) {
                $query->orderBy('created_at', 'desc')->take(5);
            }
        ]);

        return response()->json([
            'message' => 'Lấy hồ sơ cá nhân thành công',
            'data' => $user
        ]);
    }

    /**
     * Cập nhật thông tin hồ sơ cá nhân (bao gồm cả bảng users và member_profiles)
     * POST /api/customer/profile/update hoặc PUT /api/customer/profile
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            // Bảng users
            'name'              => 'nullable|string|max:255',
            'full_name'         => 'nullable|string|max:150',
            'phone'             => 'nullable|string|max:20',
            'gender'            => 'nullable|in:male,female,other',
            'avatar'            => 'nullable|string|max:255',

            // Bảng member_profiles
            'date_of_birth'     => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:100',
            'health_notes'      => 'nullable|string',
            'goal'              => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // 1. Cập nhật bảng users
            $user->update($request->only([
                'name', 'full_name', 'phone', 'gender', 'avatar'
            ]));

            // 2. Cập nhật hoặc tạo mới bảng member_profiles
            $profileData = $request->only(['date_of_birth', 'emergency_contact']);
            
            // Xử lý goal và health_notes
            $healthNotes = $request->input('health_notes', '');
            $goal = $request->input('goal');
            
            if ($goal) {
                // Nếu có goal, đính kèm vào health_notes nếu bạn chưa có cột riêng
                $profileData['health_notes'] = "Mục tiêu: " . $goal . ($healthNotes ? "\n" . $healthNotes : "");
            } elseif ($request->has('health_notes')) {
                $profileData['health_notes'] = $healthNotes;
            }

            if (!empty($profileData)) {
                $user->memberProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $profileData
                );
            }

            DB::commit();

            // Lấy lại dữ liệu mới nhất
            $user->refresh()->load('memberProfile');

            return response()->json([
                'message' => 'Cập nhật hồ sơ thành công',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Lỗi khi cập nhật hồ sơ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa hồ sơ (Chỉ xóa thông tin chi tiết trong bảng member_profiles, không xóa user)
     * DELETE /api/customer/profile
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        
        if ($user->memberProfile) {
            $user->memberProfile->delete();
        }

        return response()->json([
            'message' => 'Đã xóa thông tin chi tiết hồ sơ cá nhân'
        ]);
    }
}
