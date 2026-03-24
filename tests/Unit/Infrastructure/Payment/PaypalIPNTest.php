<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Payment;

use Darkheim\Infrastructure\Payment\PaypalIPN;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\FixedPostStore;

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

    public function testVerifyIPNThrowsWhenInjectedPostStoreIsEmpty(): void
    {
        $ipn = new PaypalIPN(new FixedPostStore(0));
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing POST Data');
        $ipn->verifyIPN();
    }

    public function testDefaultCertPathUsesBundledCertificateWhenAvailable(): void
    {
        $ipn = new PaypalIPN();

        $resolver = \Closure::bind(
            function (): string {
                return $this->cert_path;
            },
            $ipn,
            PaypalIPN::class,
        );

        $certPath = $resolver();

        $this->assertIsString($certPath);
        $this->assertFileExists($certPath);
        $this->assertStringEndsWith('/includes/paypal/cert/cacert.pem', $certPath);
    }
}
