<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiRecommendation;
use Illuminate\Http\Request;

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
    public function generateForUser(Request $request, \App\Services\GeminiService $geminiService)
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

        // Prompt gửi cho Gemini
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

        try {
            $responseJson = $geminiService->askAI($prompt);
            
            // Xóa bỏ markdown code block nếu Gemini có trả về
            $responseJson = str_replace(['```json', '```'], '', $responseJson);
            $parsed = json_decode(trim($responseJson), true);

            if (!$parsed || !isset($parsed['title'])) {
                return response()->json(['message' => 'AI trả về dữ liệu không đúng định dạng', 'raw' => $responseJson], 500);
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

            return response()->json([
                'message' => 'Đã tạo gợi ý AI thành công',
                'data' => $rec
            ], 201);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gemini API Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            $errorMessage = $e->getMessage();
            $friendlyMessage = 'Lỗi khi gọi API Gemini';
            
            if (str_contains($errorMessage, 'high demand')) {
                $friendlyMessage = 'Máy chủ AI của Google đang bị quá tải (High demand). Vui lòng đợi vài giây và thử lại.';
            } else if (str_contains($errorMessage, 'quota')) {
                $friendlyMessage = 'API Key của bạn đã hết hạn mức sử dụng (Quota Exceeded).';
            }

            return response()->json([
                'message' => $friendlyMessage,
                'error' => $errorMessage
            ], 500);
        }
    }

    /**
     * Phân tích và Dự báo hành vi Hội viên (Churn Prediction)
     * Dành cho Admin/Manager để dự báo tỷ lệ rời bỏ
     * POST /api/admin/churn-prediction
     */
    public function predictChurn(Request $request, \App\Services\GeminiService $geminiService)
    {
        // 1. Lấy tất cả user có role là 'member' kèm theo dữ liệu liên quan
        $members = \App\Models\User::whereHas('role', function($q) {
            $q->where('role_name', 'member');
        })->with(['memberProfile', 'activeSubscription', 'checkins' => function($q) {
            $q->where('checkin_time', '>=', now()->subMonths(3));
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

        // 3. Chuẩn bị Prompt
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

        // 4. Gọi AI
        try {
            $responseJson = $geminiService->askAI($prompt);
            $responseJson = str_replace(['```json', '```'], '', $responseJson);
            $parsedArray = json_decode(trim($responseJson), true);

            if (!$parsedArray || !is_array($parsedArray)) {
                return response()->json(['message' => 'AI trả về sai định dạng', 'raw' => $responseJson], 500);
            }

            $results = [];
            // 5. Lưu vào Database
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

            return response()->json([
                'message' => 'Dự báo rời bỏ thành công',
                'count' => count($results),
                'data' => $results
            ], 201);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gemini API Error (Churn): ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'message' => 'Lỗi khi gọi API Gemini',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
