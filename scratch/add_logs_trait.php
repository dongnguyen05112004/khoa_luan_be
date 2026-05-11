<?php

$dir = __DIR__ . '/../app/Models';
$files = scandir($dir);
foreach ($files as $file) {
    if (strpos($file, '.php') !== false && $file !== 'ActivityLog.php' && $file !== 'SystemSetting.php' && $file !== 'User.php') {
        $path = $dir . '/' . $file;
        $content = file_get_contents($path);
        if (strpos($content, 'LogsActivity') === false) {
            $content = preg_replace('/class\s+[A-Za-z0-9_]+\s+extends\s+Model\s*\{/', "$0\n    use \App\Traits\LogsActivity;\n", $content);
            file_put_contents($path, $content);
            echo "Added to $file\n";
        }
    }
}
echo "Done.\n";
