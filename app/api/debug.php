<?php
// Diagnostic file - REMOVE AFTER DEBUGGING
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "=== PHP INFO ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "OS: " . PHP_OS . "\n\n";

echo "=== ENVIRONMENT VARIABLES ===\n";
$envVars = ['APP_KEY', 'APP_ENV', 'APP_DEBUG', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_URL', 'VIEW_COMPILED_PATH', 'SESSION_DRIVER', 'CACHE_STORE', 'LOG_CHANNEL', 'VERCEL'];
foreach ($envVars as $var) {
    $val = getenv($var);
    if (in_array($var, ['APP_KEY', 'DB_URL', 'DB_PASSWORD']) && $val) {
        $val = substr($val, 0, 25) . '... [SET]';
    }
    echo "$var = " . ($val !== false ? $val : '[NOT SET]') . "\n";
}

echo "\n=== FILESYSTEM WRITABLE CHECK ===\n";
$dirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/cache',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
    __DIR__ . '/../storage',
    __DIR__ . '/../bootstrap/cache',
];
foreach ($dirs as $dir) {
    $realDir = realpath($dir) ?: $dir;
    $exists = is_dir($realDir) ? 'EXISTS' : 'MISSING';
    $writable = is_writable($realDir) ? 'WRITABLE' : 'NOT WRITABLE';
    echo basename($realDir) . " ($realDir): $exists | $writable\n";
}

echo "\n=== EXTENSIONS ===\n";
$exts = ['pdo', 'pdo_pgsql', 'pdo_mysql', 'openssl', 'mbstring', 'json', 'fileinfo'];
foreach ($exts as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? 'LOADED' : 'NOT LOADED') . "\n";
}

echo "\n=== FULL REQUEST SIMULATION ===\n";
try {
    // Simulate a full request, same as api/index.php
    $_SERVER['REQUEST_URI'] = '/up';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    ob_start();
    require __DIR__ . '/../public/index.php';
    $output = ob_get_clean();
    echo "Request to /up: SUCCESS\n";
    echo "Response: " . $output . "\n";
} catch (\Throwable $e) {
    $output = ob_get_clean();
    echo "Request FAILED!\n";
    echo "Exception: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
