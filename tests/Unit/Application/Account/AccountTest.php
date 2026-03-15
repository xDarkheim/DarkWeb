<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Account;

use Darkheim\Application\Account\Account;
use Darkheim\Infrastructure\Database\dB;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\DbTestHelper;
use Tests\Stubs\RedirectException;

class AccountTest extends TestCase
{
    use DbTestHelper;

    private function make(dB $mockDb, string $encryption = 'phpmd5'): Account
    {
        /** @var Account $sut */
        $sut = $this->makeWithDb(Account::class, $mockDb);
        $this->setProp($sut, '_passwordEncryption', $encryption);
        $this->setProp($sut, '_sha256salt', 'testsalt');
        $this->setProp($sut, '_debug', false);
        return $sut;
    }

    // ── registerAccount validation ────────────────────────────────────────────

    public function testRegisterAccountThrowsForEmptyUsername(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->expectException(\Exception::class);
        $sut->registerAccount('', 'pass1234', 'pass1234', 'test@example.com');
    }

    public function testRegisterAccountThrowsForShortUsername(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->expectException(\Exception::class);
        $sut->registerAccount('ab', 'pass1234', 'pass1234', 'test@example.com');
    }

    public function testRegisterAccountThrowsForNonAlphanumericUsername(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->expectException(\Exception::class);
        $sut->registerAccount('user name', 'pass1234', 'pass1234', 'test@example.com');
    }

    public function testRegisterAccountThrowsForShortPassword(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->expectException(\Exception::class);
        $sut->registerAccount('testuser', 'ab', 'ab', 'test@example.com');
    }

    public function testRegisterAccountThrowsForPasswordMismatch(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->expectException(\Exception::class);
        $sut->registerAccount('testuser', 'pass1234', 'different', 'test@example.com');
    }

    public function testRegisterAccountThrowsForInvalidEmail(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->expectException(\Exception::class);
        $sut->registerAccount('testuser', 'pass1234', 'pass1234', 'not-an-email');
    }

    public function testRegisterAccountThrowsWhenUsernameExists(): void
    {
        $db = $this->createMock(dB::class);
        // userExists() → query_fetch_single returns a row
        $db->method('query_fetch_single')->willReturn(['memb___id' => 'testuser']);
        $sut = $this->make($db);
        $this->expectException(\Exception::class);
        $sut->registerAccount('testuser', 'pass1234', 'pass1234', 'new@example.com');
    }

    public function testRegisterAccountSuccessInsertsAndRedirects(): void
    {
        $db = $this->createMock(dB::class);
        // userExists and emailExists both return null (not found)
        $db->method('query_fetch_single')->willReturn(null);
        $db->method('query')->willReturn(true);
        $sut = $this->make($db);

        // loadConfigurations('register') returns null in bootstrap → no verify_email path
        // We expect either a redirect or the test to complete
        $this->expectException(RedirectException::class);
        $sut->registerAccount('newuser', 'pass1234', 'pass1234', 'new@example.com');
    }

    // ── changePasswordProcess ─────────────────────────────────────────────────

    public function testChangePasswordThrowsForEmptyUserId(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->expectException(\Exception::class);
        $sut->changePasswordProcess('', 'user', 'old', 'newpass1', 'newpass1');
    }
}

