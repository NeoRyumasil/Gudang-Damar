<?php
try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    error_log("=== CRITICAL EXCEPTION CAUGHT IN API/INDEX.PHP ===");
    error_log("Exception: " . get_class($e));
    error_log("Message: " . $e->getMessage());
    error_log("File: " . $e->getFile() . ":" . $e->getLine());
    error_log("Stack Trace:\n" . $e->getTraceAsString());
    error_log("==================================================");
    throw $e;
}
