<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberSubscription;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\PtContract;
use App\Models\Trainer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * ServicePurchaseController
 * ---------------------------------------------------------
 * Cung cấp API cho chức năng "Mua dịch vụ" dành cho hội viên:
 *
 *  GET  /api/services/membership-plans          → Danh sách gói tập
 *  GET  /api/services/trainers                  → Danh sách HLV cá nhân (PT)
 *  GET  /api/services/my-active                 → Gói đang hoạt động của hội viên
 *  POST /api/services/purchase-plan             → Mua gói tập
 *  POST /api/services/purchase-pt               → Mua gói PT
 *  GET  /api/services/my-history                → Lịch sử mua dịch vụ
 *  GET  /api/services/my-pt-contracts           → Lịch sử hợp đồng PT
 *  POST /api/services/renew-plan/{id}           → Gia hạn gói tập
 *  POST /api/services/cancel-plan/{id}          → Hủy gói tập
 */
class ServicePurchaseController extends Controller
{
    /*==========================================================================
    | 1. DANH SÁCH GÓI TẬP
    |    GET /api/services/membership-plans
    |
    |    Trả về tất cả các gói tập đang hoạt động (status = active),
    |    kèm thông tin giá, mô tả, thời hạn.
    ==========================================================================*/
    public function listMembershipPlans(Request $request)
    {
        $plans = MembershipPlan::active()
            ->select(['id', 'plan_name', 'duration_days', 'price', 'value', 'description', 'status'])
            ->orderBy('price')
            ->get()
            ->map(fn($plan) => $this->formatPlan($plan));

        return response()->json([
            'message' => 'Danh sách gói tập',
            'data'    => $plans,
        ]);
    }

    /*==========================================================================
    | 2. DANH SÁCH HUẤN LUYỆN VIÊN CÁ NHÂN (PT)
    |    GET /api/services/trainers
    |
    |    Query params:
    |      branch_id   – lọc theo chi nhánh
    |      search      – tìm theo tên / chuyên môn
    ==========================================================================*/
    public function listTrainers(Request $request)
    {
        $trainers = Trainer::with(['user', 'branch'])
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                $q->where('specialization', 'like', "%$s%")
                  ->orWhereHas('user', fn($uq) => $uq
                      ->where('full_name', 'like', "%$s%")
                      ->orWhere('name', 'like', "%$s%")
                  );
            })
            ->get()
            ->map(fn($trainer) => $this->formatTrainer($trainer));

        return response()->json([
            'message' => 'Danh sách huấn luyện viên',
            'data'    => $trainers,
        ]);
    }

    /*==========================================================================
    | 3. GÓI ĐANG HOẠT ĐỘNG CỦA HỘI VIÊN (đã đăng nhập)
    |    GET /api/services/my-active
    ==========================================================================*/
    public function myActive(Request $request)
    {
        $user = $request->user();

        // Gói tập đang active
        $activePlan = MemberSubscription::with(['plan', 'promotion'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereDate('end_date', '>=', Carbon::today())
            ->latest()
            ->first();

        // Hợp đồng PT đang active
        $activePt = PtContract::with(['trainer.user', 'branch'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        return response()->json([
            'active_plan'       => $activePlan ? $this->formatSubscription($activePlan) : null,
            'active_pt_contract'=> $activePt   ? $this->formatPtContract($activePt)    : null,
        ]);
    }

    /*==========================================================================
    | 4. MUA GÓI TẬP
    |    POST /api/services/purchase-plan
    |
    |    Body (JSON):
    |      plan_id        * – ID gói tập
    |      promotion_id     – ID mã khuyến mãi (tùy chọn)
    |
    |    Quy trình:
    |      - Kiểm tra hội viên chưa có gói active cùng loại
    |      - Tạo MemberSubscription với status = 'pending'
    |      - Ngày bắt đầu = ngày hiện tại (mặc định)
    |      - Ngày kết thúc = start_date + duration_days của plan
    ==========================================================================*/
    public function purchasePlan(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'plan_id'      => 'required|exists:membership_plans,id',
            'promotion_id' => 'nullable|exists:promotions,id',
        ]);

        $plan = MembershipPlan::where('status', 'active')->find($data['plan_id']);
        if (!$plan) {
            return response()->json([
                'message' => 'Gói tập không tồn tại hoặc đã ngừng bán.',
            ], 422);
        }

        // Kiểm tra chống mua chồng chéo:
        // Nếu hội viên đã có gói tập active và chưa hết hạn → không được mua thêm (trừ gia hạn)
        $existing = MemberSubscription::where('user_id', $user->id)
            ->where('plan_id', $data['plan_id'])
            ->whereIn('status', ['active', 'pending'])
            ->whereDate('end_date', '>=', Carbon::today())
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Bạn đang có gói tập cùng loại còn hiệu lực. Vui lòng chờ hết hạn hoặc sử dụng chức năng Gia hạn.',
                'existing_subscription' => $this->formatSubscription($existing),
            ], 422);
        }

        return DB::transaction(function () use ($user, $data, $plan) {
            $startDate = Carbon::today();
            $endDate   = $startDate->copy()->addDays($plan->duration_days);

            // Tính giá sau khuyến mãi (nếu có)
            $finalPrice = $plan->price;
            if (!empty($data['promotion_id'])) {
                $promotion = \App\Models\Promotion::find($data['promotion_id']);
                if ($promotion && $promotion->is_valid) {
                    $finalPrice = $this->applyPromotion($plan->price, $promotion);
                } else {
                    $data['promotion_id'] = null;
                }
            }

            // Tạo subscription với trạng thái "pending" – chờ lễ tân thu tiền và kích hoạt
            $sub = MemberSubscription::create([
                'user_id'      => $user->id,
                'plan_id'      => $plan->id,
                'start_date'   => $startDate->toDateString(),
                'end_date'     => $endDate->toDateString(),
                'price'        => $finalPrice,
                'promotion_id' => $data['promotion_id'] ?? null,
                'status'       => 'pending',
            ]);

            $sub->load(['plan', 'promotion']);

            return response()->json([
                'message'      => 'Đăng ký gói tập thành công! Vui lòng chờ nhân viên lễ tân xác nhận thanh toán để kích hoạt gói.',
                'subscription' => $this->formatSubscription($sub),
                'notice'       => [
                    'status'   => 'pending',
                    'end_date' => $endDate->toDateString(),
                    'note'     => 'Gói tập sẽ được kích hoạt sau khi thanh toán hoàn tất tại quầy lễ tân.',
                ],
            ], 201);
        });
    }

    /*==========================================================================
    | 5. MUA GÓI HUẤN LUYỆN CÁ NHÂN (PT)
    |    POST /api/services/purchase-pt
    |
    |    Body (JSON):
    |      trainer_id     * – ID huấn luyện viên
    |      total_sessions * – Tổng số buổi tập muốn mua (integer >= 1)
    |      branch_id        – Chi nhánh tập (tùy chọn)
    |
    |    Quy trình:
    |      - Kiểm tra HLV tồn tại
    |      - Tính giá = trainer.pt_rate * total_sessions
    |      - Tạo PtContract với status = 'pending'
    |      - Ngày bắt đầu = ngày hiện tại
    ==========================================================================*/
    public function purchasePt(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'trainer_id'     => 'required|exists:trainers,id',
            'total_sessions' => 'required|integer|min:1|max:200',
            'branch_id'      => 'nullable|exists:branches,id',
        ]);

        $trainer = Trainer::with('user')->findOrFail($data['trainer_id']);

        // Kiểm tra hội viên đang có hợp đồng PT còn active với HLV này không
        $existingPt = PtContract::where('user_id', $user->id)
            ->where('trainer_id', $data['trainer_id'])
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($existingPt) {
            return response()->json([
                'message'          => 'Bạn đang có hợp đồng PT còn hiệu lực với huấn luyện viên này.',
                'existing_contract'=> $this->formatPtContract($existingPt->load(['trainer.user', 'branch'])),
            ], 422);
        }

        return DB::transaction(function () use ($user, $data, $trainer) {
            $totalPrice    = ($trainer->pt_rate ?? 0) * $data['total_sessions'];
            $startDate     = Carbon::today();
            // Mặc định thời hạn hợp đồng PT = tổng buổi * 7 ngày (1 buổi/tuần)
            $estimatedDays = $data['total_sessions'] * 7;
            $endDate       = $startDate->copy()->addDays($estimatedDays);

            // Tạo hợp đồng PT với trạng thái pending
            $contract = PtContract::create([
                'user_id'        => $user->id,
                'trainer_id'     => $data['trainer_id'],
                'branch_id'      => $data['branch_id'] ?? $trainer->branch_id,
                'total_sessions' => $data['total_sessions'],
                'used_sessions'  => 0,
                'start_date'     => $startDate->toDateString(),
                'end_date'       => $endDate->toDateString(),
                'price'          => $totalPrice,
                'status'         => 'pending',
            ]);

            $contract->load(['trainer.user', 'branch']);

            return response()->json([
                'message'  => 'Đăng ký gói PT thành công! Vui lòng chờ nhân viên lễ tân xác nhận thanh toán để kích hoạt hợp đồng.',
                'contract' => $this->formatPtContract($contract),
                'notice'   => [
                    'status'        => 'pending',
                    'total_price'   => $totalPrice,
                    'total_sessions'=> $data['total_sessions'],
                    'end_date'      => $endDate->toDateString(),
                    'note'          => 'Hợp đồng PT sẽ được kích hoạt sau khi thanh toán hoàn tất tại quầy lễ tân.',
                ],
            ], 201);
        });
    }

    /*==========================================================================
    | 6. LỊCH SỬ MUA GÓI TẬP CỦA HỘI VIÊN
    |    GET /api/services/my-history
    ==========================================================================*/
    public function myHistory(Request $request)
    {
        $user = $request->user();

        $history = MemberSubscription::with(['plan', 'promotion'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 10);

        $history->getCollection()->transform(fn($sub) => $this->formatSubscription($sub));

        return response()->json($history);
    }

    /*==========================================================================
    | 7. LỊCH SỬ HỢP ĐỒNG PT CỦA HỘI VIÊN
    |    GET /api/services/my-pt-contracts
    ==========================================================================*/
    public function myPtContracts(Request $request)
    {
        $user = $request->user();

        $contracts = PtContract::with(['trainer.user', 'branch'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 10);

        $contracts->getCollection()->transform(fn($c) => $this->formatPtContract($c));

        return response()->json($contracts);
    }

    /*==========================================================================
    | 8. GIA HẠN GÓI TẬP
    |    POST /api/services/renew-plan/{id}
    |
    |    Body (JSON):
    |      plan_id      * – ID gói tập muốn gia hạn (có thể khác gói cũ)
    |      promotion_id   – ID mã khuyến mãi (tùy chọn)
    |
    |    Quy trình:
    |      - Kiểm tra subscription thuộc về user đang đăng nhập
    |      - Tạo subscription mới với status = 'pending'
    |      - Hợp đồng cũ không bị xóa (lưu lịch sử)
    ==========================================================================*/
    public function renewPlan(Request $request, $id)
    {
        $user = $request->user();

        $oldSub = MemberSubscription::with(['plan'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $data = $request->validate([
            'plan_id'      => 'required|exists:membership_plans,id',
            'promotion_id' => 'nullable|exists:promotions,id',
        ]);

        $plan = MembershipPlan::where('status', 'active')->find($data['plan_id']);
        if (!$plan) {
            return response()->json(['message' => 'Gói tập không tồn tại hoặc đã ngừng bán.'], 422);
        }

        return DB::transaction(function () use ($user, $oldSub, $data, $plan) {
            $startDate  = Carbon::today();
            $endDate    = $startDate->copy()->addDays($plan->duration_days);
            $finalPrice = $plan->price;

            if (!empty($data['promotion_id'])) {
                $promotion = \App\Models\Promotion::find($data['promotion_id']);
                if ($promotion && $promotion->is_valid) {
                    $finalPrice = $this->applyPromotion($plan->price, $promotion);
                } else {
                    $data['promotion_id'] = null;
                }
            }

            // Tạo subscription mới (pending)
            $newSub = MemberSubscription::create([
                'user_id'      => $user->id,
                'plan_id'      => $plan->id,
                'start_date'   => $startDate->toDateString(),
                'end_date'     => $endDate->toDateString(),
                'price'        => $finalPrice,
                'promotion_id' => $data['promotion_id'] ?? null,
                'status'       => 'pending',
            ]);

            $newSub->load(['plan', 'promotion']);

            return response()->json([
                'message'      => 'Gia hạn gói tập thành công! Vui lòng thanh toán tại quầy lễ tân để kích hoạt.',
                'subscription' => $this->formatSubscription($newSub),
                'notice'       => [
                    'end_date' => $endDate->toDateString(),
                    'note'     => 'Gói gia hạn sẽ được kích hoạt sau khi thanh toán hoàn tất tại quầy lễ tân.',
                ],
            ], 201);
        });
    }

    /*==========================================================================
    | 9. HỦY GÓI TẬP ĐANG CHỜ THANH TOÁN
    |    POST /api/services/cancel-plan/{id}
    |
    |    Body (JSON):
    |      reason * – Lý do hủy
    |
    |    Hội viên chỉ có thể hủy các gói có trạng thái "pending"
    ==========================================================================*/
    public function cancelPlan(Request $request, $id)
    {
        $user = $request->user();

        $sub = MemberSubscription::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        if ($sub->status !== 'pending') {
            return response()->json([
                'message' => 'Chỉ có thể hủy các gói đang ở trạng thái "Chờ thanh toán". Liên hệ nhân viên để hủy gói đang active.',
            ], 422);
        }

        $sub->update([
            'status'        => 'cancelled',
            'cancel_reason' => $request->reason,
        ]);

        return response()->json([
            'message'      => 'Đã hủy đăng ký gói tập thành công.',
            'subscription' => $this->formatSubscription($sub->load(['plan', 'promotion'])),
        ]);
    }

    /*==========================================================================
    | HELPERS: Format dữ liệu trả về
    ==========================================================================*/

    private function formatPlan(MembershipPlan $plan): array
    {
        return [
            'id'           => $plan->id,
            'plan_name'    => $plan->plan_name,
            'duration_days'=> $plan->duration_days,
            'duration_label'=> $this->durationLabel($plan->duration_days),
            'price'        => (float) $plan->price,
            'value'        => $plan->value,
            'description'  => $plan->description,
            'status'       => $plan->status,
        ];
    }

    private function formatTrainer(Trainer $trainer): array
    {
        return [
            'id'             => $trainer->id,
            'name'           => $trainer->user?->full_name ?? $trainer->user?->name,
            'email'          => $trainer->user?->email,
            'phone'          => $trainer->user?->phone,
            'avatar'         => $trainer->user?->avatar,
            'branch_id'      => $trainer->branch_id,
            'branch_name'    => $trainer->branch?->name,
            'specialization' => $trainer->specialization,
            'experience'     => $trainer->experience,
            'description'    => $trainer->description,
            'pt_rate'        => (float) ($trainer->pt_rate ?? 0),
            'max_sessions'   => $trainer->max_sessions,
        ];
    }

    private function formatSubscription(MemberSubscription $sub): array
    {
        $now     = Carbon::now();
        $endDate = Carbon::parse($sub->end_date);
        $daysLeft = (int) $now->diffInDays($endDate, false);

        return [
            'id'             => $sub->id,
            'plan_id'        => $sub->plan_id,
            'plan_name'      => $sub->plan?->plan_name,
            'duration_days'  => $sub->plan?->duration_days,
            'duration_label' => $sub->plan ? $this->durationLabel($sub->plan->duration_days) : null,
            'promotion_id'   => $sub->promotion_id,
            'promotion_code' => $sub->promotion?->code ?? null,
            'start_date'     => $sub->start_date?->toDateString(),
            'end_date'       => $sub->end_date?->toDateString(),
            'price'          => (float) $sub->price,
            'status'         => $sub->status,
            'status_label'   => $this->subscriptionStatusLabel($sub->status),
            'days_left'      => $daysLeft > 0 ? $daysLeft : 0,
            'cancel_reason'  => $sub->cancel_reason,
            'created_at'     => $sub->created_at,
        ];
    }

    private function formatPtContract(PtContract $contract): array
    {
        $remaining = max(0, ($contract->total_sessions ?? 0) - ($contract->used_sessions ?? 0));

        return [
            'id'               => $contract->id,
            'trainer_id'       => $contract->trainer_id,
            'trainer_name'     => $contract->trainer?->user?->full_name ?? $contract->trainer?->user?->name,
            'trainer_avatar'   => $contract->trainer?->user?->avatar,
            'specialization'   => $contract->trainer?->specialization,
            'branch_id'        => $contract->branch_id,
            'branch_name'      => $contract->branch?->name,
            'total_sessions'   => $contract->total_sessions,
            'used_sessions'    => $contract->used_sessions,
            'remaining_sessions'=> $remaining,
            'start_date'       => $contract->start_date?->toDateString(),
            'end_date'         => $contract->end_date?->toDateString(),
            'price'            => (float) $contract->price,
            'status'           => $contract->status,
            'status_label'     => $this->ptStatusLabel($contract->status),
            'created_at'       => $contract->created_at,
        ];
    }

    private function durationLabel(int $days): string
    {
        if ($days >= 365 && $days % 365 === 0) {
            $years = $days / 365;
            return $years . ' năm';
        }
        if ($days >= 30) {
            $months = round($days / 30);
            return $months . ' tháng';
        }
        return $days . ' ngày';
    }

    private function subscriptionStatusLabel(string $status): string
    {
        return match ($status) {
            'active'    => 'Đang hoạt động',
            'pending'   => 'Chờ thanh toán',
            'expired'   => 'Đã hết hạn',
            'cancelled' => 'Đã hủy',
            default     => $status,
        };
    }

    private function ptStatusLabel(string $status): string
    {
        return match ($status) {
            'active'    => 'Đang hoạt động',
            'pending'   => 'Chờ thanh toán',
            'completed' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
            default     => $status,
        };
    }

    /**
     * Tính giá sau khi áp dụng khuyến mãi.
     * Promotion model dùng cột 'discount' (phần trăm giảm giá, ví dụ 20 = giảm 20%)
     */
    private function applyPromotion(float $originalPrice, $promotion): float
    {
        // discount là phần trăm (0 – 100)
        $discountPercent = (float) ($promotion->discount ?? 0);
        $discounted      = $originalPrice * (1 - $discountPercent / 100);
        return max(0, round($discounted, 2));
    }
}
