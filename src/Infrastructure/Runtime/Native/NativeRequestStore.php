<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Runtime\Native;

use Darkheim\Infrastructure\Runtime\Contracts\RequestStore;

final class NativeRequestStore implements RequestStore
{
    public function has(string $key): bool
    {
        return array_key_exists($key, $_REQUEST);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_REQUEST[$key] ?? $default;
    }
}
