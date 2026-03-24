<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Runtime\Native;

use Darkheim\Infrastructure\Runtime\Contracts\PostStore;

final class NativePostStore implements PostStore
{
    public function has(string $key): bool
    {
        return array_key_exists($key, $_POST);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function count(): int
    {
        return count($_POST);
    }
}
