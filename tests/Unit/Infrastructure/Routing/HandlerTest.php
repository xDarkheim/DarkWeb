<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\Handler;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\ArrayQueryStore;
use Tests\Stubs\ArraySessionStore;

class HandlerTest extends TestCase
{
    private Handler $handler;
    private ArraySessionStore $session;

    protected function setUp(): void
    {
        $this->session = new ArraySessionStore();
        $this->handler = new Handler($this->session, new ArrayQueryStore());
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
        // Create a language fixture file
        $langDir = __PATH_LANGUAGES__ . 'en/';
        @mkdir($langDir, 0777, true);
        file_put_contents($langDir . 'language.php', '<?php $lang = [];');

        $result = $this->handler->switchLanguage('en');
        $this->assertTrue($result);
        $this->assertSame('en', $this->session->get('language_display'));

        @unlink($langDir . 'language.php');
        @rmdir($langDir);
    }
}
