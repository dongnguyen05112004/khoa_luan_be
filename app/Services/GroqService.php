<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY', '');
        $this->model  = env('GROQ_MODEL', 'llama3-8b-8192');

        if (empty($this->apiKey)) {
            throw new Exception('GROQ_API_KEY chưa được cấu hình trong file .env');
        }
    }

    /**
     * Gửi prompt tới Groq AI và trả về nội dung text.
     * Tự động retry khi gặp lỗi 429 (rate limit).
     *
     * @param  string $prompt
     * @param  int    $maxRetries
     * @return string
     * @throws Exception
     */
    public function askAI(string $prompt, int $maxRetries = 3): string
    {
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $response = Http::withToken($this->apiKey)
                    ->timeout(30)
                    ->post($this->apiUrl, [
                        'model'       => $this->model,
                        'messages'    => [
                            [
                                'role'    => 'user',
                                'content' => $prompt,
                            ],
                        ],
                        'temperature' => 0.7,
                        'max_tokens'  => 1024,
                    ]);

                if ($response->successful()) {
                    $body = $response->json();
                    return $body['choices'][0]['message']['content'] ?? '';
                }

                // Xử lý Rate Limit (429)
                if ($response->status() === 429) {
                    $attempt++;
                    $waitSeconds = $this->parseRetryAfter($response);
                    Log::warning("Groq API rate limited. Waiting {$waitSeconds}s before retry #{$attempt}...");
                    sleep($waitSeconds);
                    continue;
                }

                // Lỗi khác từ Groq API
                $errorBody = $response->json();
                $errorMsg  = $errorBody['error']['message'] ?? "Groq API HTTP {$response->status()}";
                throw new Exception($errorMsg);

            } catch (Exception $e) {
                // Nếu là lỗi kết nối (timeout v.v.) thì retry
                if ($attempt < $maxRetries - 1 && !str_contains($e->getMessage(), 'GROQ_API_KEY')) {
                    $attempt++;
                    Log::warning("Groq request failed (attempt {$attempt}): " . $e->getMessage() . " – retrying...");
                    sleep(2);
                    continue;
                }
                Log::error('Groq API Error: ' . $e->getMessage());
                throw $e;
            }
        }

        throw new Exception('Groq AI không phản hồi sau ' . $maxRetries . ' lần thử. Vui lòng thử lại sau.');
    }

    /**
     * Đọc header Retry-After để biết cần đợi bao nhiêu giây.
     */
    private function parseRetryAfter(\Illuminate\Http\Client\Response $response): int
    {
        $retryAfter = $response->header('Retry-After');
        if ($retryAfter && is_numeric($retryAfter)) {
            return (int) $retryAfter;
        }

        // Fallback: thử đọc từ body JSON
        $body = $response->json();
        if (isset($body['error']['message'])) {
            preg_match('/try again in ([\d.]+)s/', $body['error']['message'], $matches);
            if (isset($matches[1])) {
                return (int) ceil((float) $matches[1]);
            }
        }

        return 5; // Đợi 5 giây mặc định
    }
}
