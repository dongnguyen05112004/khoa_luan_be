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
            'phone'             => 'nullable|string|max:20',
            'gender'            => 'nullable|in:male,female,other',
            'avatar'            => 'nullable|string|max:255',

            // Bảng member_profiles
            'date_of_birth'     => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:100',
            'health_notes'      => 'nullable|string',
            'goal'              => 'nullable|string|max:255', // Nếu bạn có cột goal ở đâu đó, có thể lưu vào health_notes
        ]);

        DB::beginTransaction();
        try {
            // 1. Cập nhật bảng users
            $userData = [];
            if ($request->has('name')) $userData['name'] = $data['name'];
            if ($request->has('phone')) $userData['phone'] = $data['phone'];
            if ($request->has('gender')) $userData['gender'] = $data['gender'];
            if ($request->has('avatar')) $userData['avatar'] = $data['avatar'];

            if (!empty($userData)) {
                $user->update($userData);
            }

            // 2. Cập nhật hoặc tạo mới bảng member_profiles
            $profileData = [];
            if ($request->has('date_of_birth')) $profileData['date_of_birth'] = $data['date_of_birth'];
            if ($request->has('emergency_contact')) $profileData['emergency_contact'] = $data['emergency_contact'];
            
            // Xử lý goal và health_notes vào chung health_notes nếu database chưa có cột goal
            $notes = [];
            if ($request->has('goal')) $notes[] = "Mục tiêu: " . $data['goal'];
            if ($request->has('health_notes')) $notes[] = $data['health_notes'];
            
            if (!empty($notes)) {
                $profileData['health_notes'] = implode("\n", $notes);
            }

            if (!empty($profileData)) {
                MemberProfile::updateOrCreate(
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
