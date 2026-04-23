<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $models = \Gemini\Laravel\Facades\Gemini::models()->list();
    foreach ($models->models as $model) {
        echo $model->name . "\n";
    }
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
