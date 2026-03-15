<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Auth;

use Darkheim\Application\Auth\AuthService;
use Darkheim\Application\Auth\SessionManager;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\RedirectException;

class AuthServiceTest extends TestCase
{
    public function testSessionReturnsInjectedManager(): void
    {
        $sm      = new SessionManager();
        $service = new AuthService($sm);
        $this->assertSame($sm, $service->session());
    }

    public function testLogoutClearsSessionAndRedirects(): void
    {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $_SESSION = ['valid' => true, 'userid' => 1, 'username' => 'test', 'timeout' => time()];

        $service = new AuthService();
        $this->expectException(RedirectException::class);
        $service->logout();
    }

    public function testSessionDefaultsToNewSessionManager(): void
    {
        $service = new AuthService();
        $this->assertInstanceOf(SessionManager::class, $service->session());
    }
}

