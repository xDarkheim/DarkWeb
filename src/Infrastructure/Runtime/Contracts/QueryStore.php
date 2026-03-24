<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Runtime\Contracts;

interface QueryStore
{
    public function has(string $key): bool;

    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): void;
}
