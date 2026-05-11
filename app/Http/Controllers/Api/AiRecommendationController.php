<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiRecommendation;
use App\Services\GroqService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AiRecommendationController extends Controller
{
    // =========================================================================
    // CRUD cơ bản
    // =========================================================================

    /** GET /api/ai-recommendations */
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            AiRecommendation::with('user')
                ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
                ->when($request->type,    fn($q) => $q->where('recommendation_type', $request->type))
                ->latest()
                ->paginate($request->per_page ?? 20)
        );
    }

    /** POST /api/ai-recommendations */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id'             => 'required|exists:users,id',
            'recommendation_type' => 'nullable|string|max:100',
            'ai_diagnosis'        => 'nullable|string',
            'title'               => 'nullable|string',
            'ai_suggestions'      => 'nullable|string',
            'is_system_created'   => 'nullable|boolean',
            'created_at_custom'   => 'nullable|date',
            'ai_next'             => 'nullable|date',
        ]);

        return response()->json(AiRecommendation::create($data)->load('user'), 201);
    }

    /** GET /api/ai-recommendations/{id} */
    public function show($id): JsonResponse
    {
        return response()->json(AiRecommendation::with('user')->findOrFail($id));
    }

    /** PUT /api/ai-recommendations/{id} */
    public function update(Request $request, $id): JsonResponse
    {
        $rec  = AiRecommendation::findOrFail($id);
        $data = $request->validate([
            'recommendation_type' => 'nullable|string|max:100',
            'ai_diagnosis'        => 'nullable|string',
            'title'               => 'nullable|string',
            'ai_suggestions'      => 'nullable|string',
            'ai_next'             => 'nullable|date',
        ]);

        $rec->update($data);
        return response()->json($rec);
    }

    /** DELETE /api/ai-recommendations/{id} */
    public function destroy($id): JsonResponse
    {
        AiRecommendation::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa gợi ý AI']);
    }

    // =========================================================================
    // AI GENERATION — Dành cho Member
    // =========================================================================

    /**
     * Tự động sinh gợi ý sức khỏe dựa trên dữ liệu của người dùng hiện tại.
     * POST /api/ai-recommendations/generate
     */
    public function generateForUser(Request $request, GroqService $groq, GeminiService $gemini): JsonResponse
    {
        $userId = $request->input('user_id');
        $user = $userId ? \App\Models\User::findOrFail($userId) : $request->user();
        
        $user->load([
            'memberProfile',
            'healthMetrics' => fn($q) => $q->orderBy('record_date', 'desc')->take(2),
        ]);

        $latest      = $user->healthMetrics->first();
        $metricsText = $latest
            ? sprintf(
                'Cân nặng: %s kg, Chiều cao: %s cm, Mỡ: %s%%, Cơ: %s%% kg, BMI: %s',
                $latest->weight             ?? 'N/A',
                $latest->height             ?? 'N/A',
                $latest->body_fat_percentage ?? 'N/A',
                $latest->muscle_mass_kg     ?? 'N/A',
                $latest->bmi               ?? 'N/A'
            )
            : 'Không có dữ liệu sức khỏe.';

        $goalText       = $user->memberProfile->health_notes ?? 'Không có thông tin mục tiêu.';
        $latestMetricId = $latest?->id ?? 'none';
        $cacheKey       = "ai_health_rec_user_{$user->id}_metric_{$latestMetricId}";

        try {
            // Cache chỉ lưu array, KHÔNG lưu Eloquent model hay Response object
            $cachedData = Cache::remember($cacheKey, now()->addHour(), function () use ($user, $groq, $gemini, $metricsText, $goalText) {
                $prompt = <<<PROMPT
Bạn là một huấn luyện viên AI chuyên nghiệp. Dựa vào thông tin của khách hàng sau đây,
hãy đưa ra 1 chẩn đoán ngắn gọn (diagnosis), 1 tiêu đề (title) cho lời khuyên,
và 3 gạch đầu dòng các gợi ý (suggestions) để cải thiện sức khỏe/thể hình.

LƯU Ý: Mọi nội dung (title, ai_diagnosis, ai_suggestions) BẮT BUỘC bằng TIẾNG VIỆT.
Trả về ĐÚNG định dạng JSON sau, không kèm markdown hay text nào khác:
{
  "title": "Tiêu đề lời khuyên",
  "ai_diagnosis": "Chẩn đoán ngắn gọn về thể trạng hiện tại",
  "ai_suggestions": "- Gợi ý 1\n- Gợi ý 2\n- Gợi ý 3"
}

Thông tin khách hàng:
- Giới tính: {$user->gender}
- Mục tiêu/Ghi chú sức khỏe: {$goalText}
- Chỉ số sức khỏe mới nhất: {$metricsText}
PROMPT;

                $parsed = $this->callAiAndParse($groq, $gemini, $prompt, ['title', 'ai_diagnosis', 'ai_suggestions']);

                $rec = AiRecommendation::create([
                    'user_id'             => $user->id,
                    'recommendation_type' => 'Health & Fitness',
                    'title'               => $parsed['title'],
                    'ai_diagnosis'        => $parsed['ai_diagnosis'],
                    'ai_suggestions'      => $parsed['ai_suggestions'],
                    'is_system_created'   => true,
                ]);

                // Trả về array thuần — Cache serializes tốt hơn Eloquent model
                return $rec->toArray();
            });

            return $this->successResponse($cachedData, 'Tạo gợi ý sức khỏe thành công');

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->errorResponse($e, 'Health & Fitness');
        }
    }

    // =========================================================================
    // AI GENERATION — Dành cho Admin / Manager
    // =========================================================================

    /**
     * Dự báo rủi ro rời bỏ hội viên (Churn Prediction).
     * POST /api/admin/churn-prediction
     */
    public function predictChurn(Request $request, GroqService $groq, GeminiService $gemini): JsonResponse
    {
        $members = \App\Models\User::whereHas('role', fn($q) => $q->where('role_name', 'member'))
            ->with([
                'memberProfile',
                'activeSubscription',
                'checkins' => fn($q) => $q->where('check_in_at', '>=', now()->subMonths(3)),
            ])
            ->latest()
            ->take(40) // Limit to 40 to avoid TPM (Tokens Per Minute) limit on free AI tiers
            ->get();

        if ($members->isEmpty()) {
            return response()->json(['message' => 'Không có hội viên nào để phân tích.'], 404);
        }

        $membersData = $members->map(function ($member) {
            $daysLeft = 0;
            if ($member->activeSubscription?->end_date) {
                $daysLeft = (int) now()->diffInDays(
                    Carbon::parse($member->activeSubscription->end_date),
                    false
                );
            }

            $checkins = $member->checkins;
            $lastCheckin = $checkins->sortByDesc('check_in_at')->first();
            $daysSinceLastCheckin = $lastCheckin ? now()->diffInDays(Carbon::parse($lastCheckin->check_in_at)) : 999;
            
            $checkinsThisMonth = $checkins->where('check_in_at', '>=', now()->subDays(30))->count();
            $checkinsLastMonth = $checkins->where('check_in_at', '>=', now()->subDays(60))->where('check_in_at', '<', now()->subDays(30))->count();

            return [
                'user_id'                 => $member->id,
                'days_since_last_checkin' => $daysSinceLastCheckin,
                'checkins_this_month'     => $checkinsThisMonth,
                'checkins_last_month'     => $checkinsLastMonth,
                'days_until_expiration'   => $daysLeft,
                'goal'                    => $member->memberProfile->health_notes ?? 'Không rõ mục tiêu',
            ];
        })->values()->toArray();

        $cacheKey = 'ai_churn_prediction_' . now()->format('Ymd_H');

        try {
            // Cache lưu array kết quả, KHÔNG lưu Response object
            $cachedData = Cache::remember($cacheKey, now()->addHour(), function () use ($groq, $gemini, $membersData) {
                $jsonInput = json_encode($membersData, JSON_UNESCAPED_UNICODE);

                $prompt = <<<PROMPT
Bạn là chuyên gia phân tích dữ liệu khách hàng (Churn Prediction) cho phòng Gym.
Dưới đây là dữ liệu ẩn danh của các hội viên trong 3 tháng gần nhất:
{$jsonInput}

Hãy đánh giá mức độ rủi ro rời bỏ và phân loại mỗi hội viên theo quy tắc SAU:
- "Đỏ" (Nguy cơ cao): Hội viên không check-in quá 14 ngày hoặc có tần suất check-in tháng này giảm đột ngột 70% so với tháng trước. ĐỐI VỚI NHÓM NÀY: Bắt buộc đưa ra ít nhất một gợi ý hành động cụ thể (ví dụ: gửi voucher giảm giá 10% gói tập tiếp theo, hoặc nhắc nhở PT liên hệ tư vấn lại lộ trình).
- "Vàng" (Cần lưu ý): Hội viên sắp hết hạn hợp đồng trong 7 ngày nhưng chưa có lịch hẹn gia hạn (days_until_expiration <= 7).
- "Xanh" (An toàn): Các trường hợp còn lại.

LƯU Ý: Nội dung BẮT BUỘC bằng Tiếng Việt.
Trả về ĐÚNG định dạng MẢNG JSON sau (không kèm markdown):
[
  {
    "user_id": 1,
    "risk_level": "Đỏ",
    "ai_diagnosis": "Lý do...",
    "ai_suggestions": "Hành động chăm sóc cụ thể (vd: Tặng voucher 10%...)..."
  }
]
PROMPT;

                $raw         = $this->callAI($groq, $gemini, $prompt);
                $raw         = preg_replace('/```json|```/', '', $raw);
                $parsedArray = json_decode(trim($raw), true);

                if (!is_array($parsedArray) || empty($parsedArray)) {
                    throw new \Exception('AI trả về sai định dạng mảng JSON cho Churn Prediction.');
                }

                $results = [];
                foreach ($parsedArray as $item) {
                    if (!isset($item['user_id'])) continue;

                    $rec = AiRecommendation::create([
                        'user_id'             => $item['user_id'],
                        'recommendation_type' => 'Churn Prediction',
                        'title'               => 'Dự báo rủi ro rời bỏ: Mức độ ' . ($item['risk_level'] ?? 'Không rõ'),
                        'ai_diagnosis'        => $item['ai_diagnosis']   ?? '',
                        'ai_suggestions'      => $item['ai_suggestions'] ?? '',
                        'is_system_created'   => true,
                    ]);

                    $results[] = $rec->toArray();
                }

                return $results; // array thuần, Cache-safe
            });

            return $this->successResponse($cachedData, 'Dự báo rời bỏ thành công');

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->errorResponse($e, 'Churn Prediction');
        }
    }

    /**
     * Báo cáo tổng quan kinh doanh (dành cho Quản lý).
     * POST /api/admin/manager-report
     */
    public function generateManagerReport(Request $request, GroqService $groq, GeminiService $gemini): JsonResponse
    {
        $cacheKey = 'ai_manager_report_' . now()->format('Ymd_H');

        try {
            $cachedData = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($request, $groq, $gemini) {
                $currentMonth = now();
                $lastMonth    = now()->subMonth(); // Lưu vào biến, tránh gọi subMonth() nhiều lần

                $currentRevenue = \App\Models\Payment::where('status', 'paid')
                    ->whereMonth('payment_date', $currentMonth->month)
                    ->whereYear('payment_date', $currentMonth->year)
                    ->sum('amount');

                $lastRevenue = \App\Models\Payment::where('status', 'paid')
                    ->whereMonth('payment_date', $lastMonth->month)
                    ->whereYear('payment_date', $lastMonth->year)
                    ->sum('amount');

                $totalSubs  = \App\Models\MemberSubscription::count();
                $activeSubs = \App\Models\MemberSubscription::where('status', 'active')->count();

                $topPlans = \App\Models\MembershipPlan::withCount('subscriptions')
                    ->orderBy('subscriptions_count', 'desc')
                    ->take(3)->get()
                    ->map(fn($p) => "{$p->plan_name} ({$p->subscriptions_count})")
                    ->implode(', ');

                $avgRating   = round(\App\Models\MemberFeedback::avg('rating') ?? 0, 1);
                $urgentCount = \App\Models\MemberFeedback::whereIn('ai_severity', ['High', 'Critical'])->count();

                $businessData = [
                    'revenue_current'  => $currentRevenue,
                    'revenue_last'     => $lastRevenue,
                    'revenue_growth_pct' => $lastRevenue > 0
                        ? round(($currentRevenue - $lastRevenue) / $lastRevenue * 100, 1)
                        : 0,
                    'retention_rate'   => $totalSubs > 0 ? round($activeSubs / $totalSubs * 100, 2) : 0,
                    'top_plans'        => $topPlans,
                    'avg_rating'       => $avgRating,
                    'urgent_feedback'  => $urgentCount,
                ];

                $prompt = 'Bạn là chuyên gia cố vấn chiến lược phòng Gym. Phân tích dữ liệu sau và trả về JSON '
                    . '(Tiếng Việt, không markdown): '
                    . json_encode($businessData, JSON_UNESCAPED_UNICODE)
                    . "\nYêu cầu JSON: { \"title\": \"...\", \"ai_diagnosis\": \"...\", \"ai_suggestions\": \"- Gợi ý 1\\n- Gợi ý 2\" }";

                return $this->runAiReport($groq, $gemini, $prompt, 'Business Report', $request);
            });

            return response()->json($cachedData, 201);

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->errorResponse($e, 'Manager Report');
        }
    }

    /**
     * Phân tích tỉ lệ giữ chân khách hàng.
     * POST /api/manager/retention-analysis
     */
    public function retentionAnalysis(Request $request, GroqService $groq, GeminiService $gemini): JsonResponse
    {
        $cacheKey = 'ai_manager_retention_' . now()->format('Ymd_H');
        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request, $groq, $gemini) {
                $total     = \App\Models\MemberSubscription::count();
                $active    = \App\Models\MemberSubscription::where('status', 'active')->count();
                $expired   = \App\Models\MemberSubscription::where('status', 'expired')->count();
                $cancelled = \App\Models\MemberSubscription::where('status', 'cancelled')->count();

                // Đếm subscription được cập nhật (gia hạn) trong 30 ngày qua
                // updated_at > created_at nghĩa là record đã bị chỉnh sửa (gia hạn)
                $renewals = \App\Models\MemberSubscription::where('updated_at', '>=', now()->subDays(30))
                    ->whereColumn('updated_at', '>', 'created_at')
                    ->count();

                $avgCheckins = round(
                    \App\Models\Checkin::where('check_in_at', '>=', now()->subDays(30))->count() / 4,
                    1
                );

                $payload = [
                    'total'                 => $total,
                    'active'                => $active,
                    'expired'               => $expired,
                    'cancelled'             => $cancelled,
                    'renewals_last_30d'     => $renewals,
                    'retention_rate_pct'    => $total > 0 ? round($active / $total * 100, 1) : 0,
                    'avg_checkins_per_week' => $avgCheckins,
                ];

                $prompt = 'Bạn là chuyên gia phân tích giữ chân khách hàng (Customer Retention) cho phòng Gym.
Dữ liệu hội viên: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . '

Phân tích: nguyên nhân rời bỏ, nhóm nguy cơ cao, xu hướng, chiến lược giữ chân cụ thể.
Trả về JSON (Tiếng Việt, không markdown):
{
  "title": "...",
  "ai_diagnosis": "Phân tích chi tiết 3-4 câu",
  "ai_suggestions": "- Chiến lược 1\n- Chiến lược 2\n- Chiến lược 3",
  "key_metrics": {"retention_rate": "...", "risk_group": "...", "trend": "..."}
}';

                return $this->runAiReport($groq, $gemini, $prompt, 'Retention Analysis', $request);
            });

            return response()->json($data, 201);

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->errorResponse($e, 'Retention Analysis');
        }
    }

    /**
     * Phân tích hiệu quả các gói tập.
     * POST /api/manager/plan-effectiveness
     */
    public function planEffectiveness(Request $request, GroqService $groq, GeminiService $gemini): JsonResponse
    {
        $cacheKey = 'ai_manager_plan_eff_' . now()->format('Ymd_H');
        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request, $groq, $gemini) {
                $plans = \App\Models\MembershipPlan::withCount([
                    'subscriptions',
                    'subscriptions as active_count' => fn($q) => $q->where('status', 'active'),
                ])->get()->map(fn($p) => [
                    'name'          => $p->plan_name,
                    'price'         => $p->price,
                    'duration_days' => $p->duration_days,
                    'total_subs'    => $p->subscriptions_count,
                    'active_subs'   => $p->active_count,
                    'revenue_est'   => $p->subscriptions_count * $p->price,
                ]);

                $prompt = 'Bạn là chuyên gia phân tích sản phẩm dịch vụ cho phòng Gym.
Dữ liệu các gói tập: ' . json_encode($plans, JSON_UNESCAPED_UNICODE) . '

Phân tích: gói bán chạy nhất, gói kém hiệu quả, cơ hội tối ưu danh mục.
Trả về JSON (Tiếng Việt, không markdown):
{
  "title": "...",
  "ai_diagnosis": "Phân tích chi tiết 3-4 câu",
  "ai_suggestions": "- Đề xuất 1\n- Đề xuất 2\n- Đề xuất 3",
  "best_plan": "...",
  "worst_plan": "..."
}';

                return $this->runAiReport($groq, $gemini, $prompt, 'Plan Effectiveness', $request);
            });

            return response()->json($data, 201);

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->errorResponse($e, 'Plan Effectiveness');
        }
    }

    /**
     * Phân tích hiệu quả khuyến mãi.
     * POST /api/manager/promotion-effectiveness
     */
    public function promotionEffectiveness(Request $request, GroqService $groq, GeminiService $gemini): JsonResponse
    {
        $cacheKey = 'ai_manager_promo_eff_' . now()->format('Ymd_H');
        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request, $groq, $gemini) {
                $promos = \App\Models\Promotion::withCount('payments')
                    ->with('payments')
                    ->get()->map(fn($p) => [
                        'name'           => $p->name,
                        'type'           => $p->discount_type,
                        'value'          => $p->discount_value,
                        'start_date'     => $p->start_date,
                        'end_date'       => $p->end_date,
                        'usage_count'    => $p->payments_count,
                        'revenue_impact' => $p->payments->sum('amount'),
                    ]);

                $prompt = 'Bạn là chuyên gia phân tích hiệu quả marketing và khuyến mãi cho phòng Gym.
Dữ liệu chiến dịch: ' . json_encode($promos, JSON_UNESCAPED_UNICODE) . '

Phân tích: chiến dịch hiệu quả nhất, ROI, đề xuất cải thiện chính sách.
Trả về JSON (Tiếng Việt, không markdown):
{
  "title": "...",
  "ai_diagnosis": "Phân tích chi tiết 3-4 câu",
  "ai_suggestions": "- Đề xuất 1\n- Đề xuất 2\n- Đề xuất 3",
  "best_promotion": "...",
  "recommendation": "..."
}';

                return $this->runAiReport($groq, $gemini, $prompt, 'Promotion Effectiveness', $request);
            });

            return response()->json($data, 201);

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->errorResponse($e, 'Promotion Effectiveness');
        }
    }

    /**
     * Phân tích phản hồi khách hàng.
     * POST /api/manager/feedback-analysis
     */
    public function feedbackAnalysis(Request $request, GroqService $groq, GeminiService $gemini): JsonResponse
    {
        $cacheKey = 'ai_manager_feedback_' . now()->format('Ymd_H');
        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request, $groq, $gemini) {
                $avgRating = round(\App\Models\MemberFeedback::avg('rating') ?? 0, 1);
                $total     = \App\Models\MemberFeedback::count();
                $urgent    = \App\Models\MemberFeedback::whereIn('ai_severity', ['High', 'Critical'])->count();
                $byRating  = \App\Models\MemberFeedback::selectRaw('rating, count(*) as cnt')
                    ->groupBy('rating')->pluck('cnt', 'rating');
                $recentNeg = \App\Models\MemberFeedback::where('rating', '<=', 2)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->pluck('content')->take(5);

                $payload = compact('avgRating', 'total', 'urgent', 'byRating') + [
                    'recent_negative_samples' => $recentNeg,
                ];

                $prompt = 'Bạn là chuyên gia phân tích trải nghiệm khách hàng (CX) cho phòng Gym.
Dữ liệu phản hồi: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . '

Phân tích: điểm đau chính, xu hướng cảm xúc, vấn đề cần giải quyết khẩn cấp.
Trả về JSON (Tiếng Việt, không markdown):
{
  "title": "...",
  "ai_diagnosis": "Phân tích 3-4 câu",
  "ai_suggestions": "- Hành động 1\n- Hành động 2\n- Hành động 3",
  "sentiment": "Tích cực / Trung lập / Tiêu cực",
  "urgent_issues": "..."
}';

                return $this->runAiReport($groq, $gemini, $prompt, 'Feedback Analysis', $request);
            });

            return response()->json($data, 201);

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->errorResponse($e, 'Feedback Analysis');
        }
    }

    /**
     * Báo cáo kinh doanh tháng hiện tại.
     * POST /api/manager/general-report
     */
    public function generalReport(Request $request, GroqService $groq, GeminiService $gemini): JsonResponse
    {
        $cacheKey = 'ai_manager_general_' . now()->format('Ymd_H');
        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($request, $groq, $gemini) {
                $now      = now();
                $lastMonth = now()->subMonth(); // lưu biến để tránh drift

                $revenue    = \App\Models\Payment::where('status', 'paid')->whereMonth('payment_date', $now->month)->whereYear('payment_date', $now->year)->sum('amount');
                $revLast    = \App\Models\Payment::where('status', 'paid')->whereMonth('payment_date', $lastMonth->month)->whereYear('payment_date', $lastMonth->year)->sum('amount');
                $newMembers = \App\Models\User::whereHas('role', fn($q) => $q->where('role_name', 'member'))->whereMonth('created_at', $now->month)->count();
                $total      = \App\Models\User::whereHas('role', fn($q) => $q->where('role_name', 'member'))->count();
                $checkins   = \App\Models\Checkin::whereMonth('check_in_at', $now->month)->count();
                $activeContracts  = \App\Models\MemberSubscription::where('status', 'active')->count();
                $pendingPayments  = \App\Models\Payment::where('status', 'pending')->count();

                $payload = compact('revenue', 'revLast', 'newMembers', 'total', 'checkins', 'activeContracts', 'pendingPayments') + [
                    'revenue_growth_pct' => $revLast > 0 ? round(($revenue - $revLast) / $revLast * 100, 1) : 0,
                    'month'              => $now->format('m/Y'),
                ];

                $month  = $now->format('m/Y');
                $prompt = "Bạn là Giám đốc Điều hành phòng Gym, đang viết báo cáo kinh doanh tháng {$month}.
Dữ liệu: " . json_encode($payload, JSON_UNESCAPED_UNICODE) . "

Viết báo cáo tổng quan: tình hình kinh doanh, điểm mạnh/yếu, cơ hội cải thiện.
Trả về JSON (Tiếng Việt, không markdown):
{
  \"title\": \"Báo cáo tháng {$month}\",
  \"ai_diagnosis\": \"Tóm tắt 4-5 câu có số liệu cụ thể\",
  \"ai_suggestions\": \"- Ưu tiên 1\\n- Ưu tiên 2\\n- Ưu tiên 3\",
  \"overall_health\": \"Tốt / Trung bình / Cần cải thiện\"
}";

                return $this->runAiReport($groq, $gemini, $prompt, 'General Report', $request);
            });

            return response()->json($data, 201);

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->errorResponse($e, 'General Report');
        }
    }

    /**
     * Phân tích sức khỏe hội viên kết hợp nguy cơ rời bỏ.
     * POST /api/manager/health-churn-report
     */
    public function healthChurnReport(Request $request, GroqService $groq, GeminiService $gemini): JsonResponse
    {
        $cacheKey = 'ai_manager_health_churn_' . now()->format('Ymd_H');
        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request, $groq, $gemini) {
                $members = \App\Models\User::whereHas('role', fn($q) => $q->where('role_name', 'member'))
                    ->with([
                        'memberProfile',
                        'activeSubscription',
                        'healthMetrics' => fn($q) => $q->latest('record_date')->take(1),
                        'checkins'      => fn($q) => $q->where('check_in_at', '>=', now()->subDays(30)),
                    ])->get();

                $summary = $members->map(fn($m) => [
                    'checkins_30d' => $m->checkins->count(),
                    'days_left'    => $m->activeSubscription
                        ? (int) now()->diffInDays(Carbon::parse($m->activeSubscription->end_date), false)
                        : -999,
                    'bmi'          => optional($m->healthMetrics->first())->bmi,
                    'body_fat'     => optional($m->healthMetrics->first())->body_fat_percentage,
                    'goal'         => optional($m->memberProfile)->health_notes ?? 'N/A',
                ]);

                $stats = [
                    'total_members'  => $members->count(),
                    'no_checkin_30d' => $summary->where('checkins_30d', 0)->count(),
                    'expiring_7d'    => $summary->whereBetween('days_left', [0, 7])->count(),
                    'expired'        => $summary->where('days_left', '<', 0)->count(),
                    'avg_bmi'        => round($summary->whereNotNull('bmi')->avg('bmi') ?? 0, 1),
                    'high_fat_count' => $summary->where('body_fat', '>', 25)->count(),
                    'sample_data'    => $summary->take(10)->values(),
                ];

                $prompt = 'Bạn là chuyên gia sức khỏe và phân tích dữ liệu hội viên phòng Gym.
Thống kê tổng hợp: ' . json_encode($stats, JSON_UNESCAPED_UNICODE) . '

Phân tích: tình trạng sức khỏe tổng thể, nhóm nguy cơ rời bỏ cao, đề xuất can thiệp cho quản lý.
Trả về JSON (Tiếng Việt, không markdown):
{
  "title": "...",
  "ai_diagnosis": "Phân tích chuyên sâu 4-5 câu",
  "ai_suggestions": "- Hành động 1\n- Hành động 2\n- Hành động 3",
  "churn_risk_count": "...",
  "health_alert": "..."
}';

                return $this->runAiReport($groq, $gemini, $prompt, 'Health & Churn Report', $request);
            });

            return response()->json($data, 201);

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->errorResponse($e, 'Health & Churn Report');
        }
    }

    /**
     * Tổng quan toàn diện — tóm lược từ snapshot DB, không gọi lại các AI con.
     * POST /api/manager/overview
     */
    public function managerOverview(Request $request, GroqService $groq, GeminiService $gemini): JsonResponse
    {
        $cacheKey = 'ai_manager_overview_' . now()->format('Ymd_H');
        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(20), function () use ($request, $groq, $gemini) {
                $now       = now();
                $lastMonth = now()->subMonth();

                $revenue   = \App\Models\Payment::where('status', 'paid')->whereMonth('payment_date', $now->month)->sum('amount');
                $revLast   = \App\Models\Payment::where('status', 'paid')->whereMonth('payment_date', $lastMonth->month)->sum('amount');
                $active    = \App\Models\MemberSubscription::where('status', 'active')->count();
                $total     = \App\Models\MemberSubscription::count();
                $avgRating = round(\App\Models\MemberFeedback::avg('rating') ?? 0, 1);
                $urgent    = \App\Models\MemberFeedback::whereIn('ai_severity', ['High', 'Critical'])->count();
                $noCheckin = \App\Models\User::whereHas('role', fn($q) => $q->where('role_name', 'member'))
                    ->whereDoesntHave('checkins', fn($q) => $q->where('check_in_at', '>=', now()->subDays(30)))
                    ->count();
                $topPlan   = \App\Models\MembershipPlan::withCount('subscriptions')->orderBy('subscriptions_count', 'desc')->first();
                $bestPromo = \App\Models\Promotion::withCount('payments')->orderBy('payments_count', 'desc')->first();

                $month    = $now->format('m/Y');
                $snapshot = [
                    'month'                   => $month,
                    'revenue_this_month'      => $revenue,
                    'revenue_growth_pct'      => $revLast > 0 ? round(($revenue - $revLast) / $revLast * 100, 1) : 0,
                    'retention_rate_pct'      => $total > 0 ? round($active / $total * 100, 1) : 0,
                    'avg_feedback_rating'     => $avgRating,
                    'urgent_feedbacks'        => $urgent,
                    'members_no_checkin_30d'  => $noCheckin,
                    'best_selling_plan'       => optional($topPlan)->plan_name,
                    'best_promotion'          => optional($bestPromo)->name,
                ];

                $prompt = "Bạn là trợ lý CEO phòng Gym. Dựa vào snapshot kinh doanh tháng {$month}:
" . json_encode($snapshot, JSON_UNESCAPED_UNICODE) . "

Viết tóm tắt TỔNG QUAN ngắn gọn: doanh thu, giữ chân KH, gói tập, khuyến mãi, phản hồi KH, sức khỏe hội viên.
Trả về JSON (Tiếng Việt, không markdown):
{
  \"title\": \"Tổng quan tháng {$month}\",
  \"ai_diagnosis\": \"Tóm tắt toàn diện 4-5 câu\",
  \"ai_suggestions\": \"- Ưu tiên 1\\n- Ưu tiên 2\\n- Ưu tiên 3\",
  \"overall_score\": \"X/10\",
  \"key_highlights\": \"...\"
}";

                return $this->runAiReport($groq, $gemini, $prompt, 'Manager Overview', $request);
            });

            return response()->json($data, 201);

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->errorResponse($e, 'Manager Overview');
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Gọi AI, strip markdown, parse JSON, validate required keys.
     *
     * @param  string[] $requiredKeys
     * @throws \Exception
     */
    private function callAiAndParse(GroqService $groq, GeminiService $gemini, string $prompt, array $requiredKeys = ['title']): array
    {
        $raw    = $this->callAI($groq, $gemini, $prompt);
        $raw    = preg_replace('/```json|```/', '', $raw);
        $parsed = json_decode(trim($raw), true);

        foreach ($requiredKeys as $key) {
            if (!isset($parsed[$key])) {
                throw new \Exception("AI trả về thiếu trường [{$key}] trong JSON.");
            }
        }

        return $parsed;
    }

    /**
     * Helper dùng cho các Manager report:
     * Gọi AI → parse → lưu DB → trả về array (Cache-safe).
     *
     * @return array  Chứa message, data (array), count — KHÔNG phải Response object
     * @throws \Exception
     */
    private function runAiReport(GroqService $groq, GeminiService $gemini, string $prompt, string $type, Request $request): array
    {
        $parsed = $this->callAiAndParse($groq, $gemini, $prompt, ['title', 'ai_diagnosis', 'ai_suggestions']);

        $rec = AiRecommendation::create([
            'user_id'             => $request->user()?->id ?? 1,
            'recommendation_type' => $type,
            'title'               => $parsed['title'],
            'ai_diagnosis'        => $parsed['ai_diagnosis'],
            'ai_suggestions'      => $parsed['ai_suggestions'],
            'is_system_created'   => true,
        ]);

        // Metadata bổ sung từ AI (ngoài 3 trường chuẩn) — đính kèm vào response, không lưu DB
        $extra = array_diff_key($parsed, array_flip(['title', 'ai_diagnosis', 'ai_suggestions']));

        return [
            'message' => "{$type} – Phân tích thành công",
            'data'    => array_merge($rec->toArray(), $extra),
            'count'   => 1,
        ];
    }

    /**
     * Gọi AI theo provider cấu hình trong Database (có Cache), có fallback tự động.
     *
     * @throws \Exception
     */
    private function callAI(GroqService $groq, GeminiService $gemini, string $prompt): string
    {
        // Lấy Provider từ Database (mặc định là groq)
        $provider = \App\Models\SystemSetting::getValue('ai_provider', 'groq');

        if ($provider === 'gemini') {
            try {
                $model = \App\Models\SystemSetting::getValue('gemini_model', 'gemini-1.5-flash');
                Log::info("Calling Gemini AI (model={$model})...");
                return $gemini->askAI($prompt);
            } catch (\Exception $e) {
                Log::warning('Gemini thất bại, fallback sang Groq: ' . $e->getMessage());
                return $groq->askAI($prompt);
            }
        }

        try {
            $model = \App\Models\SystemSetting::getValue('groq_model', 'llama-3.3-70b-versatile');
            Log::info("Calling Groq AI (model={$model})...");
            return $groq->askAI($prompt);
        } catch (\Exception $e) {
            Log::warning('Groq thất bại, fallback sang Gemini: ' . $e->getMessage());
            return $gemini->askAI($prompt);
        }
    }


    /**
     * Chuẩn hóa response thành công.
     */
    private function successResponse(mixed $data, string $message = 'Thành công'): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data'    => $data,
            'count'   => is_countable($data) ? count($data) : 1,
        ], 201);
    }

    /**
     * Chuẩn hóa response lỗi với phân loại thân thiện.
     */
    private function errorResponse(\Exception $e, string $context = ''): JsonResponse
    {
        Log::error("AI Error [{$context}]: " . $e->getMessage() . "\n" . $e->getTraceAsString());

        $message = $e->getMessage();
        $status  = 500;
        $friendly = 'Đã xảy ra lỗi khi gọi AI. Vui lòng thử lại.';

        if (str_contains($message, '429') || str_contains($message, 'rate limit')
            || str_contains($message, 'quá tải') || str_contains($message, 'high demand')) {
            $friendly = 'Máy chủ AI đang bị quá tải. Vui lòng đợi vài giây và thử lại.';
            $status   = 429;
        } elseif (str_contains($message, 'quota')) {
            preg_match('/retry in ([\d.]+)s/i', $message, $matches);
            $wait     = isset($matches[1]) ? ceil((float) $matches[1]) : null;
            $friendly = $wait
                ? "Quá giới hạn yêu cầu. Vui lòng đợi {$wait} giây nữa."
                : 'Bạn đã hết hạn mức sử dụng miễn phí. Vui lòng đợi một lát.';
            $status = 429;
        } elseif (str_contains($message, 'not found') || str_contains($message, 'model')) {
            $friendly = 'Model AI không được hỗ trợ. Kiểm tra GROQ_MODEL hoặc GEMINI_MODEL trong .env.';
        } elseif (str_contains($message, 'API_KEY') || str_contains($message, 'chưa được cấu hình')) {
            $friendly = 'API Key AI chưa được cấu hình. Kiểm tra GROQ_API_KEY / GEMINI_API_KEY trong .env.';
        } elseif (str_contains($message, 'thiếu trường') || str_contains($message, 'định dạng JSON')) {
            $friendly = 'AI trả về dữ liệu không đúng định dạng. Vui lòng thử lại.';
            $status   = 502;
        }

        return response()->json([
            'message' => $friendly,
            'error'   => $message,
            'context' => $context,
        ], $status);
    }
}