<?php

// Define CMS access
define('access', 'index');

use Darkheim\Infrastructure\Bootstrap\EntrypointBootstrapper;

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    EntrypointBootstrapper::boot(dirname(__DIR__));

} catch (Throwable $ex) {
    if (ob_get_level() > 0) {
        ob_clean();
    }
    $errorPagePath = __DIR__ . '/../includes/error.html';
    $errorPage     = @file_get_contents($errorPagePath);
    if (! is_string($errorPage) || $errorPage === '') {
        http_response_code(500);
        echo 'Error: ' . htmlspecialchars($ex->getMessage(), ENT_QUOTES, 'UTF-8');
        return;
    }
    echo str_replace('{ERROR_MESSAGE}', $ex->getMessage(), $errorPage);
}
