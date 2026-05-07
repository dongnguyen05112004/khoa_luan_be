<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * POST /api/forgot-password
     *
     * Nhận email → tạo token → lưu vào password_reset_tokens → trả về token (hoặc gửi mail).
     * Trong môi trường local (MAIL_MAILER=log), token sẽ được trả thẳng trong response
     * để frontend có thể dùng ngay mà không cần cấu hình mail server.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Luôn trả 200 để tránh lộ thông tin tài khoản tồn tại hay không
        if (!$user) {
            return response()->json([
                'message' => 'Nếu email tồn tại trong hệ thống, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu.',
            ]);
        }

        // Tạo token ngẫu nhiên 64 ký tự
        $token = Str::random(64);

        // Xóa token cũ (nếu có) rồi lưu token mới
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        // Gửi mail (nếu MAIL_MAILER=log thì mail sẽ ghi vào storage/logs/laravel.log)
        try {
            Mail::send([], [], function ($message) use ($user, $token) {
                $resetUrl = config('app.frontend_url', 'http://localhost:5173')
                    . '/reset-password?token=' . $token
                    . '&email=' . urlencode($user->email);

                $message->to($user->email, $user->full_name ?? $user->name)
                    ->subject('Đặt lại mật khẩu - ' . config('app.name'))
                    ->html(
                        '<p>Xin chào <strong>' . e($user->full_name ?? $user->name) . '</strong>,</p>'
                        . '<p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu của bạn.</p>'
                        . '<p><a href="' . $resetUrl . '" style="background:#4F46E5;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;">Đặt lại mật khẩu</a></p>'
                        . '<p>Hoặc sao chép đường dẫn: <br><code>' . $resetUrl . '</code></p>'
                        . '<p>Token có hiệu lực trong <strong>60 phút</strong>.</p>'
                        . '<p>Nếu bạn không yêu cầu, hãy bỏ qua email này.</p>'
                    );
            });
        } catch (\Exception $e) {
            // Ghi log lỗi mail nhưng không ảnh hưởng response
            Log::error('Forgot password mail error: ' . $e->getMessage());
        }

        // Trong môi trường local / debug → trả token thẳng để test dễ hơn
        $response = [
            'message' => 'Nếu email tồn tại trong hệ thống, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu.',
        ];

        if (config('app.debug')) {
            $response['debug_token'] = $token;
            $response['debug_email'] = $user->email;
            $response['note'] = 'debug_token chỉ hiển thị khi APP_DEBUG=true. Xóa khỏi production.';
        }

        return response()->json($response);
    }

    /**
     * POST /api/reset-password
     *
     * Nhận email + token + password mới → xác minh → cập nhật mật khẩu.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email',
            'token'                 => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        // Lấy bản ghi reset token
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'Token không hợp lệ hoặc đã hết hạn.',
            ], 422);
        }

        // Kiểm tra token có khớp không
        if (!Hash::check($request->token, $record->token)) {
            return response()->json([
                'message' => 'Token không hợp lệ hoặc đã hết hạn.',
            ], 422);
        }

        // Kiểm tra token hết hạn (60 phút)
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->diffInMinutes(Carbon::now()) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'message' => 'Token đã hết hạn. Vui lòng yêu cầu đặt lại mật khẩu mới.',
            ], 422);
        }

        // Tìm user và cập nhật mật khẩu
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'Không tìm thấy tài khoản.',
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Xóa tất cả Sanctum token cũ (bắt buộc đăng nhập lại)
        $user->tokens()->delete();

        // Xóa reset token sau khi dùng
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập lại.',
        ]);
    }
}
