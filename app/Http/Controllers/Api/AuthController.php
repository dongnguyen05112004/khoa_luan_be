<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone'    => 'nullable|string|max:20',
            'gender'   => 'nullable|in:male,female,other',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        return response()->json([
            'message' => 'Đăng ký thành công',
            'user'    => $user,
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
        $user->load(['role', 'branch']);

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user'    => $user,
            'token'   => $user->createToken('api-token')->plainTextToken,
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
