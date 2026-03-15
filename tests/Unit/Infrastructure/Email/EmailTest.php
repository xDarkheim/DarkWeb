<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Email;

use Darkheim\Infrastructure\Email\Email;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class EmailTest extends TestCase
{
    private function makeEmail(array $overrides = []): Email
    {
        $sut = new \ReflectionClass(Email::class)->newInstanceWithoutConstructor();

        $defaults = [
            '_active'    => false,
            '_smtp'      => false,
            '_from'      => 'noreply@example.com',
            '_name'      => 'Test Server',
            '_templates' => ['welcome' => 'Welcome to {SERVER_NAME}'],
            '_to'        => [],
            '_variables' => [],
            '_values'    => [],
            '_subject'   => null,
            '_message'   => null,
            '_template'  => null,
            '_replyTo'   => null,
            '_isCustomTemplate' => false,
        ];

        foreach (array_merge($defaults, $overrides) as $prop => $value) {
            $rp = new ReflectionProperty(Email::class, $prop);
            $rp->setValue($sut, $value);
        }

        return $sut;
    }

    // ── setTemplate ──────────────────────────────────────────────────────────

    public function testSetTemplateKnownTemplate(): void
    {
        $sut = $this->makeEmail();
        $sut->setTemplate('welcome');
        $this->assertTrue(true); // no exception
    }

    public function testSetTemplateUnknownThrows(): void
    {
        $sut = $this->makeEmail();
        $this->expectException(\Exception::class);
        $sut->setTemplate('unknown_template');
    }

    // ── addAddress ───────────────────────────────────────────────────────────

    public function testAddAddressValidEmail(): void
    {
        $sut = $this->makeEmail();
        $sut->addAddress('user@example.com');
        $rp     = new ReflectionProperty(Email::class, '_to');
        $toList = $rp->getValue($sut);
        $this->assertContains('user@example.com', $toList);
    }

    public function testAddAddressInvalidEmailThrows(): void
    {
        $sut = $this->makeEmail();
        $this->expectException(\Exception::class);
        $sut->addAddress('notanemail');
    }

    public function testSetReplyToValidEmail(): void
    {
        $sut = $this->makeEmail();
        $sut->setReplyTo('reply@example.com', 'Reply Name');
        $rp = new ReflectionProperty(Email::class, '_replyTo');
        $this->assertSame(['reply@example.com', 'Reply Name'], $rp->getValue($sut));
    }

    public function testSetReplyToInvalidEmailThrows(): void
    {
        $sut = $this->makeEmail();
        $this->expectException(\Exception::class);
        $sut->setReplyTo('notanemail');
    }

    // ── addVariable ──────────────────────────────────────────────────────────

    public function testAddVariableAppends(): void
    {
        $sut = $this->makeEmail();
        $sut->addVariable('{NAME}', 'Player1');
        $rvars = new ReflectionProperty(Email::class, '_variables');
        $rvals = new ReflectionProperty(Email::class, '_values');
        $this->assertContains('{NAME}', $rvars->getValue($sut));
        $this->assertContains('Player1', $rvals->getValue($sut));
    }

    // ── send: inactive throws ────────────────────────────────────────────────

    public function testSendThrowsWhenNotActive(): void
    {
        $sut = $this->makeEmail(['_active' => false]);
        $this->expectException(\Exception::class);
        $sut->send();
    }

    // ── send: no message and no template ─────────────────────────────────────

    public function testSendThrowsWhenNoMessageAndNoTemplate(): void
    {
        $sut = $this->makeEmail(['_active' => true]);
        $this->expectException(\Exception::class);
        $sut->send();
    }
}

