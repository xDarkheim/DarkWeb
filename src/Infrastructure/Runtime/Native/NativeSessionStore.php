<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Runtime\Native;

use Darkheim\Infrastructure\Runtime\Contracts\SessionStore;

final class NativeSessionStore implements SessionStore
{
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function clear(): void
    {
        $_SESSION = [];
    }
}
