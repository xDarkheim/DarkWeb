<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Runtime\Native;

use Darkheim\Infrastructure\Runtime\Contracts\QueryStore;

final class NativeQueryStore implements QueryStore
{
    public function has(string $key): bool
    {
        return array_key_exists($key, $_GET);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_GET[$key] = $value;
    }
}
