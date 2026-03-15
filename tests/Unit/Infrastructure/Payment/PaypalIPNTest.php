<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Payment;

use Darkheim\Infrastructure\Payment\PaypalIPN;
use PHPUnit\Framework\TestCase;

class PaypalIPNTest extends TestCase
{
    public function testDefaultUriIsLive(): void
    {
        $ipn = new PaypalIPN();
        $this->assertSame(PaypalIPN::VERIFY_URI, $ipn->getPaypalUri());
    }

    public function testUseSandboxSwitchesUri(): void
    {
        $ipn = new PaypalIPN();
        $ipn->useSandbox();
        $this->assertSame(PaypalIPN::SANDBOX_VERIFY_URI, $ipn->getPaypalUri());
    }

    public function testConstants(): void
    {
        $this->assertSame('VERIFIED', PaypalIPN::VALID);
        $this->assertSame('INVALID', PaypalIPN::INVALID);
    }

    public function testVerifyIPNThrowsWhenNoPostData(): void
    {
        $_POST = [];
        $ipn   = new PaypalIPN();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing POST Data');
        $ipn->verifyIPN();
    }
}

