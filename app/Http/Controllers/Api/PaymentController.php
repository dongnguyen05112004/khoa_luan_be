<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\MemberSubscription;
use App\Models\Promotion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
// DB facade không dùng trong file này (đã remove để tránh warning)
use Illuminate\Support\Str;

/**
 * PaymentController
 * ─────────────────────────────────────────────────────────────────────
 * Cung cấp toàn bộ API cho giao diện Thu Tiền & Xử Lý Thanh Toán:
 *
 *  GET    /api/payments                       → Danh sách giao dịch (phân trang, lọc)
 *  GET    /api/payments/stats                 → Thống kê tổng hợp (doanh thu hôm nay, ...)
 *  GET    /api/payments/summary               → Tóm tắt doanh thu theo ngày / tháng
 *  GET    /api/payments/{id}                  → Chi tiết 1 giao dịch
 *  POST   /api/payments                       → Tạo mới giao dịch
 *  PUT    /api/payments/{id}                  → Cập nhật giao dịch
 *  PATCH  /api/payments/{id}/confirm          → Xác nhận thanh toán (pending → paid)
 *  PATCH  /api/payments/{id}/refund           → Hoàn tiền (paid → refunded)
 *  DELETE /api/payments/{id}                  → Xóa giao dịch
 *  POST   /api/payments/validate-promo        → Kiểm tra mã khuyến mãi
 *  GET    /api/payments/invoice/{id}          → Dữ liệu hóa đơn để xuất PDF
 * ─────────────────────────────────────────────────────────────────────
 */
class PaymentController extends Controller
{
    /*==========================================================================
    | HELPER: Format payment cho giao diện
    ==========================================================================*/
    private function formatPayment(Payment $p): array
    {
        $user = $p->user;
        $plan = $p->subscription?->plan;

        // Ánh xạ phương thức thanh toán
        $methodMap = [
            'cash'          => ['label' => 'Tiền mặt',     'icon' => 'fas fa-money-bill-wave'],
            'card'          => ['label' => 'Thẻ tín dụng', 'icon' => 'fas fa-credit-card'],
            'bank_transfer' => ['label' => 'Chuyển khoản', 'icon' => 'fas fa-university'],
            'vnpay'         => ['label' => 'VNPay',        'icon' => 'fas fa-mobile-alt'],
            'vietqr'        => ['label' => 'VietQR',       'icon' => 'fas fa-qrcode'],
        ];
        $method = $methodMap[$p->payment_method] ?? ['label' => $p->payment_method ?? '—', 'icon' => 'fas fa-money-bill'];

        // Ánh xạ status
        $statusMap = [
            'paid'     => ['label' => 'Completed', 'class' => 'st-completed'],
            'pending'  => ['label' => 'Pending',   'class' => 'st-pending'],
            'refunded' => ['label' => 'Refunded',  'class' => 'st-refunded'],
        ];
        $status = $statusMap[$p->status] ?? ['label' => $p->status, 'class' => 'st-pending'];

        // Tên gói tập
        $packageLabel = $plan?->plan_name;

        // Nếu là PT contract (payable_type là PtContract)
        if ($p->payable_type === \App\Models\PtContract::class) {
            $contract = \App\Models\PtContract::with('trainer.user')->find($p->payable_id);
            if ($contract) {
                $trainerName = $contract->trainer?->user?->full_name ?? $contract->trainer?->user?->name ?? 'HLV';
                $packageLabel = "Thuê PT: " . $trainerName . " (" . $contract->total_sessions . " buổi)";
            }
            $pkgClass = 'pkg-pt';
        } else {
            $packageLabel = $packageLabel ?? 'Không xác định';
            $pkgClassMap  = [
                'Elite'   => 'pkg-elite',
                'Monthly' => 'pkg-monthly',
                'PT'      => 'pkg-pt',
            ];
            $pkgClass = 'pkg-monthly';
            foreach ($pkgClassMap as $keyword => $cls) {
                if (stripos($packageLabel, $keyword) !== false) {
                    $pkgClass = $cls;
                    break;
                }
            }
        }

        return [
            'id'             => $p->id,
            'txId'           => '#TRX-' . str_pad($p->id, 4, '0', STR_PAD_LEFT),
            'invoice_number' => $p->invoice_number,
            'date'           => $p->payment_date
                                    ? Carbon::parse($p->payment_date)->format('M d, Y')
                                    : Carbon::parse($p->created_at)->format('M d, Y'),
            'payment_date'   => $p->payment_date?->toDateString(),
            'branch_id'      => $p->branch_id,
            'branch_name'    => $p->branch?->branch_name,
            // Hội viên
            'user_id'        => $p->user_id,
            'memberName'     => $user?->full_name ?? $user?->name ?? 'Không rõ',
            'memberEmail'    => $user?->email,
            'memberPhone'    => $user?->phone,
            'avatarBg'       => substr(md5($user?->email ?? $p->user_id), 0, 6),
            // Gói tập
            'subscription_id'=> $p->subscription_id,
            'package'        => $packageLabel,
            'pkgClass'       => $pkgClass,
            // Tiền tệ
            'amount'         => (float) $p->amount,
            // Phương thức
            'method'         => $method['label'],
            'methodIcon'     => $method['icon'],
            'payment_method' => $p->payment_method,
            // Trạng thái
            'status'         => $status['label'],
            'statusClass'    => $status['class'],
            'status_raw'     => $p->status,
            // Khuyến mãi
            'promotion_id'   => $p->promotion_id,
            'promotion_code' => $p->promotion?->code,
            // Ghi chú
            'note'           => $p->note,
            'payment_confirmed' => (bool) $p->payment_confirmed,
            'created_at'     => $p->created_at,
        ];
    }

    /*==========================================================================
    | 1. DANH SÁCH GIAO DỊCH
    |    GET /api/payments
    |
    |    Query params:
    |      user_id    – lọc theo hội viên
    |      status     – all | pending | paid | refunded
    |      method     – cash | card | bank_transfer
    |      date_from  – YYYY-MM-DD
    |      date_to    – YYYY-MM-DD
    |      search     – tìm theo tên / email / số hóa đơn
    |      per_page   – số bản ghi (default 20)
    |      page       – trang hiện tại
    ==========================================================================*/
    public function index(Request $request)
    {
        $payments = Payment::with(['user', 'branch', 'subscription.plan', 'promotion'])
            // Quy tắc: Chỉ hiển thị các giao dịch đã được khách xác nhận thanh toán (nếu đang pending) 
            // để tránh rác cho bộ phận kế toán/lễ tân.
            ->where(function($q) {
                $q->where('status', '!=', 'pending')
                  ->orWhere('payment_confirmed', true);
            })
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when(
                $request->status && $request->status !== 'all',
                fn($q) => $q->where('status', $request->status)
            )
            ->when($request->input('method'), fn($q) => $q->where('payment_method', $request->input('method')))
            ->when($request->has('payment_confirmed'), fn($q) => $q->where('payment_confirmed', $request->input('payment_confirmed')))
            ->when($request->date_from, fn($q) => $q->where('payment_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->where('payment_date', '<', Carbon::parse($request->date_to)->addDay()->toDateString()))
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($inner) use ($s) {
                    $inner->where('invoice_number', 'like', "%$s%")
                          ->orWhereHas('user', function ($uq) use ($s) {
                              $uq->where('full_name', 'like', "%$s%")
                                 ->orWhere('name',     'like', "%$s%")
                                 ->orWhere('email',    'like', "%$s%")
                                 ->orWhere('phone',    'like', "%$s%");
                          });
                });
            })
            ->latest('id')
            ->paginate($request->per_page ?? 20);

        $payments->getCollection()->transform(fn($p) => $this->formatPayment($p));

        return response()->json($payments);
    }

    /*==========================================================================
    | 2. THỐNG KÊ TỔNG HỢP
    |    GET /api/payments/stats
    |
    |    Response:
    |      today_revenue      – doanh thu hôm nay (chỉ paid)
    |      today_revenue_change – % thay đổi so với hôm qua
    |      completed_count    – số giao dịch hoàn thành hôm nay
    |      avg_ticket         – giá trị trung bình mỗi giao dịch hôm nay
    |      refunded_count     – số giao dịch hoàn tiền hôm nay
    |      pending_count      – số giao dịch đang chờ
    |      month_revenue      – doanh thu tháng này
    |      payment_methods    – thống kê theo phương thức (tỷ lệ %)
    ==========================================================================*/
    public function stats(Request $request)
    {
        $today          = Carbon::today();
        $tomorrow       = $today->copy()->addDay();
        $yesterday      = Carbon::yesterday();
        $monthStart     = $today->copy()->startOfMonth();
        $nextMonthStart = $monthStart->copy()->addMonth();

        $paidBase = Payment::where('status', 'paid')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id));

        // Doanh thu hôm nay
        $todayRevenue = (clone $paidBase)
            ->where('payment_date', '>=', $today->toDateString())
            ->where('payment_date', '<', $tomorrow->toDateString())
            ->sum('amount');

        // Doanh thu hôm qua (để tính % thay đổi)
        $yesterdayRevenue = (clone $paidBase)
            ->where('payment_date', '>=', $yesterday->toDateString())
            ->where('payment_date', '<', $today->toDateString())
            ->sum('amount');

        $revenueChange = $yesterdayRevenue > 0
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1)
            : 0;

        // Số giao dịch hôm nay
        $completedCount = (clone $paidBase)
            ->where('payment_date', '>=', $today->toDateString())
            ->where('payment_date', '<', $tomorrow->toDateString())
            ->count();

        $refundedCount = Payment::where('status', 'refunded')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->where('payment_date', '>=', $today->toDateString())
            ->where('payment_date', '<', $tomorrow->toDateString())
            ->count();

        $pendingCount = Payment::where('status', 'pending')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->count();

        // Giá trị trung bình mỗi giao dịch
        $avgTicket = $completedCount > 0
            ? round($todayRevenue / $completedCount)
            : 0;

        // Doanh thu tháng này
        $monthRevenue = (clone $paidBase)
            ->where('payment_date', '>=', $monthStart->toDateString())
            ->where('payment_date', '<', $nextMonthStart->toDateString())
            ->sum('amount');

        // Thống kê theo phương thức thanh toán (tháng này)
        $methodStats = (clone $paidBase)
            ->where('payment_date', '>=', $monthStart->toDateString())
            ->where('payment_date', '<', $nextMonthStart->toDateString())
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        $totalTxn = $methodStats->sum('count');
        $paymentMethods = $methodStats->map(function ($m) use ($totalTxn) {
            $labelMap = [
                'cash'          => 'Tiền mặt',
                'card'          => 'Thẻ TD',
                'bank_transfer' => 'Chuyển khoản',
            ];
            $colorMap = [
                'cash'          => '#86efac',
                'card'          => '#22c55e',
                'bank_transfer' => '#16a34a',
            ];
            return [
                'key'   => $m->payment_method,
                'label' => $labelMap[$m->payment_method] ?? $m->payment_method,
                'count' => (int) $m->count,
                'total' => (float) $m->total,
                'pct'   => $totalTxn > 0 ? round(($m->count / $totalTxn) * 100) : 0,
                'color' => $colorMap[$m->payment_method] ?? '#94a3b8',
            ];
        })->values();

        return response()->json([
            'today_revenue'         => (float) $todayRevenue,
            'today_revenue_change'  => $revenueChange,
            'completed_count'       => (int) $completedCount,
            'avg_ticket'            => (int) $avgTicket,
            'refunded_count'        => (int) $refundedCount,
            'pending_count'         => (int) $pendingCount,
            'month_revenue'         => (float) $monthRevenue,
            'payment_methods'       => $paymentMethods,
        ]);
    }

    /*==========================================================================
    | 3. TÓM TẮT DOANH THU
    |    GET /api/payments/summary
    |
    |    Query params:
    |      period – day | month (default: day)
    |      date   – YYYY-MM-DD (default: hôm nay)
    |      year   – YYYY (cho period=month)
    ==========================================================================*/
    public function summary(Request $request)
    {
        $period = $request->period ?? 'day';

        if ($period === 'month') {
            $year = $request->year ?? Carbon::today()->year;
            $data = Payment::where('status', 'paid')
                ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
                ->where('payment_date', '>=', Carbon::create((int) $year, 1, 1)->toDateString())
                ->where('payment_date', '<', Carbon::create((int) $year + 1, 1, 1)->toDateString())
                ->selectRaw('MONTH(payment_date) as month, SUM(amount) as total, COUNT(*) as count')
                ->groupByRaw('MONTH(payment_date)')
                ->orderBy('month')
                ->get()
                ->map(fn($r) => [
                    'label' => 'T' . $r->month,
                    'total' => (float) $r->total,
                    'count' => (int)   $r->count,
                ]);
        } else {
            $date = $request->date ?? Carbon::today()->toDateString();
            $from = Carbon::parse($date)->toDateString();
            $to   = Carbon::parse($date)->addDay()->toDateString();
            $data = Payment::where('status', 'paid')
                ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
                ->where('payment_date', '>=', $from)
                ->where('payment_date', '<', $to)
                ->selectRaw('HOUR(created_at) as hour, SUM(amount) as total, COUNT(*) as count')
                ->groupByRaw('HOUR(created_at)')
                ->orderBy('hour')
                ->get()
                ->map(fn($r) => [
                    'label' => str_pad($r->hour, 2, '0', STR_PAD_LEFT) . ':00',
                    'total' => (float) $r->total,
                    'count' => (int)   $r->count,
                ]);
        }

        return response()->json(['period' => $period, 'data' => $data]);
    }

    /*==========================================================================
    | 4. CHI TIẾT GIAO DỊCH
    |    GET /api/payments/{id}
    ==========================================================================*/
    public function show($id)
    {
        $payment = Payment::with(['user', 'branch', 'subscription.plan', 'promotion'])->findOrFail($id);
        return response()->json($this->formatPayment($payment));
    }

    /*==========================================================================
    | 5. TẠO GIAO DỊCH MỚI
    |    POST /api/payments
    |
    |    Body:
    |      user_id         * – ID hội viên
    |      subscription_id   – ID đăng ký gói tập (nếu có)
    |      amount          * – Số tiền
    |      payment_method  * – cash | card | bank_transfer
    |      payment_date      – YYYY-MM-DD (default: hôm nay)
    |      status            – pending | paid | refunded (default: paid)
    |      promotion_id      – ID khuyến mãi (tùy chọn)
    |      note              – Ghi chú
    ==========================================================================*/
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'          => 'required|exists:users,id',
            'branch_id'        => 'nullable|exists:branches,id',
            'subscription_id'  => 'nullable|exists:member_subscriptions,id',
            'amount'           => 'required|numeric|min:0',
            'payment_method'   => 'required|in:cash,card,bank_transfer',
            'payment_date'     => 'nullable|date',
            'status'           => 'nullable|in:pending,paid,refunded',
            'payment_confirmed'=> 'nullable|boolean',
            'promotion_id'     => 'nullable|exists:promotions,id',
            'note'             => 'nullable|string|max:500',
        ]);

        $data['invoice_number']    = 'INV-' . strtoupper(Str::random(8));
        $data['payment_date']      = $data['payment_date'] ?? Carbon::today()->toDateString();
        $data['branch_id']         = $data['branch_id'] ?? User::whereKey($data['user_id'])->value('branch_id');
        // BUG FIX: phải set status trước rồi mới dùng nó để tính payment_confirmed
        $data['status']            = $data['status'] ?? 'paid';
        $data['payment_confirmed'] = $data['payment_confirmed'] ?? ($data['status'] === 'paid');

        $payment = Payment::create($data);
        $payment->load(['user', 'branch', 'subscription.plan', 'promotion']);

        return response()->json($this->formatPayment($payment), 201);
    }

    /*==========================================================================
    | 6. CẬP NHẬT GIAO DỊCH
    |    PUT /api/payments/{id}
    ==========================================================================*/
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $data = $request->validate([
            'status'            => 'sometimes|in:pending,paid,refunded',
            'payment_method'    => 'nullable|in:cash,card,bank_transfer',
            'payment_date'      => 'nullable|date',
            'payment_confirmed' => 'nullable|boolean',
            'promotion_id'      => 'nullable|exists:promotions,id',
            'note'              => 'nullable|string|max:500',
        ]);
        $payment->update($data);
        return response()->json($this->formatPayment($payment->load(['user', 'branch', 'subscription.plan', 'promotion'])));
    }

    /*==========================================================================
    | 7. XÁC NHẬN THANH TOÁN
    |    PATCH /api/payments/{id}/confirm
    |
    |    Chuyển trạng thái pending → paid
    |    Body (tuỳ chọn):
    |      payment_method – cash | card | bank_transfer
    |      note           – Ghi chú xác nhận
    ==========================================================================*/
    public function confirm(Request $request, $id)
    {
        $payment = Payment::with(['user', 'subscription.plan', 'promotion'])->findOrFail($id);

        if ($payment->status === 'paid') {
            return response()->json(['message' => 'Giao dịch đã được xác nhận trước đó'], 422);
        }
        if ($payment->status === 'refunded') {
            return response()->json(['message' => 'Không thể xác nhận giao dịch đã hoàn tiền'], 422);
        }

        $request->validate([
            'payment_method' => 'nullable|in:cash,card,bank_transfer',
            'note'           => 'nullable|string|max:500',
        ]);

        $payment->update([
            'status'            => 'paid',
            'payment_confirmed' => true,
            // BUG FIX: payment_date là Carbon object (do $casts), dùng ->toDateString() thay vì so sánh trực tiếp
            'payment_date'      => $payment->payment_date?->toDateString() ?? Carbon::today()->toDateString(),
            'payment_method'    => $request->payment_method ?? $payment->payment_method ?? 'cash',
            'note'              => $request->note ?? $payment->note,
        ]);

        $this->activateRelatedService($payment);

        return response()->json([
            'message' => 'Xác nhận thanh toán thành công',
            'payment' => $this->formatPayment($payment->fresh(['user', 'branch', 'subscription.plan', 'promotion'])),
        ]);
    }

    /*==========================================================================
    | 8. HOÀN TIỀN
    |    PATCH /api/payments/{id}/refund
    |
    |    Chuyển trạng thái paid → refunded
    |    Body:
    |      reason * – Lý do hoàn tiền (bắt buộc)
    ==========================================================================*/
    public function refund(Request $request, $id)
    {
        $payment = Payment::with(['user', 'subscription.plan'])->findOrFail($id);

        if ($payment->status !== 'paid') {
            return response()->json(['message' => 'Chỉ có thể hoàn tiền giao dịch đã thanh toán'], 422);
        }

        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $payment->update([
            'status'            => 'refunded',
            'payment_confirmed' => false,
            'note'              => ($payment->note ? $payment->note . ' | ' : '') . 'Hoàn tiền: ' . $request->reason,
        ]);

        return response()->json([
            'message' => 'Hoàn tiền thành công',
            'payment' => $this->formatPayment($payment->fresh(['user', 'branch', 'subscription.plan', 'promotion'])),
        ]);
    }

    /*==========================================================================
    | 9. XÓA GIAO DỊCH
    |    DELETE /api/payments/{id}
    ==========================================================================*/
    public function destroy($id)
    {
        Payment::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa giao dịch']);
    }

    /*==========================================================================
    | 10. KIỂM TRA MÃ KHUYẾN MÃI
    |     POST /api/payments/validate-promo
    |
    |     Body:
    |       code   * – Mã khuyến mãi
    |       amount * – Số tiền gốc (để tính số tiền giảm)
    |
    |     Response:
    |       valid          – true | false
    |       promo_id       – ID khuyến mãi (nếu valid)
    |       code           – Mã code
    |       title          – Tên chương trình
    |       discount       – % giảm giá
    |       discount_amount– Số tiền được giảm
    |       message        – Thông báo
    ==========================================================================*/
    public function validatePromo(Request $request)
    {
        $request->validate([
            'code'   => 'required|string',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $today     = Carbon::today();
        $promotion = Promotion::where('code', strtoupper(trim($request->code)))
            ->where('is_active', true)
            ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $today))
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today))
            ->where(fn($q) => $q->whereNull('usage_limit')->orWhereColumn('current_usage', '<', 'usage_limit'))
            ->first();

        if (!$promotion) {
            return response()->json([
                'valid'   => false,
                'message' => 'Mã không hợp lệ, đã hết hạn hoặc đã đạt giới hạn sử dụng.',
            ]);
        }

        $amount         = (float) ($request->amount ?? 0);
        $discountAmount = round($amount * ($promotion->discount / 100));

        return response()->json([
            'valid'           => true,
            'promo_id'        => $promotion->id,
            'code'            => $promotion->code,
            'title'           => $promotion->title,
            'discount'        => (float) $promotion->discount,
            'discount_amount' => $discountAmount,
            'final_amount'    => max(0, $amount - $discountAmount),
            'message'         => "✓ Áp dụng mã {$promotion->code} – giảm {$promotion->discount}%",
        ]);
    }

    /*==========================================================================
    | 11. DỮ LIỆU HÓA ĐƠN
    |     GET /api/payments/invoice/{id}
    |
    |     Trả về toàn bộ thông tin để render hóa đơn PDF / in
    ==========================================================================*/
    public function invoice($id)
    {
        $payment = Payment::with([
            'user.memberProfile',
            'subscription.plan',
            'promotion',
        ])->findOrFail($id);

        $user = $payment->user;
        $sub  = $payment->subscription;
        $plan = $sub?->plan;

        return response()->json([
            'invoice_number' => $payment->invoice_number ?? '#TRX-' . str_pad($payment->id, 4, '0', STR_PAD_LEFT),
            'payment_date'   => $payment->payment_date?->format('d/m/Y') ?? Carbon::parse($payment->created_at)->format('d/m/Y'),
            'status'         => $payment->status,
            // Hội viên
            'member' => [
                'id'       => $user?->id,
                'name'     => $user?->full_name ?? $user?->name,
                'email'    => $user?->email,
                'phone'    => $user?->phone,
                'address'  => $user?->memberProfile?->address,
            ],
            // Dịch vụ
            'service' => [
                'name'       => $plan?->plan_name ?? 'Dịch vụ',
                'start_date' => $sub?->start_date?->format('d/m/Y'),
                'end_date'   => $sub?->end_date?->format('d/m/Y'),
                'duration'   => $plan?->duration_days ? $plan->duration_days . ' ngày' : null,
            ],
            // Tiền
            // BUG FIX: tính discount_amount và total đúng (total phải TRỪ discount)
            'subtotal'        => (float) $payment->amount,
            'discount'        => $payment->promotion ? (int) round($payment->amount * ($payment->promotion->discount / 100)) : 0,
            'discount_pct'    => $payment->promotion?->discount ?? 0,
            'promo_code'      => $payment->promotion?->code,
            'total'           => $payment->promotion
                                    ? max(0, (float) $payment->amount - round($payment->amount * ($payment->promotion->discount / 100)))
                                    : (float) $payment->amount,
            'payment_method'  => $payment->payment_method,
            'note'            => $payment->note,
            // Gym info
            'gym' => [
                'name'    => 'KP FITNESS GYM',
                'address' => 'Hồ Chí Minh - Quận 1',
                'phone'   => '0123 456 789',
                'bank'    => 'Techcombank',
                'account' => '19039637328012',
            ],
        ]);
    }

    /*==========================================================================
    | HELPER: Xác thực chữ ký VNPay
    ==========================================================================*/
    private function verifyVnpayHash(Request $request): bool
    {
        $vnp_SecureHash = $request->vnp_SecureHash;
        $inputData = [];
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);
        ksort($inputData);
        
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $vnp_HashSecret = config('vnpay.hash_secret');
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        return $secureHash === $vnp_SecureHash;
    }

    /*==========================================================================
    | HELPER: Kích hoạt dịch vụ liên quan
    ==========================================================================*/
    private function activateRelatedService(Payment $payment)
    {
        // 1. Kích hoạt Subscription (Gói tập)
        if ($payment->subscription_id) {
            $sub = MemberSubscription::find($payment->subscription_id);
            if ($sub && $sub->status !== 'active') {
                $sub->update(['status' => 'active']);
            }
        }

        // 2. Kích hoạt dịch vụ khác (PT Contract, ...) qua Polymorphic
        if ($payment->payable_type && $payment->payable_id) {
            $service = $payment->payable;
            if ($service && isset($service->status) && $service->status !== 'active') {
                $service->update(['status' => 'active']);
            }
        }
    }

    /*==========================================================================
    | 12. TẠO URL THANH TOÁN VNPAY
    |     POST /api/payment/create
    |
    |     Body:
    |       order_id * – ID của Payment đang chờ (status=pending)
    ==========================================================================*/
    public function vnpayCreate(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:payments,id',
        ]);

        $payment = Payment::findOrFail($request->order_id);

        if ($payment->status === 'paid') {
            return response()->json(['message' => 'Đơn hàng này đã được thanh toán'], 400);
        }

        $vnp_TmnCode = config('vnpay.tmn_code');
        $vnp_HashSecret = config('vnpay.hash_secret');
        $vnp_Url = config('vnpay.url');
        $vnp_Returnurl = config('vnpay.return_url');

        if (empty($vnp_TmnCode) || empty($vnp_HashSecret)) {
            return response()->json(['message' => 'Cấu hình VNPay chưa hoàn tất (Thiếu TMN_CODE hoặc HASH_SECRET)'], 500);
        }
        
        $vnp_TxnRef = $payment->id . '_' . time(); 
        $vnp_OrderInfo = 'Thanh toan don hang ' . $payment->invoice_number;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = round($payment->amount) * 100; // Đảm bảo là số nguyên
        $vnp_Locale = 'vn';
        $vnp_IpAddr = $request->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        );

        if ($request->bank_code) {
            $inputData['vnp_BankCode'] = $request->bank_code;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return response()->json([
            'code' => '00',
            'message' => 'success',
            'data' => $vnp_Url
        ]);
    }

    /*==========================================================================
    | 13. XỬ LÝ CALLBACK TỪ VNPAY (Frontend redirect return)
    |     GET /api/payment/callback
    ==========================================================================*/
    public function vnpayCallback(Request $request)
    {
        if ($this->verifyVnpayHash($request)) {
            // Lấy id gốc (bỏ phần timestamp)
            $txnRefParts = explode('_', $request->vnp_TxnRef);
            $paymentId = $txnRefParts[0];

            if ($request->vnp_ResponseCode == '00') {
                return response()->json(['message' => 'Giao dịch thành công', 'payment_id' => $paymentId, 'status' => 'success']);
            } else {
                return response()->json(['message' => 'Giao dịch không thành công hoặc bị hủy', 'payment_id' => $paymentId, 'status' => 'error'], 400);
            }
        } else {
            return response()->json(['message' => 'Chữ ký không hợp lệ', 'status' => 'invalid_signature'], 400);
        }
    }

    /*==========================================================================
    | 13b. XỬ LÝ REDIRECT VỀ FRONTEND
    |      GET /api/payment/vnpay-return
    ==========================================================================*/
    public function vnpayReturn(Request $request)
    {
        // Thường redirect về 1 trang của Frontend (ví dụ: /payment/result)
        $status = ($request->vnp_ResponseCode == '00') ? 'success' : 'error';
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/payment/result';
        
        $params = http_build_query([
            'status'     => $status,
            'payment_id' => explode('_', $request->vnp_TxnRef)[0],
            'message'    => $request->vnp_ResponseCode == '00' ? 'Thanh toán thành công' : 'Thanh toán thất bại'
        ]);

        return redirect($frontendUrl . '?' . $params);
    }

    /*==========================================================================
    | 14. IPN WEBHOOK VNPAY GỌI ĐẾN (Cập nhật database)
    |     POST /api/payment/ipn (hoặc GET)
    ==========================================================================*/
    public function vnpayIpn(Request $request)
    {
        if ($this->verifyVnpayHash($request)) {
            $txnRefParts = explode('_', $request->vnp_TxnRef);
            $paymentId = $txnRefParts[0] ?? null;
            $payment = Payment::find($paymentId);

            if ($payment != null) {
                // Kiểm tra số tiền
                $amountFromVnPay = $request->vnp_Amount / 100;
                if ($payment->amount == $amountFromVnPay) {
                    if ($payment->status !== 'paid') {
                        if ($request->vnp_ResponseCode == '00' && $request->vnp_TransactionStatus == '00') {
                            $payment->status = 'paid';
                            $payment->payment_confirmed = true;
                            $payment->payment_date = Carbon::now();
                            $payment->payment_method = 'bank_transfer';
                            $payment->note = ($payment->note ? $payment->note . ' | ' : '') . 'Thanh toán qua VNPay, GD: ' . $request->vnp_TransactionNo;
                            $payment->save();
                            
                            $this->activateRelatedService($payment);

                            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
                        } else {
                            // Giao dịch không thành công
                            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success but transaction failed']);
                        }
                    } else {
                        return response()->json(['RspCode' => '02', 'Message' => 'Order already confirmed']);
                    }
                } else {
                    return response()->json(['RspCode' => '04', 'Message' => 'invalid amount']);
                }
            } else {
                return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
            }
        } else {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }
    }
}
