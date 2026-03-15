<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Database;

use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\Database\DatabaseFactory;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function testDatabaseUnknownNameReturnsNull(): void
    {
        $this->assertNull(Connection::Database('UnknownDB'));
    }

    public function testDatabaseMuOnlineThrowsWhenConfigPointsToBadHost(): void
    {
        // The bootstrap cmsConfigs() returns a bad host ('127.0.0.1:1433')
        // which causes dB->dead = true → Connection::Database throws
        $this->expectException(\Exception::class);
        Connection::Database('MuOnline');
    }
}

class DatabaseFactoryTest extends TestCase
{
    public function testMuOnlineThrowsWhenConnectionFails(): void
    {
        $this->expectException(\Exception::class);
        DatabaseFactory::muOnline();
    }
}

