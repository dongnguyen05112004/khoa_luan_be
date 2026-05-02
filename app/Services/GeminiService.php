<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;
use Exception;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $model;
    protected string $fallbackModel;
    protected int    $maxRetries;

    public function __construct()
    {
        $this->apiKey        = env('GEMINI_API_KEY', '');
        $this->model         = env('GEMINI_MODEL', 'gemini-2.5-flash');
        $this->fallbackModel = env('GEMINI_FALLBACK_MODEL', 'gemini-2.0-flash');
        $this->maxRetries    = 3;

        if (empty($this->apiKey)) {
            throw new Exception('GEMINI_API_KEY chưa được cấu hình trong file .env');
        }
    }

    /**
     * Gửi prompt lên Google Gemini AI.
     * - Tự động retry khi gặp 429/503 (quá tải).
     * - Tự động fallback sang model dự phòng sau khi hết retry.
     * - Luôn throw Exception nếu không lấy được kết quả.
     *
     * @throws Exception
     */
    public function askAI(string $prompt): string
    {
        // Thử model chính trước
        try {
            return $this->callWithRetry($this->model, $prompt);
        } catch (Exception $e) {
            $message = $e->getMessage();

            // Nếu lỗi quá tải → thử fallback model
            if ($this->isOverloadError($message)) {
                Log::warning("Gemini ({$this->model}) quá tải sau {$this->maxRetries} lần thử. Chuyển sang fallback: {$this->fallbackModel}");
                return $this->callFallback($prompt);
            }

            // Lỗi khác (API key sai, model không tồn tại,...) → throw thẳng
            Log::error("Gemini Error (model={$this->model}): {$message}");
            throw $e;
        }
    }

    /**
     * Gọi một model cụ thể với cơ chế retry khi quá tải.
     *
     * @throws Exception
     */
    private function callWithRetry(string $modelName, string $prompt): string
    {
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                Log::info("Gemini: Gọi model [{$modelName}], attempt " . ($attempt + 1) . "/{$this->maxRetries}");

                $result = Gemini::generativeModel(model: $modelName)
                    ->generateContent($prompt);

                $text = $result->text();

                if (empty($text)) {
                    throw new Exception("Gemini trả về nội dung rỗng từ model [{$modelName}].");
                }

                return $text;

            } catch (Exception $e) {
                $message = $e->getMessage();
                $attempt++;

                if ($this->isOverloadError($message)) {
                    if ($attempt < $this->maxRetries) {
                        $waitSeconds = $this->parseWaitSeconds($message);
                        Log::warning("Gemini [{$modelName}] quá tải. Đợi {$waitSeconds}s trước retry #{$attempt}...");
                        sleep($waitSeconds);
                        continue;
                    }
                    // Hết số lần retry → throw để caller xử lý
                    throw new Exception("Gemini [{$modelName}] quá tải sau {$this->maxRetries} lần thử.");
                }

                // Lỗi không phải quá tải → không retry, throw ngay
                throw new Exception($message);
            }
        }

        // Đảm bảo luôn throw nếu thoát khỏi vòng lặp (không bao giờ return null)
        throw new Exception("Gemini [{$modelName}] không phản hồi sau {$this->maxRetries} lần thử.");
    }

    /**
     * Thử gọi fallback model 1 lần duy nhất (không retry).
     *
     * @throws Exception
     */
    private function callFallback(string $prompt): string
    {
        try {
            Log::info("Gemini: Thử fallback model [{$this->fallbackModel}]...");

            $result = Gemini::generativeModel(model: $this->fallbackModel)
                ->generateContent($prompt);

            $text = $result->text();

            if (empty($text)) {
                throw new Exception("Gemini fallback [{$this->fallbackModel}] trả về nội dung rỗng.");
            }

            Log::info("Gemini: Fallback [{$this->fallbackModel}] thành công.");
            return $text;

        } catch (Exception $e) {
            $msg = "Gemini quá tải ở cả model chính [{$this->model}] lẫn fallback [{$this->fallbackModel}]: " . $e->getMessage();
            Log::error($msg);
            throw new Exception($msg);
        }
    }

    /**
     * Kiểm tra có phải lỗi quá tải / rate limit không.
     */
    private function isOverloadError(string $message): bool
    {
        return str_contains($message, '429')
            || str_contains($message, '503')
            || str_contains($message, 'high demand')
            || str_contains($message, 'overloaded')
            || str_contains($message, 'quá tải');
    }

    /**
     * Đọc thời gian chờ từ error message (nếu có), mặc định 3s.
     */
    private function parseWaitSeconds(string $message): int
    {
        // Ví dụ: "retry after 5.2 seconds" hoặc "try again in 3s"
        if (preg_match('/(?:retry after|try again in)\s*([\d.]+)\s*s/i', $message, $matches)) {
            return (int) ceil((float) $matches[1]);
        }
        return 3;
    }
}