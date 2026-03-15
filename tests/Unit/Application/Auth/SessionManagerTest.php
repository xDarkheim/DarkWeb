<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Auth;

use Darkheim\Application\Auth\SessionManager;
use PHPUnit\Framework\TestCase;

class SessionManagerTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testIsAuthenticatedWhenAllKeysPresent(): void
    {
        $_SESSION = ['valid' => true, 'userid' => 1, 'username' => 'test', 'timeout' => time()];
        $sm = new SessionManager();
        $this->assertTrue($sm->isAuthenticated());
    }

    public function testIsAuthenticatedReturnsFalseWhenKeyMissing(): void
    {
        $sm = new SessionManager();
        $this->assertFalse($sm->isAuthenticated());

        $_SESSION = ['valid' => true, 'userid' => 1, 'username' => 'test'];
        $this->assertFalse($sm->isAuthenticated());
    }

    public function testUserId(): void
    {
        $sm = new SessionManager();
        $this->assertNull($sm->userId());

        $_SESSION['userid'] = '7';
        $this->assertSame(7, $sm->userId());
    }

    public function testUsername(): void
    {
        $sm = new SessionManager();
        $this->assertNull($sm->username());

        $_SESSION['username'] = 'darkheim';
        $this->assertSame('darkheim', $sm->username());
    }

    public function testLastActivity(): void
    {
        $sm = new SessionManager();
        $this->assertSame(0, $sm->lastActivity());

        $_SESSION['timeout'] = '1700000000';
        $this->assertSame(1700000000, $sm->lastActivity());
    }

    public function testHasTimedOut(): void
    {
        $sm = new SessionManager();
        $_SESSION['timeout'] = time() - 400;
        $this->assertTrue($sm->hasTimedOut(300));

        $_SESSION['timeout'] = time() - 100;
        $this->assertFalse($sm->hasTimedOut(300));
    }

    public function testRefreshTimeout(): void
    {
        $sm = new SessionManager();
        $before = time();
        $sm->refreshTimeout();
        $this->assertGreaterThanOrEqual($before, $_SESSION['timeout']);
    }

    public function testClearSession(): void
    {
        $_SESSION = ['valid' => true, 'userid' => 1, 'username' => 'test', 'timeout' => time()];
        $sm = new SessionManager();
        $sm->clearSession();
        $this->assertSame([], $_SESSION);
    }
}

