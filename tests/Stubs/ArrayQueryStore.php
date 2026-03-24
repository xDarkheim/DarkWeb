<?php

declare(strict_types=1);

namespace Tests\Stubs;

use Darkheim\Infrastructure\Runtime\Contracts\QueryStore;

final class ArrayQueryStore implements QueryStore
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(private array $data = []) {}

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }
}
