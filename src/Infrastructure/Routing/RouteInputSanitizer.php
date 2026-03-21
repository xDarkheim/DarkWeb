<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

final class RouteInputSanitizer
{
    public function sanitize(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        return preg_replace('/[^a-zA-Z0-9\s\/]/', '', $input);
    }
}

