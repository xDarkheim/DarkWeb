<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Auth;

use Darkheim\Application\Auth\SessionManager;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\ArraySessionStore;

class SessionManagerTest extends TestCase
{
    private function make(array $data = []): array
    {
        $store = new ArraySessionStore($data);
        return [new SessionManager($store), $store];
    }

    public function testIsAuthenticatedWhenAllKeysPresent(): void
    {
        [$sm] = $this->make(['valid' => true, 'userid' => 1, 'username' => 'test', 'timeout' => time()]);
        $this->assertTrue($sm->isAuthenticated());
    }

    public function testIsAuthenticatedReturnsFalseWhenKeyMissing(): void
    {
        [$sm] = $this->make();
        $this->assertFalse($sm->isAuthenticated());

        [$sm] = $this->make(['valid' => true, 'userid' => 1, 'username' => 'test']);
        $this->assertFalse($sm->isAuthenticated());
    }

    public function testUserId(): void
    {
        [$sm, $store] = $this->make();
        $this->assertNull($sm->userId());

        $store->set('userid', '7');
        $this->assertSame(7, $sm->userId());
    }

    public function testUsername(): void
    {
        [$sm, $store] = $this->make();
        $this->assertNull($sm->username());

        $store->set('username', 'darkheim');
        $this->assertSame('darkheim', $sm->username());
    }

    public function testLastActivity(): void
    {
        [$sm, $store] = $this->make();
        $this->assertSame(0, $sm->lastActivity());

        $store->set('timeout', '1700000000');
        $this->assertSame(1700000000, $sm->lastActivity());
    }

    public function testHasTimedOut(): void
    {
        [$sm, $store] = $this->make();
        $store->set('timeout', time() - 400);
        $this->assertTrue($sm->hasTimedOut(300));

        $store->set('timeout', time() - 100);
        $this->assertFalse($sm->hasTimedOut(300));
    }

    public function testRefreshTimeout(): void
    {
        [$sm, $store] = $this->make();
        $before       = time();
        $sm->refreshTimeout();
        $this->assertGreaterThanOrEqual($before, (int) $store->get('timeout'));
    }

    public function testStartAuthenticatedSessionStoresCanonicalKeys(): void
    {
        [$sm, $store] = $this->make();

        $sm->startAuthenticatedSession(7, 'darkheim');

        $this->assertTrue($store->has('valid'));
        $this->assertSame(7, $store->get('userid'));
        $this->assertSame('darkheim', $store->get('username'));
        $this->assertIsInt($store->get('timeout'));
    }

    public function testClearSession(): void
    {
        [$sm, $store] = $this->make(['valid' => true, 'userid' => 1, 'username' => 'test', 'timeout' => time()]);
        $sm->clearSession();
        $this->assertSame([], $store->all());
    }
}
