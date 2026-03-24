<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Auth;

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Auth\Login;
use Darkheim\Infrastructure\Database\dB;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\DbTestHelper;
use Tests\Stubs\RedirectException;

class LoginTest extends TestCase
{
    use DbTestHelper;

    private function makeLogin(dB $mockDb, Common $mockCommon, array $config = []): Login
    {
        /** @var Login $sut */
        $sut = new \ReflectionClass(Login::class)->newInstanceWithoutConstructor();
        $this->setProp($sut, 'muonline', $mockDb);
        $this->setProp($sut, 'common', $mockCommon);
        $this->setProp($sut, '_config', array_merge([
            'max_login_attempts'   => 5,
            'failed_login_timeout' => 30,
        ], $config));
        return $sut;
    }

    private function makeCommon(dB $mockDb): Common
    {
        /** @var Common $common */
        $common = $this->makeWithDb(Common::class, $mockDb);
        $this->setProp($common, '_passwordEncryption', 'phpmd5');
        $this->setProp($common, '_sha256salt', 'salt');
        $this->setProp($common, '_debug', false);
        return $common;
    }

    // ── canLogin ─────────────────────────────────────────────────────────────

    public function testCanLoginReturnsTrueWhenBelowThreshold(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn(['failed_attempts' => 2]);

        $sut = $this->makeLogin($db, $this->makeCommon($db));
        $this->assertTrue($sut->canLogin('127.0.0.1'));
    }

    public function testCanLoginReturnsNullForInvalidIp(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->makeLogin($db, $this->makeCommon($db));
        $this->assertNull($sut->canLogin('not-an-ip'));
    }

    public function testCanLoginReturnsNullWhenLocked(): void
    {
        $db = $this->createMock(dB::class);
        // first call: checkFailedLogins → returns 5 attempts (at threshold)
        // second call: canLogin → locked record with future timestamp
        $db->method('query_fetch_single')->willReturnOnConsecutiveCalls(
            ['failed_attempts' => 5],
            ['ip_address' => '127.0.0.1', 'unlock_timestamp' => time() + 3600],
        );
        $sut = $this->makeLogin($db, $this->makeCommon($db));
        $this->assertNull($sut->canLogin('127.0.0.1'));
    }

    // ── checkFailedLogins ────────────────────────────────────────────────────

    public function testCheckFailedLoginsReturnsCount(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn(['failed_attempts' => 3]);
        $sut = $this->makeLogin($db, $this->makeCommon($db));
        $this->assertSame(3, $sut->checkFailedLogins('127.0.0.1'));
    }

    public function testCheckFailedLoginsReturnsNullForInvalidIp(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->makeLogin($db, $this->makeCommon($db));
        $this->assertNull($sut->checkFailedLogins('bad-ip'));
    }

    // ── removeFailedLogins ───────────────────────────────────────────────────

    public function testRemoveFailedLoginsCallsDeleteQuery(): void
    {
        $db = $this->createMock(dB::class);
        $db->expects($this->once())->method('query')->willReturn(true);
        $sut = $this->makeLogin($db, $this->makeCommon($db));
        $sut->removeFailedLogins('127.0.0.1');
    }

    public function testRemoveFailedLoginsDoesNothingForInvalidIp(): void
    {
        $db = $this->createMock(dB::class);
        $db->expects($this->never())->method('query');
        $sut = $this->makeLogin($db, $this->makeCommon($db));
        $sut->removeFailedLogins('bad');
    }

    // ── logout ───────────────────────────────────────────────────────────────

    public function testLogoutClearsSessionAndRedirects(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION = ['valid' => true];

        $db  = $this->createMock(dB::class);
        $sut = $this->makeLogin($db, $this->makeCommon($db));

        $this->expectException(RedirectException::class);
        $sut->logout();
    }
}
