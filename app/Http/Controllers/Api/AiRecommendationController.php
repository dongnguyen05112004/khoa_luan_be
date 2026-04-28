<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiRecommendation;
use App\Services\GroqService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AiRecommendationController extends Controller
{
    /** GET /api/ai-recommendations */
    public function index(Request $request)
    {
        return response()->json(
            AiRecommendation::with('user')
                ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
                ->when($request->type, fn($q) => $q->where('recommendation_type', $request->type))
                ->latest()->paginate($request->per_page ?? 20)
        );
    }

    /** POST /api/ai-recommendations */
    public function store(Request $request)
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
    public function show($id)
    {
        return response()->json(AiRecommendation::with('user')->findOrFail($id));
    }

    /** PUT /api/ai-recommendations/{id} */
    public function update(Request $request, $id)
    {
        $rec = AiRecommendation::findOrFail($id);
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
    public function destroy($id)
    {
        AiRecommendation::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa gợi ý AI']);
    }

    /** 
     * Tự động sinh Phản hồi AI dựa trên dữ liệu sức khỏe của người dùng hiện tại 
     * POST /api/ai-recommendations/generate 
     */
    public function generateForUser(Request $request, GroqService $groqService, GeminiService $geminiService)
    {
        $user = $request->user()->load(['memberProfile', 'healthMetrics' => function($q) {
            $q->orderBy('record_date', 'desc')->take(2);
        }]);

        $metricsText = "Không có dữ liệu sức khỏe nào.";
        if ($user->healthMetrics->count() > 0) {
            $latest = $user->healthMetrics->first();
            $metricsText = sprintf(
                "Cân nặng: %s kg, Chiều cao: %s cm, Mỡ: %s%%, Cơ: %s%%, BMI: %s",
                $latest->weight ?? 'N/A',
                $latest->height ?? 'N/A',
                $latest->body_fat_percentage ?? 'N/A',
                $latest->muscle_mass_kg ?? 'N/A',
                $latest->bmi ?? 'N/A'
            );
        }

        $goalText = $user->memberProfile->health_notes ?? 'Không có thông tin mục tiêu.';

        $latestMetricId = $user->healthMetrics->first() ? $user->healthMetrics->first()->id : 'none';
        $cacheKey = "ai_health_rec_user_{$user->id}_metric_{$latestMetricId}";

        try {
            return Cache::remember($cacheKey, now()->addHour(), function() use ($user, $groqService, $geminiService, $metricsText, $goalText) {
                $prompt = "Bạn là một huấn luyện viên AI chuyên nghiệp. Dựa vào thông tin của khách hàng sau đây, hãy đưa ra 1 chẩn đoán ngắn gọn (diagnosis), 1 tiêu đề (title) cho lời khuyên, và 3 gạch đầu dòng các gợi ý (suggestions) để cải thiện sức khỏe/thể hình. 

LƯU Ý QUAN TRỌNG: Mọi nội dung trả về (title, ai_diagnosis, ai_suggestions) BẮT BUỘC PHẢI ĐƯỢC VIẾT HOÀN TOÀN BẰNG TIẾNG VIỆT.

Hãy trả về ĐÚNG định dạng JSON sau, không kèm markdown hay text nào khác:
{
  \"title\": \"Tiêu đề lời khuyên bằng tiếng việt\",
  \"ai_diagnosis\": \"Chẩn đoán ngắn gọn về thể trạng hiện tại bằng tiếng việt\",
  \"ai_suggestions\": \"- Gợi ý 1 bằng tiếng việt\\n- Gợi ý 2 bằng tiếng việt\\n- Gợi ý 3 bằng tiếng việt\"
}

Thông tin khách hàng:
- Giới tính: {$user->gender}
- Mục tiêu/Ghi chú sức khỏe: {$goalText}
- Chỉ số sức khỏe mới nhất: {$metricsText}";

                $responseJson = $this->callAI($groqService, $geminiService, $prompt);
                $responseJson = str_replace(['```json', '```'], '', $responseJson);
                $parsed = json_decode(trim($responseJson), true);

                if (!$parsed || !isset($parsed['title'])) {
                    throw new \Exception("AI trả về dữ liệu không đúng định dạng JSON.");
                }

                // Lưu vào database
                $rec = AiRecommendation::create([
                    'user_id'             => $user->id,
                    'recommendation_type' => 'Health & Fitness',
                    'title'               => $parsed['title'],
                    'ai_diagnosis'        => $parsed['ai_diagnosis'],
                    'ai_suggestions'      => $parsed['ai_suggestions'],
                    'is_system_created'   => true,
                ]);

                return $this->handleAiSuccess($rec, 'Tạo gợi ý sức khỏe thành công (Kết quả mới)');
            });

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->handleAiError($e, 'Health');
        }
    }

    /**
     * Phân tích và Dự báo hành vi Hội viên (Churn Prediction)
     * Dành cho Admin/Manager để dự báo tỷ lệ rời bỏ
     * POST /api/admin/churn-prediction
     */
    public function predictChurn(Request $request, GroqService $groqService, GeminiService $geminiService)
    {
        // 1. Lấy tất cả user có role là 'member' kèm theo dữ liệu liên quan
        $members = \App\Models\User::whereHas('role', function($q) {
            $q->where('role_name', 'member');
        })->with(['memberProfile', 'activeSubscription', 'checkins' => function($q) {
            $q->where('check_in_at', '>=', now()->subMonths(3));
        }])->get();

        if ($members->isEmpty()) {
            return response()->json(['message' => 'Không có hội viên nào để phân tích.'], 404);
        }

        // 2. Tạo chuỗi dữ liệu ẩn danh để gửi cho AI
        $membersData = [];
        foreach ($members as $member) {
            $checkinCount = $member->checkins->count();
            $daysLeft = 0;
            if ($member->activeSubscription && $member->activeSubscription->end_date) {
                $endDate = \Carbon\Carbon::parse($member->activeSubscription->end_date);
                $daysLeft = now()->diffInDays($endDate, false); // Âm nếu đã hết hạn
            }
            $goal = $member->memberProfile->health_notes ?? 'Không rõ mục tiêu';

            $membersData[] = [
                'user_id' => $member->id,
                'checkin_last_3_months' => $checkinCount,
                'days_until_expiration' => intval($daysLeft),
                'goal' => $goal
            ];
        }

        $jsonInput = json_encode($membersData, JSON_UNESCAPED_UNICODE);

        $cacheKey = 'ai_churn_prediction_latest';

        try {
            return Cache::remember($cacheKey, now()->addHour(), function() use ($groqService, $geminiService, $jsonInput) {
                $prompt = "Bạn là chuyên gia phân tích dữ liệu khách hàng (Churn Prediction) cho phòng Gym.
Dưới đây là dữ liệu ẩn danh của các hội viên:
$jsonInput

Dựa vào dữ liệu, phân loại hội viên theo rủi ro rời bỏ (Không gia hạn):
- risk_level: 'Đỏ' (Nguy cơ cao: ít tập, sắp hết hạn) hoặc 'Vàng' (Cần lưu ý) hoặc 'Xanh' (Ổn định).
- ai_diagnosis: Lý do tại sao xếp loại như vậy.
- ai_suggestions: 1 hành động chăm sóc cụ thể.

LƯU Ý: Nội dung trả về BẮT BUỘC bằng Tiếng Việt.
Trả về ĐÚNG định dạng MẢNG JSON sau (không kèm markdown):
[
  {
    \"user_id\": 1,
    \"risk_level\": \"Đỏ\",
    \"ai_diagnosis\": \"Lý do...\",
    \"ai_suggestions\": \"Hành động...\"
  }
]";

                $responseJson = $this->callAI($groqService, $geminiService, $prompt);
                $responseJson = str_replace(['```json', '```'], '', $responseJson);
                $parsedArray = json_decode(trim($responseJson), true);

                if (!$parsedArray || !is_array($parsedArray)) {
                    throw new \Exception("AI trả về sai định dạng mảng JSON.");
                }

                $results = [];
                foreach ($parsedArray as $item) {
                    if (isset($item['user_id'])) {
                        $rec = \App\Models\AiRecommendation::create([
                            'user_id'             => $item['user_id'],
                            'recommendation_type' => 'Churn Prediction',
                            'title'               => 'Dự báo rủi ro rời bỏ: Mức độ ' . ($item['risk_level'] ?? 'Không rõ'),
                            'ai_diagnosis'        => $item['ai_diagnosis'] ?? '',
                            'ai_suggestions'      => $item['ai_suggestions'] ?? '',
                            'is_system_created'   => true,
                        ]);
                        $results[] = $rec;
                    }
                }

                return $this->handleAiSuccess($results, 'Dự báo rời bỏ thành công (Kết quả mới)');
            });

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->handleAiError($e, 'Churn');
        }
    }

    /**
     * Báo cáo tổng quan hoạt động phòng gym (Dành cho Quản lý)
     * POST /api/admin/manager-report
     */
    public function generateManagerReport(Request $request, GroqService $groqService, GeminiService $geminiService)
    {
        // Sử dụng Cache để tránh gọi AI quá nhiều lần trong thời gian ngắn (15 phút)
        $cacheKey = 'ai_manager_report_latest';
        
        try {
            return Cache::remember($cacheKey, now()->addMinutes(15), function() use ($request, $groqService, $geminiService) {
                // 1. Thu thập dữ liệu doanh thu
                $currentMonthRevenue = \App\Models\Payment::where('status', 'paid')
                    ->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year)
                    ->sum('amount');
                    
                $lastMonthRevenue = \App\Models\Payment::where('status', 'paid')
                    ->whereMonth('payment_date', now()->subMonth()->month)
                    ->whereYear('payment_date', now()->subMonth()->year)
                    ->sum('amount');

                // 2. Tỷ lệ giữ chân khách hàng
                $totalSubscriptions = \App\Models\MemberSubscription::count();
                $activeSubscriptions = \App\Models\MemberSubscription::where('status', 'active')->count();
                $retentionRate = $totalSubscriptions > 0 ? round(($activeSubscriptions / $totalSubscriptions) * 100, 2) : 0;

                // 3. Phân tích gói tập
                $topPlans = \App\Models\MembershipPlan::withCount('subscriptions')
                    ->orderBy('subscriptions_count', 'desc')
                    ->take(3)
                    ->get()
                    ->map(fn($p) => $p->plan_name . " ({$p->subscriptions_count})")->implode(', ');

                // 4. Phản hồi khách hàng
                $avgRating = \App\Models\MemberFeedback::avg('rating') ?? 0;
                $urgentCount = \App\Models\MemberFeedback::whereIn('ai_severity', ['High', 'Critical'])->count();

                $businessData = [
                    'revenue_current' => $currentMonthRevenue,
                    'revenue_last' => $lastMonthRevenue,
                    'retention_rate' => $retentionRate,
                    'top_plans' => $topPlans,
                    'avg_rating' => round($avgRating, 1),
                    'urgent_feedback' => $urgentCount
                ];

                $prompt = "Bạn là chuyên gia cố vấn chiến lược phòng Gym. Phân tích dữ liệu sau và trả về JSON: " . json_encode($businessData) . 
                          "\nYêu cầu JSON: { \"title\": \"...\", \"ai_diagnosis\": \"...\", \"ai_suggestions\": \"- Gợi ý 1\\n- Gợi ý 2\" } (Tiếng Việt)";

                $responseJson = $this->callAI($groqService, $geminiService, $prompt);
                $responseJson = str_replace(['```json', '```'], '', $responseJson);
                $parsed = json_decode(trim($responseJson), true);

                if (!$parsed || !isset($parsed['title'])) {
                    throw new \Exception("AI trả về dữ liệu không đúng định dạng JSON.");
                }

                // Lưu vào database để có lịch sử
                $rec = \App\Models\AiRecommendation::create([
                    'user_id'             => $request->user() ? $request->user()->id : 1,
                    'recommendation_type' => 'Business Report',
                    'title'               => $parsed['title'],
                    'ai_diagnosis'        => $parsed['ai_diagnosis'],
                    'ai_suggestions'      => $parsed['ai_suggestions'],
                    'is_system_created'   => true,
                ]);

                return $this->handleAiSuccess($rec, 'Tạo báo cáo quản lý thành công (Kết quả mới)');
            });

        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->handleAiError($e, 'Manager Report');
        }
    }

    /**
     * POST /api/manager/retention-analysis
     * [2] Phân tích tỉ lệ giữ chân khách hàng
     */
    public function retentionAnalysis(Request $request, GroqService $groq, GeminiService $gemini)
    {
        $cacheKey = 'ai_manager_retention_' . now()->format('Ymd_H');
        try {
            return Cache::remember($cacheKey, now()->addMinutes(30), function() use ($request, $groq, $gemini) {
                $total  = \App\Models\MemberSubscription::count();
                $active = \App\Models\MemberSubscription::where('status', 'active')->count();
                $expired= \App\Models\MemberSubscription::where('status', 'expired')->count();
                $cancelled = \App\Models\MemberSubscription::where('status', 'cancelled')->count();

                // Tỉ lệ gia hạn trong 30 ngày qua
                $renewals = \App\Models\MemberSubscription::where('created_at', '>=', now()->subDays(30))
                    ->whereColumn('created_at', '>', 'updated_at')->count();

                // Check-in trung bình/tuần
                $avgCheckins = \App\Models\Checkin::where('check_in_at', '>=', now()->subDays(30))
                    ->count() / 4;

                $data = compact('total', 'active', 'expired', 'cancelled', 'renewals') + [
                    'retention_rate_pct' => $total > 0 ? round($active / $total * 100, 1) : 0,
                    'avg_checkins_per_week' => round($avgCheckins, 1),
                ];

                $prompt = "Bạn là chuyên gia phân tích giữ chân khách hàng cho phòng Gym (Customer Retention).
Dữ liệu hội viên: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "

Hãy phân tích chuyên sâu: nguyên nhân rời bỏ, nhóm nguy cơ cao, xu hướng, và đề xuất chiến lược giữ chân cụ thể.
Trả về JSON (Tiếng Việt, không markdown):
{
  \"title\": \"...\",
  \"ai_diagnosis\": \"Phân tích chi tiết 3-4 câu\",
  \"ai_suggestions\": \"- Chiến lược 1\\n- Chiến lược 2\\n- Chiến lược 3\",
  \"key_metrics\": {\"retention_rate\": \"...\", \"risk_group\": \"...\", \"trend\": \"...\"}
}";

                return $this->runAiReport($groq, $gemini, $prompt, 'Retention Analysis', $request);
            });
        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->handleAiError($e, 'Retention Analysis');
        }
    }

    /**
     * POST /api/manager/plan-effectiveness
     * [3] Phân tích hiệu quả gói tập
     */
    public function planEffectiveness(Request $request, GroqService $groq, GeminiService $gemini)
    {
        $cacheKey = 'ai_manager_plan_eff_' . now()->format('Ymd_H');
        try {
            return Cache::remember($cacheKey, now()->addMinutes(30), function() use ($request, $groq, $gemini) {
                $plans = \App\Models\MembershipPlan::withCount([
                    'subscriptions',
                    'subscriptions as active_count' => fn($q) => $q->where('status', 'active'),
                ])->get()->map(fn($p) => [
                    'name'         => $p->plan_name,
                    'price'        => $p->price,
                    'duration_days'=> $p->duration_days,
                    'total_subs'   => $p->subscriptions_count,
                    'active_subs'  => $p->active_count,
                    'revenue_est'  => $p->subscriptions_count * $p->price,
                ]);

                $prompt = "Bạn là chuyên gia phân tích sản phẩm dịch vụ cho phòng Gym.
Dữ liệu các gói tập: " . json_encode($plans, JSON_UNESCAPED_UNICODE) . "

Phân tích: gói nào bán chạy nhất, gói nào kém hiệu quả, cơ hội tối ưu danh mục gói tập.
Trả về JSON (Tiếng Việt, không markdown):
{
  \"title\": \"...\",
  \"ai_diagnosis\": \"Phân tích chi tiết 3-4 câu\",
  \"ai_suggestions\": \"- Đề xuất 1\\n- Đề xuất 2\\n- Đề xuất 3\",
  \"best_plan\": \"...\",
  \"worst_plan\": \"...\"
}";

                return $this->runAiReport($groq, $gemini, $prompt, 'Plan Effectiveness', $request);
            });
        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->handleAiError($e, 'Plan Effectiveness');
        }
    }

    /**
     * POST /api/manager/promotion-effectiveness
     * [4] Phân tích hiệu quả khuyến mãi
     */
    public function promotionEffectiveness(Request $request, GroqService $groq, GeminiService $gemini)
    {
        $cacheKey = 'ai_manager_promo_eff_' . now()->format('Ymd_H');
        try {
            return Cache::remember($cacheKey, now()->addMinutes(30), function() use ($request, $groq, $gemini) {
                $promos = \App\Models\Promotion::withCount('payments')
                    ->with('payments')
                    ->get()
                    ->map(fn($p) => [
                        'name'          => $p->name,
                        'type'          => $p->discount_type,
                        'value'         => $p->discount_value,
                        'start_date'    => $p->start_date,
                        'end_date'      => $p->end_date,
                        'usage_count'   => $p->payments_count,
                        'revenue_impact'=> $p->payments->sum('amount'),
                    ]);

                $prompt = "Bạn là chuyên gia phân tích hiệu quả marketing và khuyến mãi cho phòng Gym.
Dữ liệu chiến dịch khuyến mãi: " . json_encode($promos, JSON_UNESCAPED_UNICODE) . "

Phân tích: chiến dịch nào hiệu quả nhất, ROI, đề xuất cải thiện chính sách khuyến mãi.
Trả về JSON (Tiếng Việt, không markdown):
{
  \"title\": \"...\",
  \"ai_diagnosis\": \"Phân tích chi tiết 3-4 câu\",
  \"ai_suggestions\": \"- Đề xuất 1\\n- Đề xuất 2\\n- Đề xuất 3\",
  \"best_promotion\": \"...\",
  \"recommendation\": \"...\"
}";

                return $this->runAiReport($groq, $gemini, $prompt, 'Promotion Effectiveness', $request);
            });
        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->handleAiError($e, 'Promotion Effectiveness');
        }
    }

    /**
     * POST /api/manager/feedback-analysis
     * [5] Phân tích phản hồi khách hàng
     */
    public function feedbackAnalysis(Request $request, GroqService $groq, GeminiService $gemini)
    {
        $cacheKey = 'ai_manager_feedback_' . now()->format('Ymd_H');
        try {
            return Cache::remember($cacheKey, now()->addMinutes(30), function() use ($request, $groq, $gemini) {
                $avgRating   = round(\App\Models\MemberFeedback::avg('rating') ?? 0, 1);
                $total       = \App\Models\MemberFeedback::count();
                $urgent      = \App\Models\MemberFeedback::whereIn('ai_severity', ['High', 'Critical'])->count();
                $byRating    = \App\Models\MemberFeedback::selectRaw('rating, count(*) as cnt')
                    ->groupBy('rating')->pluck('cnt', 'rating');
                $recentNeg   = \App\Models\MemberFeedback::where('rating', '<=', 2)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->pluck('content')->take(5);

                $data = compact('avgRating', 'total', 'urgent', 'byRating') + [
                    'recent_negative_samples' => $recentNeg,
                ];

                $prompt = "Bạn là chuyên gia phân tích trải nghiệm khách hàng (CX) cho phòng Gym.
Dữ liệu phản hồi: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "

Phân tích: điểm đau chính của khách hàng, xu hướng cảm xúc, vấn đề cần giải quyết khẩn cấp.
Trả về JSON (Tiếng Việt, không markdown):
{
  \"title\": \"...\",
  \"ai_diagnosis\": \"Phân tích cảm xúc và xu hướng 3-4 câu\",
  \"ai_suggestions\": \"- Hành động 1\\n- Hành động 2\\n- Hành động 3\",
  \"sentiment\": \"Tích cực / Trung lập / Tiêu cực\",
  \"urgent_issues\": \"...\"
}";

                return $this->runAiReport($groq, $gemini, $prompt, 'Feedback Analysis', $request);
            });
        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->handleAiError($e, 'Feedback Analysis');
        }
    }

    /**
     * POST /api/manager/general-report
     * [6] Báo cáo chung (Business Report)
     */
    public function generalReport(Request $request, GroqService $groq, GeminiService $gemini)
    {
        $cacheKey = 'ai_manager_general_' . now()->format('Ymd_H');
        try {
            return Cache::remember($cacheKey, now()->addMinutes(15), function() use ($request, $groq, $gemini) {
                $revenue      = \App\Models\Payment::where('status', 'paid')->whereMonth('payment_date', now()->month)->sum('amount');
                $revLast      = \App\Models\Payment::where('status', 'paid')->whereMonth('payment_date', now()->subMonth()->month)->sum('amount');
                $newMembers   = \App\Models\User::whereHas('role', fn($q) => $q->where('role_name', 'member'))
                    ->whereMonth('created_at', now()->month)->count();
                $totalMembers = \App\Models\User::whereHas('role', fn($q) => $q->where('role_name', 'member'))->count();
                $checkins     = \App\Models\Checkin::whereMonth('check_in_at', now()->month)->count();
                $activeContracts = \App\Models\MemberSubscription::where('status', 'active')->count();
                $pendingPayments = \App\Models\Payment::where('status', 'pending')->count();

                $data = compact('revenue', 'revLast', 'newMembers', 'totalMembers', 'checkins', 'activeContracts', 'pendingPayments') + [
                    'revenue_growth_pct' => $revLast > 0 ? round(($revenue - $revLast) / $revLast * 100, 1) : 0,
                    'month' => now()->format('m/Y'),
                ];

                $prompt = "Bạn là Giám đốc Điều hành phòng Gym, đang viết báo cáo kinh doanh tháng " . now()->format('m/Y') . ".
Dữ liệu kinh doanh: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "

Viết báo cáo tổng quan: tình hình kinh doanh, điểm mạnh/yếu, cơ hội cải thiện.
Trả về JSON (Tiếng Việt, không markdown):
{
  \"title\": \"Báo cáo tháng " . now()->format('m/Y') . "\",
  \"ai_diagnosis\": \"Tóm tắt tình hình 4-5 câu có số liệu cụ thể\",
  \"ai_suggestions\": \"- Ưu tiên 1\\n- Ưu tiên 2\\n- Ưu tiên 3\",
  \"overall_health\": \"Tốt / Trung bình / Cần cải thiện\"
}";

                return $this->runAiReport($groq, $gemini, $prompt, 'General Report', $request);
            });
        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->handleAiError($e, 'General Report');
        }
    }

    /**
     * POST /api/manager/health-churn-report
     * [7] Phân tích sức khỏe hội viên, nguy cơ rời bỏ & báo cáo quản lý
     */
    public function healthChurnReport(Request $request, GroqService $groq, GeminiService $gemini)
    {
        $cacheKey = 'ai_manager_health_churn_' . now()->format('Ymd_H');
        try {
            return Cache::remember($cacheKey, now()->addMinutes(30), function() use ($request, $groq, $gemini) {
                $members = \App\Models\User::whereHas('role', fn($q) => $q->where('role_name', 'member'))
                    ->with([
                        'memberProfile',
                        'activeSubscription',
                        'healthMetrics' => fn($q) => $q->latest('record_date')->take(1),
                        'checkins'      => fn($q) => $q->where('check_in_at', '>=', now()->subDays(30)),
                    ])->get();

                $summary = $members->map(fn($m) => [
                    'checkins_30d'    => $m->checkins->count(),
                    'days_left'       => $m->activeSubscription
                        ? now()->diffInDays(Carbon::parse($m->activeSubscription->end_date), false)
                        : -999,
                    'bmi'             => optional($m->healthMetrics->first())->bmi,
                    'body_fat'        => optional($m->healthMetrics->first())->body_fat_percentage,
                    'goal'            => optional($m->memberProfile)->health_notes ?? 'N/A',
                ]);

                $stats = [
                    'total_members'    => $members->count(),
                    'no_checkin_30d'   => $summary->where('checkins_30d', 0)->count(),
                    'expiring_7d'      => $summary->where('days_left', '>=', 0)->where('days_left', '<=', 7)->count(),
                    'expired'          => $summary->where('days_left', '<', 0)->count(),
                    'avg_bmi'          => round($summary->whereNotNull('bmi')->avg('bmi'), 1),
                    'high_fat_pct'     => $summary->where('body_fat', '>', 25)->count(),
                    'sample_data'      => $summary->take(10)->values(),
                ];

                $prompt = "Bạn là chuyên gia sức khỏe và phân tích dữ liệu hội viên phòng Gym.
Thống kê tổng hợp: " . json_encode($stats, JSON_UNESCAPED_UNICODE) . "

Hãy phân tích: tình trạng sức khỏe tổng thể hội viên, nhóm có nguy cơ rời bỏ cao, đề xuất can thiệp cho quản lý.
Trả về JSON (Tiếng Việt, không markdown):
{
  \"title\": \"...\",
  \"ai_diagnosis\": \"Phân tích chuyên sâu 4-5 câu về sức khỏe và nguy cơ rời bỏ\",
  \"ai_suggestions\": \"- Hành động quản lý 1\\n- Hành động quản lý 2\\n- Hành động quản lý 3\",
  \"churn_risk_count\": \"...\",
  \"health_alert\": \"...\"
}";

                return $this->runAiReport($groq, $gemini, $prompt, 'Health & Churn Report', $request);
            });
        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->handleAiError($e, 'Health & Churn Report');
        }
    }

    /**
     * POST /api/manager/overview
     * [1] Tổng quan – gọi song song và tóm tắt kết quả từ các phân tích 2-7
     */
    public function managerOverview(Request $request, GroqService $groq, GeminiService $gemini)
    {
        $cacheKey = 'ai_manager_overview_' . now()->format('Ymd_H');
        try {
            return Cache::remember($cacheKey, now()->addMinutes(20), function() use ($request, $groq, $gemini) {
                // Thu thập nhanh số liệu tổng hợp từ DB (không gọi lại AI con)
                $revenue   = \App\Models\Payment::where('status', 'paid')->whereMonth('payment_date', now()->month)->sum('amount');
                $revLast   = \App\Models\Payment::where('status', 'paid')->whereMonth('payment_date', now()->subMonth()->month)->sum('amount');
                $active    = \App\Models\MemberSubscription::where('status', 'active')->count();
                $total     = \App\Models\MemberSubscription::count();
                $avgRating = round(\App\Models\MemberFeedback::avg('rating') ?? 0, 1);
                $urgent    = \App\Models\MemberFeedback::whereIn('ai_severity', ['High', 'Critical'])->count();
                $noCheckin = \App\Models\User::whereHas('role', fn($q) => $q->where('role_name', 'member'))
                    ->whereDoesntHave('checkins', fn($q) => $q->where('check_in_at', '>=', now()->subDays(30)))
                    ->count();
                $topPlan   = \App\Models\MembershipPlan::withCount('subscriptions')->orderBy('subscriptions_count', 'desc')->first();
                $bestPromo = \App\Models\Promotion::withCount('payments')->orderBy('payments_count', 'desc')->first();

                $snapshot = [
                    'month'              => now()->format('m/Y'),
                    'revenue_this_month' => $revenue,
                    'revenue_growth_pct' => $revLast > 0 ? round(($revenue - $revLast) / $revLast * 100, 1) : 0,
                    'retention_rate_pct' => $total > 0 ? round($active / $total * 100, 1) : 0,
                    'avg_feedback_rating'=> $avgRating,
                    'urgent_feedbacks'   => $urgent,
                    'members_no_checkin_30d' => $noCheckin,
                    'best_selling_plan'  => optional($topPlan)->plan_name,
                    'best_promotion'     => optional($bestPromo)->name,
                ];

                $prompt = "Bạn là trợ lý CEO phòng Gym. Dựa vào snapshot kinh doanh tháng " . now()->format('m/Y') . ":
" . json_encode($snapshot, JSON_UNESCAPED_UNICODE) . "

Viết tóm tắt TỔNG QUAN ngắn gọn bao gồm: doanh thu, giữ chân KH, hiệu quả gói tập, khuyến mãi, phản hồi KH, sức khỏe hội viên.
Trả về JSON (Tiếng Việt, không markdown):
{
  \"title\": \"Tổng quan tháng " . now()->format('m/Y') . "\",
  \"ai_diagnosis\": \"Tóm tắt toàn diện 4-5 câu bao quát tất cả khía cạnh\",
  \"ai_suggestions\": \"- Ưu tiên hành động 1\\n- Ưu tiên hành động 2\\n- Ưu tiên hành động 3\",
  \"overall_score\": \"X/10\",
  \"key_highlights\": \"...\"
}";

                return $this->runAiReport($groq, $gemini, $prompt, 'Manager Overview', $request);
            });
        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            return $this->handleAiError($e, 'Manager Overview');
        }
    }

    /** Helper: gọi AI, parse JSON, lưu DB, trả response */
    private function runAiReport(GroqService $groq, GeminiService $gemini, string $prompt, string $type, Request $request): \Illuminate\Http\JsonResponse
    {
        $raw    = $this->callAI($groq, $gemini, $prompt);
        $raw    = preg_replace('/```json|```/', '', $raw);
        $parsed = json_decode(trim($raw), true);

        if (!$parsed || !isset($parsed['title'])) {
            throw new \Exception("AI trả về sai định dạng JSON cho: $type");
        }

        $rec = AiRecommendation::create([
            'user_id'             => $request->user() ? $request->user()->id : 1,
            'recommendation_type' => $type,
            'title'               => $parsed['title'],
            'ai_diagnosis'        => $parsed['ai_diagnosis'] ?? '',
            'ai_suggestions'      => $parsed['ai_suggestions'] ?? '',
            'is_system_created'   => true,
        ]);

        // Đính kèm metadata bổ sung vào response (không lưu DB)
        $extra = array_diff_key($parsed, array_flip(['title', 'ai_diagnosis', 'ai_suggestions']));

        return response()->json([
            'message' => "$type – Phân tích thành công",
            'data'    => array_merge($rec->toArray(), $extra),
            'count'   => 1,
        ], 201);
    }


    private function callAI(GroqService $groqService, GeminiService $geminiService, string $prompt): string
    {
        try {
            Log::info('Calling Groq AI (' . env('GROQ_MODEL') . ')...');
            return $groqService->askAI($prompt);
        } catch (\Exception $e) {
            Log::warning('Groq AI failed, falling back to Gemini: ' . $e->getMessage());
            return $geminiService->askAI($prompt);
        }
    }

    /** Helper xử lý lỗi AI tập trung */
    private function handleAiError(\Exception $e, $context = '')
    {
        Log::error("AI Error ($context): " . $e->getMessage() . "\n" . $e->getTraceAsString());

        $errorMessage = $e->getMessage();
        $friendlyMessage = 'Đã xảy ra lỗi khi gọi AI. Vui lòng thử lại.';
        $status = 500;

        if (str_contains($errorMessage, '429') || str_contains($errorMessage, 'rate limit') || str_contains($errorMessage, 'quá tải') || str_contains($errorMessage, 'high demand')) {
            $friendlyMessage = 'Máy chủ AI đang bị quá tải. Vui lòng đợi vài giây và thử lại.';
            $status = 429;
        } elseif (str_contains($errorMessage, 'quota')) {
            preg_match('/retry in ([\d\.]+)s/', $errorMessage, $matches);
            $waitTime = isset($matches[1]) ? ceil((float)$matches[1]) : null;
            $friendlyMessage = $waitTime
                ? "Quá giới hạn yêu cầu. Vui lòng đợi {$waitTime} giây nữa."
                : 'Bạn đã hết hạn mức sử dụng miễn phí. Vui lòng đợi một lát.';
            $status = 429;
        } elseif (str_contains($errorMessage, 'not found') || str_contains($errorMessage, 'model')) {
            $friendlyMessage = 'Model AI không được hỗ trợ. Vui lòng kiểm tra GROQ_MODEL hoặc GEMINI_MODEL trong .env.';
        } elseif (str_contains($errorMessage, 'API_KEY') || str_contains($errorMessage, 'chưa được cấu hình')) {
            $friendlyMessage = 'API Key AI chưa được cấu hình. Vui lòng kiểm tra GROQ_API_KEY trong .env.';
        }

        return response()->json([
            'message' => $friendlyMessage,
            'error'   => $errorMessage,
            'context' => $context
        ], $status);
    }

    /** Helper xử lý thành công AI tập trung */
    private function handleAiSuccess($data, $message = 'Thành công')
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'count' => is_countable($data) ? count($data) : 1
        ], 201);
    }
}
