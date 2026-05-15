<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberSubscription;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * ContractController
 * ---------------------------------------------------------
 * Cung cấp toàn bộ API cho giao diện "Quản lý Hợp đồng & Gia hạn":
 *
 *  GET    /api/contracts                      → Danh sách hợp đồng (phân trang, tìm kiếm, lọc status)
 *  GET    /api/contracts/stats                → Thống kê tổng quan hợp đồng
 *  GET    /api/contracts/{id}                 → Chi tiết hợp đồng
 *  POST   /api/contracts                      → Tạo hợp đồng mới
 *  PUT    /api/contracts/{id}                 → Cập nhật hợp đồng
 *  DELETE /api/contracts/{id}                 → Xóa hợp đồng
 *  POST   /api/contracts/{id}/renew           → Gia hạn hợp đồng
 *  POST   /api/contracts/{id}/cancel          → Hủy hợp đồng
 *  GET    /api/contracts/member/{userId}      → Hợp đồng hiện tại của hội viên
 */
class ContractController extends Controller
{
    /*==========================================================================
    | HELPER: Format contract number từ ID → #CN-XXXX
    ==========================================================================*/
    private function formatContractNumber(int $id): string
    {
        return '#CN-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    }

    /*==========================================================================
    | HELPER: Format dữ liệu hợp đồng cho giao diện
    ==========================================================================*/
    private function formatContract(MemberSubscription $sub): array
    {
        $now = Carbon::now();
        $startDate = Carbon::parse($sub->start_date);
        $endDate   = Carbon::parse($sub->end_date);

        // Tính % lifecycle (tiến độ hợp đồng)
        $totalDays   = max($startDate->diffInDays($endDate), 1);
        $elapsedDays = max($startDate->diffInDays($now, false), 0);
        $lifecycle   = min(round(($elapsedDays / $totalDays) * 100), 100);

        // Số ngày còn lại / đã hết
        $daysLeft  = (int) $now->diffInDays($endDate, false);
        $isExpired = $daysLeft < 0;

        // Cảnh báo sắp hết hạn (trong 30 ngày)
        $expiryWarning = null;
        if ($sub->status === 'active' && !$isExpired && $daysLeft <= 30) {
            $expiryWarning = "Contract expires in {$daysLeft} days. Recommend immediate renewal protocol.";
        }

        return [
            'id'              => $sub->id,
            'contract_number' => $this->formatContractNumber($sub->id),
            'user_id'         => $sub->user_id,
            'member_name'     => $sub->user?->full_name ?? $sub->user?->name,
            'member_email'    => $sub->user?->email,
            'member_phone'    => $sub->user?->phone,
            'member_avatar'   => $sub->user?->avatar,
            'member_card'     => $sub->user?->card_number,
            'plan_id'         => $sub->plan_id,
            'package'         => $sub->plan?->plan_name,
            'package_label'   => $sub->plan
                ? $sub->plan->plan_name . ' (' . $sub->plan->duration_days . 'd)'
                : null,
            'promotion_id'    => $sub->promotion_id,
            'promotion_code'  => $sub->promotion?->code ?? null,
            'start_date'      => $sub->start_date?->toDateString(),
            'end_date'        => $sub->end_date?->toDateString(),
            'price'           => (float) $sub->price,
            'status'          => $sub->status,          // active | expired | cancelled
            'lifecycle'       => $lifecycle,             // 0-100 %
            'days_left'       => $isExpired ? 0 : $daysLeft,
            'days_overdue'    => $isExpired ? abs($daysLeft) : 0,
            'expiry_warning'  => $expiryWarning,
            'created_at'      => $sub->created_at,
            'updated_at'      => $sub->updated_at,
        ];
    }

    /*==========================================================================
    | 1. DANH SÁCH HỢP ĐỒNG
    |    GET /api/contracts
    |
    |    Query params:
    |      search    – tìm theo tên / email / số điện thoại / mã hợp đồng (#CN-XXXX)
    |      status    – all (default) | active | expired | cancelled | pending
    |      plan_id   – lọc theo gói tập
    |      per_page  – số bản ghi mỗi trang (default 10)
    |      page      – trang hiện tại
    ==========================================================================*/
    public function index(Request $request)
    {
        $query = MemberSubscription::with(['user', 'plan', 'promotion'])
            ->when($request->plan_id, fn($q) => $q->where('plan_id', $request->plan_id))
            ->when($request->status && $request->status !== 'all', fn($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                // Kiểm tra có phải mã #CN-XXXX không
                if (preg_match('/^#?CN-?(\d+)$/i', $s, $matches)) {
                    $q->where('id', (int) $matches[1]);
                } else {
                    $q->whereHas('user', function ($uq) use ($s) {
                        $uq->where('full_name', 'like', "%$s%")
                           ->orWhere('name', 'like', "%$s%")
                           ->orWhere('email', 'like', "%$s%")
                           ->orWhere('phone', 'like', "%$s%")
                           ->orWhere('card_number', 'like', "%$s%");
                    });
                }
            })
            ->latest();

        $perPage  = (int) ($request->per_page ?? 10);
        $paginated = $query->paginate($perPage);

        $paginated->getCollection()->transform(fn($sub) => $this->formatContract($sub));

        return response()->json($paginated);
    }

    /*==========================================================================
    | 2. THỐNG KÊ HỢP ĐỒNG
    |    GET /api/contracts/stats
    |
    |    Response:
    |      total          – tổng số hợp đồng
    |      total_active   – đang active
    |      total_expired  – đã hết hạn
    |      total_cancelled– đã hủy
    |      expiring_soon  – sắp hết hạn trong 30 ngày
    |      revenue_total  – tổng giá trị hợp đồng
    ==========================================================================*/
    public function stats(Request $request)
    {
        $base = MemberSubscription::query();

        $total          = (clone $base)->count();
        $totalActive    = (clone $base)->where('status', 'active')->count();
        $totalExpired   = (clone $base)->where('status', 'expired')->count();
        $totalCancelled = (clone $base)->where('status', 'cancelled')->count();

        $expiringSoon = (clone $base)
            ->where('status', 'active')
            ->whereBetween('end_date', [Carbon::today(), Carbon::today()->addDays(30)])
            ->count();

        $revenueTotal = (clone $base)->sum('price');

        // Hợp đồng mới trong tháng này
        $monthStart = Carbon::now()->startOfMonth();
        $nextMonthStart = $monthStart->copy()->addMonth();
        $newThisMonth = (clone $base)
            ->where('created_at', '>=', $monthStart)
            ->where('created_at', '<', $nextMonthStart)
            ->count();

        return response()->json([
            'total'           => $total,
            'total_active'    => $totalActive,
            'total_expired'   => $totalExpired,
            'total_cancelled' => $totalCancelled,
            'expiring_soon'   => $expiringSoon,
            'new_this_month'  => $newThisMonth,
            'revenue_total'   => (float) $revenueTotal,
        ]);
    }

    /*==========================================================================
    | 3. CHI TIẾT HỢP ĐỒNG
    |    GET /api/contracts/{id}
    ==========================================================================*/
    public function show($id)
    {
        // FIX: Bỏ 'payments' thừa — formatContract() không dùng dữ liệu này
        $sub = MemberSubscription::with(['user', 'plan', 'promotion'])->findOrFail($id);
        return response()->json($this->formatContract($sub));
    }

    /*==========================================================================
    | 4. TẠO HỢP ĐỒNG MỚI
    |    POST /api/contracts
    |
    |    Body (JSON):
    |      user_id         * – ID hội viên
    |      plan_id         * – ID gói tập
    |      start_date      * – ngày bắt đầu (YYYY-MM-DD)
    |      end_date        * – ngày kết thúc (YYYY-MM-DD)
    |      price             – giá hợp đồng (nếu không có, lấy từ plan)
    |      promotion_id      – ID khuyến mãi (tùy chọn)
    |      status            – active | expired | cancelled (default: active)
    |      payment_method    – cash | card | bank_transfer (default: cash)
    |      create_payment    – true/false: có tạo Payment record không (default: true)
    ==========================================================================*/
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'        => 'required|exists:users,id',
            'plan_id'        => 'required|exists:membership_plans,id',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after:start_date',
            'price'          => 'nullable|numeric|min:0',
            'promotion_id'   => 'nullable|exists:promotions,id',
            'status'         => 'nullable|in:active,expired,cancelled',
            'payment_method' => 'nullable|in:cash,card,bank_transfer',
            'create_payment' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($data, $request) {
            // Nếu không có giá, lấy từ plan
            if (empty($data['price'])) {
                $plan = MembershipPlan::find($data['plan_id']);
                $data['price'] = $plan?->price ?? 0;
            }

            $data['status'] = $data['status'] ?? 'active';

            $sub = MemberSubscription::create([
                'user_id'      => $data['user_id'],
                'plan_id'      => $data['plan_id'],
                'start_date'   => $data['start_date'],
                'end_date'     => $data['end_date'],
                'price'        => $data['price'],
                'promotion_id' => $data['promotion_id'] ?? null,
                'status'       => $data['status'],
            ]);

            // Tạo Payment record nếu create_payment !== false và hợp đồng active
            $shouldCreatePayment = $request->boolean('create_payment', true);
            if ($shouldCreatePayment && $data['status'] === 'active') {
                Payment::create([
                    'invoice_number'    => 'INV-' . strtoupper(Str::random(8)),
                    'user_id'           => $data['user_id'],
                    'branch_id'         => User::whereKey($data['user_id'])->value('branch_id'),
                    'subscription_id'   => $sub->id,
                    'amount'            => $data['price'],
                    'payment_date'      => $data['start_date'],
                    'payment_method'    => $data['payment_method'] ?? 'cash',
                    'status'            => 'paid',
                    'payment_confirmed' => true,
                    'promotion_id'      => $data['promotion_id'] ?? null,
                    'note'              => 'Tạo hợp đồng mới #CN-' . str_pad($sub->id, 4, '0', STR_PAD_LEFT),
                ]);
            }

            return response()->json($this->formatContract($sub->load(['user', 'plan', 'promotion'])), 201);
        });
    }


    /*==========================================================================
    | 5. CẬP NHẬT HỢP ĐỒNG
    |    PUT /api/contracts/{id}
    ==========================================================================*/
    public function update(Request $request, $id)
    {
        $sub = MemberSubscription::findOrFail($id);

        $data = $request->validate([
            'plan_id'      => 'sometimes|exists:membership_plans,id',
            'start_date'   => 'sometimes|date',
            'end_date'     => 'sometimes|date',
            'price'        => 'nullable|numeric|min:0',
            'promotion_id' => 'nullable|exists:promotions,id',
            'status'       => 'nullable|in:active,expired,cancelled',
        ]);

        $sub->update($data);
        return response()->json($this->formatContract($sub->load(['user', 'plan', 'promotion'])));
    }

    /*==========================================================================
    | 6. XÓA HỢP ĐỒNG
    |    DELETE /api/contracts/{id}
    ==========================================================================*/
    public function destroy($id)
    {
        MemberSubscription::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa hợp đồng thành công']);
    }

    /*==========================================================================
    | 7. GIA HẠN HỢP ĐỒNG
    |    POST /api/contracts/{id}/renew
    |
    |    Body:
    |      plan_id       * – ID gói tập mới
    |      new_start_date* – ngày bắt đầu gói mới (YYYY-MM-DD)
    |      price           – giá gói mới (nếu không có, lấy từ plan)
    |      promotion_id    – ID khuyến mãi (tùy chọn)
    |
    |    Xử lý:
    |      - Đánh dấu hợp đồng cũ là "expired"
    |      - Tạo hợp đồng mới cho hội viên đó
    ==========================================================================*/
    public function renew(Request $request, $id)
    {
        $oldSub = MemberSubscription::with(['user', 'plan'])->findOrFail($id);

        $data = $request->validate([
            'plan_id'         => 'required|exists:membership_plans,id',
            'new_start_date'  => 'required|date',
            'price'           => 'nullable|numeric|min:0',
            'promotion_id'    => 'nullable|exists:promotions,id',
            'payment_method'  => 'nullable|in:cash,card,bank_transfer',
        ]);

        return DB::transaction(function () use ($oldSub, $data) {
            // 1. Tính end_date theo duration của plan mới
            $newPlan   = MembershipPlan::findOrFail($data['plan_id']);
            $startDate = Carbon::parse($data['new_start_date']);
            $endDate   = $startDate->copy()->addDays($newPlan->duration_days);
            $price     = $data['price'] ?? $newPlan->price;

            // 2. Đánh dấu hợp đồng cũ là expired
            $oldSub->update(['status' => 'expired']);

            // 3. Tạo hợp đồng mới
            $newSub = MemberSubscription::create([
                'user_id'      => $oldSub->user_id,
                'plan_id'      => $data['plan_id'],
                'start_date'   => $startDate->toDateString(),
                'end_date'     => $endDate->toDateString(),
                'price'        => $price,
                'promotion_id' => $data['promotion_id'] ?? null,
                'status'       => 'active',
            ]);

            // FIX: Tạo bản ghi Payment tương ứng cho hợp đồng gia hạn
            Payment::create([
                'invoice_number'    => 'INV-' . strtoupper(Str::random(8)),
                'user_id'           => $oldSub->user_id,
                'branch_id'         => $oldSub->user?->branch_id,
                'subscription_id'   => $newSub->id,
                'amount'            => $price,
                'payment_date'      => $startDate->toDateString(),
                'payment_method'    => $data['payment_method'] ?? 'cash',
                'status'            => 'paid',
                'payment_confirmed' => true,
                'promotion_id'      => $data['promotion_id'] ?? null,
                'note'              => 'Gia hạn hợp đồng #CN-' . str_pad($oldSub->id, 4, '0', STR_PAD_LEFT),
            ]);

            return response()->json([
                'message'      => 'Gia hạn hợp đồng thành công',
                'old_contract' => $this->formatContract($oldSub->fresh(['user', 'plan', 'promotion'])),
                'new_contract' => $this->formatContract($newSub->load(['user', 'plan', 'promotion'])),
            ], 201);
        });
    }

    /*==========================================================================
    | 8. HỦY HỢP ĐỒNG
    |    POST /api/contracts/{id}/cancel
    |
    |    Body:
    |      reason  * – lý do hủy hợp đồng
    ==========================================================================*/
    public function cancel(Request $request, $id)
    {
        $sub = MemberSubscription::findOrFail($id);

        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        if ($sub->status === 'cancelled') {
            return response()->json(['message' => 'Hợp đồng này đã được hủy trước đó'], 422);
        }

        // FIX: Lưu lý do hủy vào DB (trước đây bị bỏ qua)
        $sub->update([
            'status'        => 'cancelled',
            'cancel_reason' => $request->reason,
        ]);

        return response()->json([
            'message'  => 'Hủy hợp đồng thành công',
            'contract' => $this->formatContract($sub->load(['user', 'plan', 'promotion'])),
        ]);
    }

    /*==========================================================================
    | 9. HỢP ĐỒNG HIỆN TẠI CỦA HỘI VIÊN
    |    GET /api/contracts/member/{userId}
    |
    |    Trả về hợp đồng active mới nhất của hội viên,
    |    kèm toàn bộ lịch sử hợp đồng.
    ==========================================================================*/
    public function memberContracts($userId)
    {
        $user = User::with([
            'memberProfile',
            'activeSubscription.plan',
            'activeSubscription.promotion',
        ])->findOrFail($userId);

        // Hợp đồng active hiện tại
        $activeSub = $user->activeSubscription;
        $current   = $activeSub ? $this->formatContract($activeSub) : null;

        // Toàn bộ lịch sử hợp đồng của hội viên
        // FIX: Thêm 'user' vào eager load để tránh N+1 query trong formatContract()
        $history = MemberSubscription::with(['user', 'plan', 'promotion'])
            ->where('user_id', $userId)
            ->orderByDesc('start_date')
            ->get()
            ->map(fn($sub) => $this->formatContract($sub));

        return response()->json([
            'member' => [
                'id'       => $user->id,
                'name'     => $user->full_name ?? $user->name,
                'email'    => $user->email,
                'phone'    => $user->phone,
                'avatar'   => $user->avatar,
                'card'     => $user->card_number,
            ],
            'current_contract' => $current,
            'history'          => $history,
        ]);
    }
}
