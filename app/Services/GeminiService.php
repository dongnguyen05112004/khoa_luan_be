<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;
use Exception;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    /**
     * Gửi prompt lên Google Gemini AI với cơ chế tự động thử lại khi quá tải.
     */
    public function askAI($prompt)
    {
        $modelName = env('GEMINI_MODEL', 'gemini-2.5-flash'); // Sử dụng 2.5-flash vì 1.5-flash không có trong danh sách khả dụng của bạn
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $model = Gemini::generativeModel(model: $modelName);
                $result = $model->generateContent($prompt);
                
                return $result->text();

            } catch (Exception $e) {
                $message = $e->getMessage();
                $attempt++;

                // Kiểm tra lỗi quá tải
                if (str_contains($message, '429') || str_contains($message, '503') || str_contains($message, 'high demand')) {
                    if ($attempt < $maxRetries) {
                        Log::warning("Gemini API ($modelName) overloaded. Retrying attempt $attempt...");
                        sleep(2); // Đợi 2s cố định để nhanh hơn
                        continue;
                    }
                    
                    // Nếu đã thử hết số lần với model chính, thử 1 lần cuối với model dự phòng
                    if ($modelName !== 'gemini-flash-latest') {
                        Log::info("Falling back to gemini-flash-latest...");
                        try {
                            $model = Gemini::generativeModel(model: 'gemini-flash-latest');
                            $result = $model->generateContent($prompt);
                            return $result->text();
                        } catch (Exception $fallbackEx) {
                            throw new Exception("Máy chủ AI hiện đang quá tải (ngay cả bản dự phòng). Vui lòng thử lại sau 1 phút.");
                        }
                    }
                    
                    throw new Exception("Máy chủ AI hiện đang quá tải. Vui lòng quay lại sau ít phút.");
                }

                Log::error("Gemini Error: " . $message);
                throw new Exception($message); // Trả về message gốc để Controller xử lý
            }
        }
    }
}