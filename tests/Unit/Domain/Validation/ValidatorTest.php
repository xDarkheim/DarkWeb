<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Validation;

use Darkheim\Domain\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    // ── Email ────────────────────────────────────────────────────────────────

    public function testEmailValid(): void
    {
        $this->assertTrue(Validator::Email('user@example.com'));
        $this->assertTrue(Validator::Email('user.name@sub.domain.org'));
    }

    public function testEmailInvalidFormat(): void
    {
        $this->assertFalse(Validator::Email('notanemail'));
        $this->assertFalse(Validator::Email('missing@'));
        $this->assertFalse(Validator::Email('@nodomain.com'));
    }

    public function testEmailExcludeString(): void
    {
        $this->assertFalse(Validator::Email('user@spam.com', 'spam'));
    }

    public function testEmailExcludeArray(): void
    {
        $this->assertFalse(Validator::Email('user@banned.com', ['banned', 'trash']));
        $this->assertTrue(Validator::Email('user@good.com', ['banned', 'trash']));
    }

    // ── Url ──────────────────────────────────────────────────────────────────

    public function testUrlValidHttp(): void
    {
        $this->assertTrue(Validator::Url('http://example.com'));
        $this->assertTrue(Validator::Url('https://example.com/path'));
        $this->assertTrue(Validator::Url('ftp://files.example.com'));
    }

    public function testUrlInvalid(): void
    {
        $this->assertFalse(Validator::Url('example.com'));
        $this->assertFalse(Validator::Url('just text'));
    }

    public function testUrlExclude(): void
    {
        $this->assertFalse(Validator::Url('http://blocked.com', 'blocked'));
    }

    // ── Ip ───────────────────────────────────────────────────────────────────

    public function testIpValidIPv4(): void
    {
        $this->assertTrue(Validator::Ip('192.168.1.1'));
        $this->assertTrue(Validator::Ip('127.0.0.1'));
    }

    public function testIpValidIPv6(): void
    {
        $this->assertTrue(Validator::Ip('::1'));
        $this->assertTrue(Validator::Ip('2001:db8::1'));
    }

    public function testIpInvalid(): void
    {
        $this->assertFalse(Validator::Ip('999.999.999.999'));
        $this->assertFalse(Validator::Ip('not-an-ip'));
    }

    // ── Number ───────────────────────────────────────────────────────────────

    public function testNumberValid(): void
    {
        $this->assertTrue(Validator::Number(5));
        $this->assertTrue(Validator::Number(5, 10, 1));
    }

    public function testNumberBelowMin(): void
    {
        $this->assertFalse(Validator::Number(0, 10, 1));
    }

    public function testNumberAboveMax(): void
    {
        $this->assertFalse(Validator::Number(11, 10));
    }

    public function testNumberNonNumeric(): void
    {
        $this->assertFalse(Validator::Number('abc'));
    }

    // ── UnsignedNumber ───────────────────────────────────────────────────────

    public function testUnsignedNumberValid(): void
    {
        $this->assertTrue(Validator::UnsignedNumber(0));
        $this->assertTrue(Validator::UnsignedNumber(42));
        $this->assertTrue(Validator::UnsignedNumber('100'));
    }

    public function testUnsignedNumberNegative(): void
    {
        $this->assertFalse(Validator::UnsignedNumber(-1));
    }

    public function testUnsignedNumberFloat(): void
    {
        $this->assertFalse(Validator::UnsignedNumber(1.5));
    }

    // ── Float ────────────────────────────────────────────────────────────────

    public function testFloatValid(): void
    {
        $this->assertTrue(Validator::Float(1.5));
        $this->assertTrue(Validator::Float('3.14'));
    }

    public function testFloatInvalid(): void
    {
        $this->assertFalse(Validator::Float('abc'));
    }

    // ── Alpha ────────────────────────────────────────────────────────────────

    public function testAlpha(): void
    {
        $this->assertTrue(Validator::Alpha('HelloWorld'));
        $this->assertFalse(Validator::Alpha('Hello1'));
        $this->assertFalse(Validator::Alpha(''));
    }

    // ── AlphaNumeric ─────────────────────────────────────────────────────────

    public function testAlphaNumeric(): void
    {
        $this->assertTrue(Validator::AlphaNumeric('Test123'));
        $this->assertFalse(Validator::AlphaNumeric('Test 123'));
        $this->assertFalse(Validator::AlphaNumeric('Test_123'));
    }

    // ── Chars ────────────────────────────────────────────────────────────────

    public function testCharsDefault(): void
    {
        $this->assertTrue(Validator::Chars('abc'));
        $this->assertFalse(Validator::Chars('ABC'));
    }

    public function testCharsCustomAllowed(): void
    {
        $this->assertTrue(Validator::Chars('ABC123', ['A-Z', '0-9']));
        $this->assertFalse(Validator::Chars('abc', ['A-Z', '0-9']));
    }

    // ── Length ───────────────────────────────────────────────────────────────

    public function testLengthValid(): void
    {
        $this->assertTrue(Validator::Length('hello', 10, 1));
    }

    public function testLengthTooShort(): void
    {
        $this->assertFalse(Validator::Length('', 10, 1));
    }

    public function testLengthTooLong(): void
    {
        $this->assertFalse(Validator::Length('toolongstring', 5));
    }

    // ── Date ─────────────────────────────────────────────────────────────────

    public function testDateValid(): void
    {
        $this->assertTrue(Validator::Date('2024-01-15'));
        $this->assertTrue(Validator::Date('January 1, 2024'));
    }

    public function testDateInvalid(): void
    {
        $this->assertFalse(Validator::Date('not-a-date'));
        $this->assertFalse(Validator::Date('0'));
    }

    // ── UsernameLength / PasswordLength ──────────────────────────────────────
    // These read limits via BootstrapContext::cmsValue(); tests bootstrap default min/max values.

    public function testUsernameLengthValid(): void
    {
        $this->assertTrue(Validator::UsernameLength('testuser'));
    }

    public function testUsernameLengthTooShort(): void
    {
        $this->assertFalse(Validator::UsernameLength('ab'));
    }

    public function testUsernameLengthTooLong(): void
    {
        $this->assertFalse(Validator::UsernameLength('averylongusername'));
    }

    public function testPasswordLengthValid(): void
    {
        $this->assertTrue(Validator::PasswordLength('pass1234'));
    }

    public function testPasswordLengthTooShort(): void
    {
        $this->assertFalse(Validator::PasswordLength('ab'));
    }

    public function testPasswordLengthTooLong(): void
    {
        $this->assertFalse(Validator::PasswordLength('averylongpasswordthatexceeds'));
    }
}
