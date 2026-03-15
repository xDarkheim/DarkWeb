<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Database;

/**
 * PSR-4 factory wrapping Connection::Database() — both classes live in the same namespace.
 */
final class DatabaseFactory
{
    /**
     * @throws \Exception when connection fails
     */
    public static function muOnline(): dB
    {
        return Connection::Database('MuOnline');
    }
}
