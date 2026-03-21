<?php

declare(strict_types=1);

namespace Darkheim\Application\Api;

use Darkheim\Infrastructure\Http\JsonResponse;

final class VersionApiController
{
    public function render(): void
    {
        try {
            JsonResponse::send([
                'code' => 200,
                'apache' => $this->apacheVersion(),
                'php' => PHP_VERSION,
                'darkheim' => __CMS_VERSION__,
            ], 200);
        } catch (\Exception $ex) {
            JsonResponse::send(['code' => 500, 'error' => $ex->getMessage()], 500);
        }
    }

    private function apacheVersion(): string
    {
        if (function_exists('apache_get_version')) {
            return (string) apache_get_version();
        }

        return (string) ($_SERVER['SERVER_SOFTWARE'] ?? '');
    }
}

