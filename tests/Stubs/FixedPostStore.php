<?php

declare(strict_types=1);

namespace Tests\Stubs;

use Darkheim\Infrastructure\Runtime\Contracts\PostStore;

final class FixedPostStore implements PostStore
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(private int $count, private array $data = []) {}

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function count(): int
    {
        return $this->count;
    }
}
