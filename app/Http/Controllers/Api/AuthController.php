<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/register
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:150',
            'email'     => 'required|string|email|unique:users',
            'password'  => 'required|string|min:8|confirmed',
            'phone'     => 'nullable|string|max:20',
            'gender'    => 'nullable|in:male,female,other',
            'cmnd'      => 'nullable|string|max:20',
        ]);

        // Tự động gán role "member"
        $memberRole = Role::where('role_name', 'member')->first();
        if ($memberRole) {
            $data['role_id'] = $memberRole->id;
        }

        // Tự động gán branch đầu tiên nếu chưa có
        $firstBranch = \App\Models\Branch::first();
        if ($firstBranch) {
            $data['branch_id'] = $firstBranch->id;
        }

        if ($request->has('cmnd')) {
            $data['card_number'] = $request->cmnd;
        }

        // Tự động sinh name = "Hội viên X" (X = số thứ tự member tiếp theo)
        $memberCount  = User::where('role_id', $memberRole?->id)->count();
        $data['name'] = 'Hội Viên ' . ($memberCount + 1);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        return response()->json([
            'message' => 'Đăng ký thành công',
            'user'    => $user->load('role'),
            'token'   => $user->createToken('api-token')->plainTextToken,
        ], 201);
    }

    /**
     * POST /api/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Thông tin đăng nhập không đúng.'],
            ]);
        }

        $user = Auth::user();

        if ($user->state !== 'active') {
            if ($request->hasSession()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            throw ValidationException::withMessages([
                'email' => ['Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên để biết thêm chi tiết.'],
            ]);
        }

        $user->load(['role', 'branch']);

        // Bản đồ role → đường dẫn UI tương ứng
        $redirectMap = [
            'admin'   => '/admin/quanlynguoidung',
            'manager' => '/quanly/baocao',
            'staff'   => '/nhanvien',
            'trainer' => '/PT',
            'member'  => '/khachhang/ho_so_ca_nhan',
        ];

        $roleName    = strtolower($user->role?->role_name ?? '');
        $redirectUrl = $redirectMap[$roleName] ?? '/dashboard';

        return response()->json([
            'message'      => 'Đăng nhập thành công',
            'user'         => $user,
            'token'        => $user->createToken('api-token')->plainTextToken,
            'redirect_url' => $redirectUrl,
        ]);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        // Xóa token (nếu người dùng đăng nhập bằng Bearer Token)
        /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        // Đăng xuất khỏi guard web (nếu người dùng đăng nhập bằng Session/Cookie)
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Đăng xuất thành công']);
    }

    /**
     * GET /api/me
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['role', 'branch', 'memberProfile', 'employeeProfile']);

        return response()->json($user);
    }

    /**
     * PUT /api/me
     */
    public function updateMe(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'full_name'=> 'sometimes|string|max:150',
            'phone'    => 'sometimes|string|max:20',
            'gender'   => 'sometimes|in:male,female,other',
            'avatar'   => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json(['message' => 'Cập nhật thành công', 'user' => $user]);
    }
}
