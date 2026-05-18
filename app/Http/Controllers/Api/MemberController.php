<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MemberProfile;
use App\Models\MemberSubscription;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * MemberController
 * ---------------------------------------------------------
 * Cung cấp toàn bộ API cho giao diện "Quản lý hội viên":
 *
 *  GET    /api/members                  → Danh sách hội viên (phân trang, tìm kiếm, lọc status)
 *  GET    /api/members/stats            → Thống kê (tổng active, mới hôm nay, …)
 *  GET    /api/members/search           → Tìm kiếm nhanh hội viên cho dropdown
 *  GET    /api/members/{id}             → Chi tiết hội viên
 *  POST   /api/members                  → Thêm hội viên mới
 *  PUT    /api/members/{id}             → Cập nhật thông tin hội viên
 *  DELETE /api/members/{id}             → Xóa hội viên
 *  PATCH  /api/members/{id}/status      → Cập nhật trạng thái (active / on_hold / banned)
 *  GET    /api/members/{id}/checkins    → Lịch sử check-in của hội viên
 *  GET    /api/members/{id}/subscriptions → Lịch sử gói tập của hội viên
 */
class MemberController extends Controller
{
    /*==========================================================================
    | HELPER: Lấy role_id của "member" từ bảng roles
    ==========================================================================*/
    private function getMemberRoleId(): ?int
    {
        $role = DB::table('roles')->where('role_name', 'member')->first();
        return $role ? $role->id : null;
    }

    /*==========================================================================
    | SEARCH NHANH HỘI VIÊN
    |    GET /api/members/search
    |
    |    Query params:
    |      q        * – từ khoá tìm kiếm (tên / email / SĐT / mã thẻ)
    |      limit      – số kết quả tối đa (default 10, max 30)
    |
    |    Response: mảng các object nhỏ gọn cho dropdown:
    |      [{ id, full_name, email, phone, card_number, avatar, status }]
    ==========================================================================*/
    public function search(Request $request)
    {
        $q     = trim($request->get('q', ''));
        $limit = min((int) ($request->get('limit', 10)), 30);

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $memberRoleId = $this->getMemberRoleId();

        $users = User::with('activeSubscription')
            ->when($memberRoleId, fn($query) => $query->where('role_id', $memberRoleId))
            ->where(function ($query) use ($q) {
                $query->where('full_name',   'like', "%$q%")
                      ->orWhere('name',       'like', "%$q%")
                      ->orWhere('email',      'like', "%$q%")
                      ->orWhere('phone',      'like', "%$q%")
                      ->orWhere('card_number','like', "%$q%");
            })
            ->limit($limit)
            ->get();

        $result = $users->map(fn($u) => [
            'id'          => $u->id,
            'full_name'   => $u->full_name ?? $u->name,
            'email'       => $u->email,
            'phone'       => $u->phone,
            'card_number' => $u->card_number,
            'avatar'      => $u->avatar,
            'status'      => $u->state === 'inactive' ? 'on_hold'
                           : ($u->activeSubscription ? 'active' : 'expired'),
        ]);

        return response()->json($result);
    }


    /*==========================================================================
    | 1. DANH SÁCH HỘI VIÊN
    |    GET /api/members
    |
    |    Query params:
    |      search      – tìm theo tên / email / phone / card_number
    |      status      – lọc trạng thái gói tập: active | expired | on_hold | all (default)
    |      branch_id   – lọc theo chi nhánh
    |      per_page    – số bản ghi mỗi trang (default 15)
    |      page        – trang hiện tại
    ==========================================================================*/
    public function index(Request $request)
    {
        $memberRoleId = $this->getMemberRoleId();

        $query = User::with([
                'memberProfile',
                'activeSubscription.plan',
            ])
            ->when($memberRoleId, fn($q) => $q->where('role_id', $memberRoleId))
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($q2) use ($s) {
                    $q2->where('full_name', 'like', "%$s%")
                       ->orWhere('name', 'like', "%$s%")
                       ->orWhere('email', 'like', "%$s%")
                       ->orWhere('phone', 'like', "%$s%")
                       ->orWhere('card_number', 'like', "%$s%");
                });
            });

        // Lọc theo trạng thái gói tập
        $status = $request->status;
        if ($status && $status !== 'all') {
            if ($status === 'on_hold') {
                // on_hold: tài khoản bị tạm khóa (state = inactive)
                $query->where('state', 'inactive');
            } elseif ($status === 'active') {
                $query->where('state', 'active')
                      ->whereHas('subscriptions', fn($q) =>
                          $q->where('status', 'active')
                      );
            } elseif ($status === 'expired') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('subscriptions', fn($q2) =>
                        $q2->where('status', 'active')
                    )->orWhereHas('subscriptions', fn($q2) =>
                        $q2->where('status', 'expired')
                    );
                });
            }
        }

        $perPage = (int) ($request->per_page ?? 15);
        $members = $query->orderByDesc('created_at')->paginate($perPage);

        // Lấy last check-in cho mỗi member
        $userIds = $members->pluck('id')->toArray();
        $lastCheckins = Checkin::select('user_id', DB::raw('MAX(check_in_at) as last_check_in_at'))
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->pluck('last_check_in_at', 'user_id');

        // Transform từng member sang format giao diện
        $members->getCollection()->transform(function ($user) use ($lastCheckins) {
            return $this->formatMember($user, $lastCheckins[$user->id] ?? null);
        });

        return response()->json($members);
    }

    /*==========================================================================
    | 2. THỐNG KÊ TỔNG QUAN
    |    GET /api/members/stats
    |
    |    Response:
    |      total_active      – tổng hội viên đang active
    |      new_today         – hội viên đăng ký mới hôm nay
    |      expiring_soon     – hội viên sắp hết hạn (trong 7 ngày)
    |      total_members     – tổng hội viên
    |      retention_change  – % thay đổi so với tuần trước
    ==========================================================================*/
    public function stats(Request $request)
    {
        $memberRoleId = $this->getMemberRoleId();

        $baseQuery = User::when($memberRoleId, fn($q) => $q->where('role_id', $memberRoleId))
                         ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id));

        $totalMembers = (clone $baseQuery)->count();

        $totalActive = (clone $baseQuery)
            ->where('state', 'active')
            ->whereHas('subscriptions', fn($q) => $q->where('status', 'active'))
            ->count();

        $today = Carbon::today();
        $newToday = (clone $baseQuery)
            ->where('created_at', '>=', $today)
            ->where('created_at', '<', $today->copy()->addDay())
            ->count();

        // Hội viên mới tuần này
        $newThisWeek = (clone $baseQuery)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();

        // Hội viên mới tuần trước
        $newLastWeek = (clone $baseQuery)
            ->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
            ->count();

        // % thay đổi retention so với tuần trước
        $retentionChange = $newLastWeek > 0
            ? round((($newThisWeek - $newLastWeek) / $newLastWeek) * 100, 1)
            : ($newThisWeek > 0 ? 100 : 0);

        // Hội viên sắp hết hạn trong 7 ngày
        $expiringSoon = MemberSubscription::with('user')
            ->where('status', 'active')
            ->whereBetween('end_date', [Carbon::today(), Carbon::today()->addDays(7)])
            ->when(
                $memberRoleId,
                fn($q) => $q->whereHas('user', fn($q2) => $q2->where('role_id', $memberRoleId))
            )
            ->count();

        // Hội viên On-Hold (state=inactive)
        $onHold = (clone $baseQuery)->where('state', 'inactive')->count();

        // Hội viên hết hạn (không có active subscription)
        $expired = (clone $baseQuery)
            ->whereDoesntHave('subscriptions', fn($q) => $q->where('status', 'active'))
            ->count();

        return response()->json([
            'total_members'    => $totalMembers,
            'total_active'     => $totalActive,
            'new_today'        => $newToday,
            'new_this_week'    => $newThisWeek,
            'expiring_soon'    => $expiringSoon,
            'on_hold'          => $onHold,
            'expired'          => $expired,
            'retention_change' => $retentionChange, // ví dụ: +12 hoặc -5
        ]);
    }

    /*==========================================================================
    | 3. CHI TIẾT HỘI VIÊN
    |    GET /api/members/{id}
    ==========================================================================*/
    public function show($id)
    {
        $user = User::with([
            'role',
            'branch',
            'memberProfile',
            'subscriptions.plan',
            'subscriptions.promotion',
            'activeSubscription.plan',
        ])->findOrFail($id);

        // Last check-in
        $lastCheckin = Checkin::with('branch')
            ->where('user_id', $id)
            ->latest('check_in_at')
            ->first();

        $data = $this->formatMember($user, $lastCheckin?->check_in_at);
        $data['member_profile']   = $user->memberProfile;
        $data['subscriptions']    = $user->subscriptions;
        $data['last_checkin']     = $lastCheckin;
        $data['branch']           = $user->branch;
        $data['role']             = $user->role;

        return response()->json($data);
    }

    /*==========================================================================
    | 4. THÊM HỘI VIÊN MỚI
    |    POST /api/members
    |
    |    Body (JSON):
    |      name *          – username
    |      email *         – email đăng nhập
    |      password *      – mật khẩu (min 8 ký tự)
    |      full_name       – họ tên đầy đủ
    |      phone           – số điện thoại
    |      gender          – male | female | other
    |      avatar          – URL ảnh đại diện
    |      card_number     – mã thẻ thành viên (#PC-XXXX)
    |      branch_id       – chi nhánh
    |      date_of_birth   – ngày sinh (YYYY-MM-DD)
    |      join_date       – ngày tham gia (YYYY-MM-DD)
    |      emergency_contact – liên hệ khẩn cấp
    |      health_notes    – ghi chú sức khỏe
    |      membership_type – loại hội viên (text)
    |
    |      [Gói tập – tùy chọn]
    |      plan_id         – ID gói tập
    |      promotion_id    – ID khuyến mãi
    |      start_date      – ngày bắt đầu gói (YYYY-MM-DD)
    |      end_date        – ngày kết thúc gói (YYYY-MM-DD)
    |      price           – giá gói
    ==========================================================================*/
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'required|string|min:8',
            'full_name'         => 'nullable|string|max:150',
            'phone'             => 'nullable|string|max:20',
            'gender'            => 'nullable|in:male,female,other',
            'avatar'            => 'nullable|string|max:255',
            'card_number'       => 'nullable|string|max:50|unique:users,card_number',
            'branch_id'         => 'nullable|exists:branches,id',
            // Member Profile
            'date_of_birth'     => 'nullable|date',
            'join_date'         => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:100',
            'health_notes'      => 'nullable|string',
            'membership_type'   => 'nullable|string|max:50',
            'profile_picture'   => 'nullable|string|max:255',
            // Gói tập (tùy chọn)
            'plan_id'           => 'nullable|exists:membership_plans,id',
            'promotion_id'      => 'nullable|exists:promotions,id',
            'start_date'        => 'nullable|required_with:plan_id|date',
            'end_date'          => 'nullable|required_with:plan_id|date|after:start_date',
            'price'             => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Lấy role_id của member
            $memberRoleId = $this->getMemberRoleId();

            // 2. Tạo user
            $user = User::create([
                'name'        => $request->name,
                'email'       => $request->email,
                'password'    => Hash::make($request->password),
                'full_name'   => $request->full_name,
                'phone'       => $request->phone,
                'gender'      => $request->gender,
                'avatar'      => $request->avatar,
                'card_number' => $request->card_number,
                'branch_id'   => $request->branch_id,
                'role_id'     => $memberRoleId,
                'state'       => 'active',
            ]);

            // 3. Tạo member profile
            MemberProfile::create([
                'user_id'           => $user->id,
                'date_of_birth'     => $request->date_of_birth,
                'join_date'         => $request->join_date ?? now()->toDateString(),
                'emergency_contact' => $request->emergency_contact,
                'health_notes'      => $request->health_notes,
                'membership_type'   => $request->membership_type,
                'profile_picture'   => $request->profile_picture,
            ]);

            // 4. Tạo gói tập (nếu có)
            if ($request->plan_id) {
                MemberSubscription::create([
                    'user_id'      => $user->id,
                    'plan_id'      => $request->plan_id,
                    'promotion_id' => $request->promotion_id,
                    'start_date'   => $request->start_date,
                    'end_date'     => $request->end_date,
                    'price'        => $request->price ?? 0,
                    'status'       => 'active',
                ]);
            }

            $user->load(['role', 'branch', 'memberProfile', 'activeSubscription.plan']);
            return response()->json($this->formatMember($user, null), 201);
        });
    }

    /*==========================================================================
    | 5. CẬP NHẬT THÔNG TIN HỘI VIÊN
    |    PUT /api/members/{id}
    ==========================================================================*/
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'              => 'sometimes|string|max:255',
            'email'             => 'sometimes|email|unique:users,email,' . $id,
            'full_name'         => 'nullable|string|max:150',
            'phone'             => 'nullable|string|max:20',
            'gender'            => 'nullable|in:male,female,other',
            'avatar'            => 'nullable|string|max:255',
            'card_number'       => 'nullable|string|max:50|unique:users,card_number,' . $id,
            'branch_id'         => 'nullable|exists:branches,id',
            'state'             => 'nullable|in:active,inactive,banned',
            // Member Profile
            'date_of_birth'     => 'nullable|date',
            'join_date'         => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:100',
            'health_notes'      => 'nullable|string',
            'membership_type'   => 'nullable|string|max:50',
            'profile_picture'   => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($request, $user) {
            // Cập nhật user
            $user->update($request->only([
                'name', 'email', 'full_name', 'phone',
                'gender', 'avatar', 'card_number', 'branch_id', 'state',
            ]));

            // Cập nhật member profile
            $profileData = $request->only([
                'date_of_birth', 'join_date', 'emergency_contact',
                'health_notes', 'membership_type', 'profile_picture',
            ]);
            if (!empty(array_filter($profileData, fn($v) => $v !== null))) {
                $user->memberProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $profileData
                );
            }

            $user->load(['role', 'branch', 'memberProfile', 'activeSubscription.plan']);
            $lastCheckinAt = Checkin::where('user_id', $user->id)->max('check_in_at');
            return response()->json($this->formatMember($user, $lastCheckinAt));
        });
    }

    /*==========================================================================
    | 6. XÓA HỘI VIÊN
    |    DELETE /api/members/{id}
    ==========================================================================*/
    public function destroy($id)
    {
        $user = User::withTrashed()->with('role')->findOrFail($id);

        if (strtolower($user->role?->role_name ?? '') !== 'member') {
            return response()->json(['message' => 'Chỉ được xóa hội viên từ API này.'], 403);
        }

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->forceDelete();
        });

        return response()->json(['message' => 'Đã xóa vĩnh viễn hội viên thành công']);
    }

    /*==========================================================================
    | 7. CẬP NHẬT TRẠNG THÁI HỘI VIÊN
    |    PATCH /api/members/{id}/status
    |
    |    Body:
    |      status *  – active | on_hold | banned
    ==========================================================================*/
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,on_hold,banned',
        ]);

        $user = User::findOrFail($id);

        // Map status giao diện → state trong DB
        $stateMap = [
            'active'  => 'active',
            'on_hold' => 'inactive',
            'banned'  => 'banned',
        ];

        $user->update(['state' => $stateMap[$request->status]]);

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công',
            'id'      => $user->id,
            'status'  => $request->status,
            'state'   => $user->state,
        ]);
    }

    /*==========================================================================
    | 8. LỊCH SỬ CHECK-IN CỦA HỘI VIÊN
    |    GET /api/members/{id}/checkins
    |
    |    Query params:
    |      per_page – số bản ghi (default 20)
    |      date     – lọc theo ngày (YYYY-MM-DD)
    ==========================================================================*/
    public function checkins(Request $request, $id)
    {
        User::findOrFail($id);

        $checkins = Checkin::with('branch')
            ->where('user_id', $id)
            ->when($request->date, function ($q) use ($request) {
                $from = Carbon::parse($request->date)->startOfDay();
                $to = $from->copy()->addDay();

                $q->where('check_in_at', '>=', $from)
                    ->where('check_in_at', '<', $to);
            })
            ->latest('check_in_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($checkins);
    }

    /*==========================================================================
    | 9. LỊCH SỬ GÓI TẬP CỦA HỘI VIÊN
    |    GET /api/members/{id}/subscriptions
    |
    |    Query params:
    |      status   – lọc theo trạng thái gói: active | expired | cancelled
    ==========================================================================*/
    public function subscriptions(Request $request, $id)
    {
        User::findOrFail($id);

        $subscriptions = MemberSubscription::with(['plan', 'promotion', 'payments'])
            ->where('user_id', $id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('start_date')
            ->get();

        return response()->json($subscriptions);
    }

    /*==========================================================================
    | HELPER PRIVATE: Format dữ liệu hội viên cho giao diện
    ==========================================================================*/
    private function formatMember(User $user, $lastCheckinAt): array
    {
        $activeSub = $user->activeSubscription;

        // Xác định status hiển thị
        if ($user->state === 'inactive') {
            $displayStatus = 'on_hold';
        } elseif ($user->state === 'banned') {
            $displayStatus = 'banned';
        } elseif ($activeSub) {
            $displayStatus = 'active';
        } else {
            $displayStatus = 'expired';
        }

        // Tính thời gian còn lại / đã hết hạn của gói tập
        $expiryLabel = null;
        if ($activeSub) {
            $endDate = Carbon::parse($activeSub->end_date);
            $diff = (int) now()->diffInDays($endDate, false);
            if ($diff > 0) {
                if ($diff > 365) {
                    $years = (int) floor($diff / 365);
                    $expiryLabel = $years . ' year' . ($years > 1 ? 's' : '') . ' left';
                } elseif ($diff > 30) {
                    $months = (int) floor($diff / 30);
                    $expiryLabel = $months . ' month' . ($months > 1 ? 's' : '') . ' left';
                } else {
                    $expiryLabel = $diff . ' day' . ($diff > 1 ? 's' : '') . ' left';
                }
            } else {
                $absDiff = abs($diff);
                $expiryLabel = $absDiff . ' day' . ($absDiff > 1 ? 's' : '') . ' ago';
            }
        }

        $lastCheckinLabel = null;
        if ($lastCheckinAt) {
            $checkinCarbon = Carbon::parse($lastCheckinAt);
            $diffMins = now()->diffInMinutes($checkinCarbon, false);
            $diffDays = (int) abs(now()->diffInDays($checkinCarbon));
            if ($diffMins >= -60 && $diffMins <= 0) {
                $lastCheckinLabel = abs((int)$diffMins) . ' min ago';
            } elseif ($diffDays === 0) {
                $lastCheckinLabel = 'Today, ' . $checkinCarbon->format('h:i A');
            } elseif ($diffDays === 1) {
                $lastCheckinLabel = 'Yesterday, ' . $checkinCarbon->format('h:i A');
            } elseif ($diffDays < 7) {
                $lastCheckinLabel = $diffDays . ' day' . ($diffDays > 1 ? 's' : '') . ' ago';
            } elseif ($diffDays < 35) {
                $weeks = (int) abs(now()->diffInWeeks($checkinCarbon));
                $lastCheckinLabel = $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
            } else {
                $lastCheckinLabel = $checkinCarbon->format('M d, Y');
            }
        }

        return [
            'id'           => $user->id,
            'name'         => $user->name,
            'full_name'    => $user->full_name ?? $user->name,
            'email'        => $user->email,
            'phone'        => $user->phone,
            'gender'       => $user->gender,
            'avatar'       => $user->avatar,
            'card_number'  => $user->card_number,   // #PC-XXXX
            'e_number'     => $user->e_number,
            'state'        => $user->state,
            'status'       => $displayStatus,       // active | expired | on_hold | banned
            'branch_id'    => $user->branch_id,
            'created_at'   => $user->created_at,

            // Gói tập hiện tại
            'package'      => $activeSub?->plan?->plan_name,
            'package_id'   => $activeSub?->plan_id,
            'subscription_id' => $activeSub?->id,
            'start_date'   => $activeSub?->start_date,
            'end_date'     => $activeSub?->end_date,
            'expiry_label' => $expiryLabel,         // "1 year left", "6 months left"…

            // Check-in
            'last_check_in_at'    => $lastCheckinAt,
            'last_check_in_label' => $lastCheckinLabel, // "Today, 08:45 AM"…

            // Member profile
            'join_date'           => $user->memberProfile?->join_date,
            'date_of_birth'       => $user->memberProfile?->date_of_birth,
            'profile_picture'     => $user->memberProfile?->profile_picture ?? $user->avatar,
            'membership_type'     => $user->memberProfile?->membership_type,
            'emergency_contact'   => $user->memberProfile?->emergency_contact,
            'health_notes'        => $user->memberProfile?->health_notes,
        ];
    }
}
