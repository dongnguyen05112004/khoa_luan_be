<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\GroqService;

try {
    $groq = new GroqService();
    echo "Testing Groq with model: llama-3.3-70b-versatile\n";
    $response = $groq->askAI("Say hello in 1 word");
    echo "Response: " . $response . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
