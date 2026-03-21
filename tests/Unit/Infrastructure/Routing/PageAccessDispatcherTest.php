<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\PageAccessDispatcher;
use PHPUnit\Framework\TestCase;

final class PageAccessDispatcherTest extends TestCase
{
    private PageAccessDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = new PageAccessDispatcher();
    }

    public function testDispatchThrowsForInvalidAccess(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Access forbidden.');

        $this->dispatcher->dispatch('unknown', 'default');
    }

    public function testDispatchThrowsWhenThemeIsMissingForIndexAccess(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The chosen theme cannot be loaded (missing-theme).');

        $this->dispatcher->dispatch('index', 'missing-theme');
    }

    public function testDispatchIncludesThemeEntryFileForIndexAccess(): void
    {
        $theme = '__route_test_theme';
        $themeDir = __PATH_THEMES__ . $theme;
        @mkdir($themeDir, 0777, true);
        file_put_contents($themeDir . '/index.php', "<?php \$GLOBALS['__page_access_theme_loaded'] = true;\n");

        $GLOBALS['__page_access_theme_loaded'] = false;
        $this->dispatcher->dispatch('index', $theme);

        $this->assertTrue((bool) $GLOBALS['__page_access_theme_loaded']);

        @unlink($themeDir . '/index.php');
        @rmdir($themeDir);
    }

    public function testDispatchAllowsNonIndexAccessModes(): void
    {
        $this->dispatcher->dispatch('api', 'default');
        $this->dispatcher->dispatch('cron', 'default');
        $this->dispatcher->dispatch('admincp', 'default');
        $this->dispatcher->dispatch('install', 'default');

        $this->assertTrue(true);
    }
}

