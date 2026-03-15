<?php

declare(strict_types=1);

namespace Tests\Unit\Application\News;

use Darkheim\Application\News\NewsItem;
use PHPUnit\Framework\TestCase;

class NewsItemTest extends TestCase
{
    private NewsItem $item;

    protected function setUp(): void
    {
        $this->item = new NewsItem(
            id:    42,
            title: 'Default Title',
            author: 'Admin',
            date:   1700000000,
            translations: [
                'fr' => base64_encode('Titre Français'),
                'de' => base64_encode(''),    // empty decode — should fall back
                'ru' => 'not-base64!!!@@@',   // invalid base64 — should fall back
            ],
        );
    }

    public function testProperties(): void
    {
        $this->assertSame(42, $this->item->id);
        $this->assertSame('Default Title', $this->item->title);
        $this->assertSame('Admin', $this->item->author);
        $this->assertSame(1700000000, $this->item->date);
    }

    public function testTitleForLanguageEmptyStringReturnsDefault(): void
    {
        $this->assertSame('Default Title', $this->item->titleForLanguage(''));
    }

    public function testTitleForLanguageMissingKeyReturnsDefault(): void
    {
        $this->assertSame('Default Title', $this->item->titleForLanguage('jp'));
    }

    public function testTitleForLanguageValidTranslation(): void
    {
        $this->assertSame('Titre Français', $this->item->titleForLanguage('fr'));
    }

    public function testTitleForLanguageEmptyDecodedFallsBack(): void
    {
        $this->assertSame('Default Title', $this->item->titleForLanguage('de'));
    }

    public function testUrlAppendsIdCorrectly(): void
    {
        $this->assertSame('https://example.com/news/42/', $this->item->url('https://example.com'));
        $this->assertSame('https://example.com/news/42/', $this->item->url('https://example.com/'));
    }
}

