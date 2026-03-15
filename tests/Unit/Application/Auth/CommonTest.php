<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Auth;

use Darkheim\Application\Auth\Common;
use Darkheim\Infrastructure\Database\dB;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\DbTestHelper;

class CommonTest extends TestCase
{
    use DbTestHelper;

    private function make(dB $mockDb): Common
    {
        /** @var Common $sut */
        $sut = $this->makeWithDb(Common::class, $mockDb);
        $this->setProp($sut, '_passwordEncryption', 'phpmd5');
        $this->setProp($sut, '_sha256salt', 'salt');
        $this->setProp($sut, '_debug', false);
        return $sut;
    }

    // ── emailExists ──────────────────────────────────────────────────────────

    public function testEmailExistsReturnsTrueWhenFound(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn(['mail_addr' => 'a@b.com']);
        $sut = $this->make($db);
        $this->assertTrue($sut->emailExists('user@example.com'));
    }

    public function testEmailExistsReturnsNullWhenNotFound(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn(null);
        $sut = $this->make($db);
        $this->assertNull($sut->emailExists('user@example.com'));
    }

    public function testEmailExistsReturnsNullForInvalidEmail(): void
    {
        $db  = $this->createMock(dB::class);
        $db->expects($this->never())->method('query_fetch_single');
        $sut = $this->make($db);
        $this->assertNull($sut->emailExists('notanemail'));
    }

    // ── userExists ───────────────────────────────────────────────────────────

    public function testUserExistsReturnsTrueWhenFound(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn(['memb___id' => 'testuser']);
        $sut = $this->make($db);
        $this->assertTrue($sut->userExists('testuser'));
    }

    public function testUserExistsReturnsNullForShortUsername(): void
    {
        $db  = $this->createMock(dB::class);
        $db->expects($this->never())->method('query_fetch_single');
        $sut = $this->make($db);
        $this->assertNull($sut->userExists('ab'));
    }

    public function testUserExistsReturnsNullForNonAlphanumeric(): void
    {
        $db  = $this->createMock(dB::class);
        $db->expects($this->never())->method('query_fetch_single');
        $sut = $this->make($db);
        $this->assertNull($sut->userExists('user name'));
    }

    // ── accountInformation ───────────────────────────────────────────────────

    public function testAccountInformationReturnsArrayWhenFound(): void
    {
        $row = ['memb_guid' => 42, 'memb___id' => 'testuser'];
        $db  = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn($row);
        $sut = $this->make($db);
        $this->assertSame($row, $sut->accountInformation(42));
    }

    public function testAccountInformationReturnsNullForNonNumericId(): void
    {
        $db  = $this->createMock(dB::class);
        $db->expects($this->never())->method('query_fetch_single');
        $sut = $this->make($db);
        $this->assertNull($sut->accountInformation('notanumber'));
    }

    // ── accountOnline ────────────────────────────────────────────────────────

    public function testAccountOnlineReturnsTrueWhenOnline(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn(['ConnectStat' => 1]);
        $sut = $this->make($db);
        $this->assertTrue($sut->accountOnline('testuser'));
    }

    public function testAccountOnlineReturnsNullWhenOffline(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn(null);
        $sut = $this->make($db);
        $this->assertNull($sut->accountOnline('testuser'));
    }

    // ── generateAccountRecoveryCode ──────────────────────────────────────────

    public function testGenerateAccountRecoveryCode(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $expected = md5(md5('5') . md5('testuser'));
        $this->assertSame($expected, $sut->generateAccountRecoveryCode('5', 'testuser'));
    }

    // ── generatePasswordChangeVerificationURL ────────────────────────────────

    public function testGeneratePasswordChangeVerificationURL(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $url = $sut->generatePasswordChangeVerificationURL(7, 'abc123');
        $this->assertStringContainsString('verifyemail', $url);
        $this->assertStringContainsString('uid=7', $url);
        $this->assertStringContainsString('key=abc123', $url);
    }

    // ── updateEmail ──────────────────────────────────────────────────────────

    public function testUpdateEmailReturnsTrueOnSuccess(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query')->willReturn(true);
        $sut = $this->make($db);
        $this->assertTrue($sut->updateEmail(1, 'new@example.com'));
    }

    public function testUpdateEmailReturnsNullForInvalidEmail(): void
    {
        $db  = $this->createMock(dB::class);
        $db->expects($this->never())->method('query');
        $sut = $this->make($db);
        $this->assertNull($sut->updateEmail(1, 'bademail'));
    }

    // ── validateUser ─────────────────────────────────────────────────────────

    public function testValidateUserReturnsTrueWhenCredentialsMatch(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn(['memb___id' => 'testuser']);
        $sut = $this->make($db);
        $this->assertTrue($sut->validateUser('testuser', 'pass1234'));
    }

    public function testValidateUserReturnsFalseWhenNotFound(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn(null);
        $sut = $this->make($db);
        $this->assertFalse($sut->validateUser('testuser', 'pass1234'));
    }

    public function testValidateUserReturnsNullForTooShortPassword(): void
    {
        $db  = $this->createMock(dB::class);
        $db->expects($this->never())->method('query_fetch_single');
        $sut = $this->make($db);
        $this->assertNull($sut->validateUser('testuser', 'ab'));
    }

    // ── removePasswordChangeRequest ──────────────────────────────────────────

    public function testRemovePasswordChangeRequestReturnsTrueOnSuccess(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query')->willReturn(true);
        $sut = $this->make($db);
        $this->assertTrue($sut->removePasswordChangeRequest(1));
    }
}

