<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Runtime\Support;

final class ServerContext
{
    public function remoteAddress(): ?string
    {
        $address = $_SERVER['REMOTE_ADDR'] ?? null;
        return is_string($address) ? $address : null;
    }
}
