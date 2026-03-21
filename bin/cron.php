#!/usr/bin/env php
<?php

declare(strict_types=1);

use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Cron\CronExecutor;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from CLI.\n");
    exit(1);
}

define('access', 'cron');

$rootPath = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
$bootPath = $rootPath . '/includes/bootstrap/boot.php';
if (!@include $bootPath) {
    fwrite(STDERR, "Could not load Darkheim CMS.\n");
    exit(1);
}

$options = getopt('', ['id::', 'help']);
if (isset($options['help'])) {
    echo "Usage:\n";
    echo "  php bin/cron.php            Execute all due enabled cron jobs\n";
    echo "  php bin/cron.php --id=3     Execute a single cron job by DB id\n";
    exit(0);
}

$singleCronId = null;
if (isset($options['id'])) {
    $id = (string) $options['id'];
    if (!Validator::UnsignedNumber($id)) {
        fwrite(STDERR, "Invalid --id value.\n");
        exit(1);
    }
    $singleCronId = $id;
}

try {
    $executor = new CronExecutor();
    $executed = $executor->execute($singleCronId);

    echo json_encode([
        'code' => 200,
        'message' => 'Crons successfully executed.',
        'executed' => $executed,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, json_encode([
        'code' => 500,
        'error' => $e->getMessage(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    exit(1);
}

