<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\Support\RouteInputSanitizer;
use PHPUnit\Framework\TestCase;

final class RouteInputSanitizerTest extends TestCase
{
    private RouteInputSanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new RouteInputSanitizer();
    }

    public function testSanitizeNullReturnsNull(): void
    {
        $this->assertNull($this->sanitizer->sanitize(null));
    }

    public function testSanitizeStripsSpecialChars(): void
    {
        $this->assertSame('hello', $this->sanitizer->sanitize('hello!@#'));
        $this->assertSame('usercp', $this->sanitizer->sanitize('usercp'));
    }

    public function testSanitizeAllowsSlash(): void
    {
        $this->assertSame('usercp/myprofile', $this->sanitizer->sanitize('usercp/myprofile'));
    }

    public function testSanitizeAllowsAlphanumeric(): void
    {
        $this->assertSame('Page1', $this->sanitizer->sanitize('Page1'));
    }

    public function testSanitizeRemovesHyphensAndUnderscores(): void
    {
        $this->assertSame('mypagetest', $this->sanitizer->sanitize('my-page_test'));
    }
}
