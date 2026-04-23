<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;

class GeminiService
{
    /**
     * Gửi prompt lên Google Gemini AI và nhận kết quả text.
     * 
     * @param string $prompt Nội dung câu lệnh
     * @return string Kết quả từ AI
     */
    public function askAI($prompt)
    {
        // Sử dụng model gemini-2.5-flash theo đúng thông số trên Google AI Studio của bạn
        $result = Gemini::generativeModel(model: 'gemini-2.5-flash')->generateContent($prompt);
            
        return $result->text();
    }
}
