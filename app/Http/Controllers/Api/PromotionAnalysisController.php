<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\User;
use App\Models\Payment;
use App\Services\GroqService;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PromotionAnalysisController extends Controller
{
    /**
     * Phân tích hiệu quả khuyến mãi chuyên sâu (ROI, Conversion Rate).
     * POST /api/manager/promotion-analysis
     */
    public function analyze(Request $request, GroqService $groq, GeminiService $gemini): JsonResponse
    {
        $cacheKey = 'ai_promotion_analysis_' . now()->format('Ymd_H');

        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($groq, $gemini) {
                // Lấy tất cả khuyến mãi kèm thống kê thanh toán đã hoàn tất
                $promos = Promotion::withCount(['payments' => function($q) {
                    $q->where('status', 'paid');
                }])
                ->with(['payments' => function($q) {
                    $q->where('status', 'paid');
                }])
                ->get();

                $totalMembers = User::whereHas('role', fn($q) => $q->where('role_name', 'member'))->count();

                $analysisData = $promos->map(function ($p) use ($totalMembers) {
                    $revenue = $p->payments->sum('amount');
                    $usageCount = $p->payments_count;
                    
                    // Giả định discount trong DB là số phần trăm (decimal 5,2)
                    // ROI = Doanh thu thu về / Tổng số tiền đã giảm giá
                    // Nếu discount = 10 (10%), số tiền giảm = Revenue / 0.9 * 0.1
                    $discountPct = (float) $p->discount;
                    $totalDiscountGiven = 0;
                    
                    if ($discountPct > 0 && $discountPct < 100) {
                        $originalValue = $revenue / (1 - ($discountPct / 100));
                        $totalDiscountGiven = $originalValue - $revenue;
                    } else {
                        // Nếu là giảm tiền mặt cố định (giả định nếu > 100)
                        $totalDiscountGiven = $usageCount * $discountPct;
                    }
                    
                    $roi = $totalDiscountGiven > 0 ? round($revenue / $totalDiscountGiven, 2) : 0;
                    $conversionRate = $totalMembers > 0 ? round(($usageCount / $totalMembers) * 100, 2) : 0;

                    return [
                        'promotion_id'   => $p->id,
                        'title'          => $p->title,
                        'code'           => $p->code,
                        'discount'       => $p->discount . ($discountPct < 100 ? '%' : ' VNĐ'),
                        'usage_count'    => $usageCount,
                        'total_revenue'  => round($revenue, 0),
                        'total_discount' => round($totalDiscountGiven, 0),
                        'roi'            => $roi,
                        'conversion_rate'=> $conversionRate . '%',
                    ];
                });

                // Tổng hợp các chỉ số chính cho AI
                $summary = [
                    'total_active_promotions' => $promos->where('is_active', true)->count(),
                    'total_usage'             => $analysisData->sum('usage_count'),
                    'total_revenue_generated' => $analysisData->sum('total_revenue'),
                    'total_discount_cost'     => $analysisData->sum('total_discount'),
                    'avg_roi'                 => $analysisData->avg('roi'),
                ];

                $prompt = "Bạn là chuyên gia phân tích marketing và tăng trưởng cho chuỗi phòng Gym.
Dưới đây là dữ liệu thống kê thực tế về các chương trình khuyến mãi:
Thống kê tổng quan: " . json_encode($summary, JSON_UNESCAPED_UNICODE) . "
Chi tiết từng mã: " . json_encode($analysisData, JSON_UNESCAPED_UNICODE) . "

Nhiệm vụ của bạn:
1. Phân tích chỉ số ROI (Tỷ suất hoàn vốn) và Conversion Rate (Tỷ lệ chuyển đổi) để tìm ra chương trình hiệu quả nhất.
2. Đánh giá xem việc giảm giá có đang thực sự mang lại doanh thu hay chỉ đang làm giảm lợi nhuận.
3. Đề xuất cải thiện: nên tiếp tục, điều chỉnh hay dừng mã khuyến mãi nào.
4. Đề xuất 1 chiến dịch mới tối ưu hơn dựa trên xu hướng hiện tại.

Trả về kết quả dưới định dạng JSON (Tiếng Việt, không markdown):
{
  \"title\": \"Báo cáo Phân tích Chiến dịch Khuyến mãi\",
  \"ai_diagnosis\": \"Nhận xét tổng quát về hiệu quả marketing 4-5 câu.\",
  \"ai_suggestions\": \"- Đề xuất 1\\n- Đề xuất 2\\n- Đề xuất 3\",
  \"top_performer\": \"Tên mã KM tốt nhất\",
  \"roi_analysis\": \"Giải thích ngắn gọn về chỉ số ROI trung bình\",
  \"recommendation\": \"Hành động ưu tiên cần thực hiện ngay\"
}";

                // Sử dụng Groq là chính (vì nhanh), Gemini làm fallback
                $provider = \App\Models\SystemSetting::getValue('ai_provider', 'groq');
                $groqService = app(GroqService::class);
                $geminiService = app(GeminiService::class);
                
                try {
                    $raw = ($provider === 'gemini') ? $geminiService->askAI($prompt) : $groqService->askAI($prompt);
                } catch (\Exception $e) {
                    $raw = ($provider === 'gemini') ? $groqService->askAI($prompt) : $geminiService->askAI($prompt);
                }

                // Trích xuất JSON
                $jsonStart = strpos($raw, '{');
                $jsonEnd   = strrpos($raw, '}');
                if ($jsonStart !== false && $jsonEnd !== false) {
                    $raw = substr($raw, $jsonStart, $jsonEnd - $jsonStart + 1);
                }

                $aiParsed = json_decode($raw, true);

                return [
                    'metrics'     => $analysisData,
                    'summary'     => $summary,
                    'ai_analysis' => $aiParsed
                ];
            });

            return response()->json([
                'message' => 'Phân tích khuyến mãi thành công',
                'data'    => $data
            ], 201);

        } catch (\Exception $e) {
            Log::error("Promotion Analysis Error: " . $e->getMessage());
            Cache::forget($cacheKey);
            return response()->json([
                'message' => 'Lỗi khi phân tích khuyến mãi',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
