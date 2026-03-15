<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\Handler;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class HandlerTest extends TestCase
{
    private Handler $handler;

    protected function setUp(): void
    {
        $this->handler = new Handler();
    }

    // ── cleanRequest ─────────────────────────────────────────────────────────

    private function cleanRequest(?string $input): ?string
    {
        $m = new ReflectionMethod(Handler::class, 'cleanRequest');
        return $m->invoke($this->handler, $input);
    }

    public function testCleanRequestNullReturnsNull(): void
    {
        $this->assertNull($this->cleanRequest(null));
    }

    public function testCleanRequestStripsSpecialChars(): void
    {
        $this->assertSame('hello', $this->cleanRequest('hello!@#'));
        $this->assertSame('usercp', $this->cleanRequest('usercp'));
    }

    public function testCleanRequestAllowsSlash(): void
    {
        $this->assertSame('usercp/myprofile', $this->cleanRequest('usercp/myprofile'));
    }

    public function testCleanRequestAllowsAlphanumeric(): void
    {
        $this->assertSame('Page1', $this->cleanRequest('Page1'));
    }

    public function testCleanRequestRemovesHyphensAndUnderscores(): void
    {
        $result = $this->cleanRequest('my-page_test');
        $this->assertSame('mypagetest', $result);
    }

    // ── switchLanguage ───────────────────────────────────────────────────────

    public function testSwitchLanguageReturnsFalseForEmpty(): void
    {
        $this->assertFalse($this->handler->switchLanguage(''));
    }

    public function testSwitchLanguageReturnsFalseForNonExistentLanguage(): void
    {
        $this->assertFalse($this->handler->switchLanguage('zz'));
    }

    public function testSwitchLanguageReturnsTrueAndSetsSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) @session_start();

        // Create a language fixture file
        $langDir = __PATH_LANGUAGES__ . 'en/';
        @mkdir($langDir, 0777, true);
        file_put_contents($langDir . 'language.php', '<?php $lang = [];');

        $result = $this->handler->switchLanguage('en');
        $this->assertTrue($result);
        $this->assertSame('en', $_SESSION['language_display'] ?? null);

        @unlink($langDir . 'language.php');
        @rmdir($langDir);
    }
}

