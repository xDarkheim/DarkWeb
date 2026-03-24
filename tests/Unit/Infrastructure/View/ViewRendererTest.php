<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\View;

use Darkheim\Infrastructure\View\ViewRenderer;
use PHPUnit\Framework\TestCase;

final class ViewRendererTest extends TestCase
{
    private string $tmpDir;
    private string $themesDir;
    private string $viewsDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/darkcore_view_test_' . uniqid(
            '',
            true,
        );
        $this->themesDir = $this->tmpDir . '/themes/';
        $this->viewsDir  = $this->tmpDir . '/views/';

        mkdir($this->themesDir . 'myTheme/views', 0o777, true);
        mkdir($this->themesDir . 'default/views', 0o777, true);
        mkdir($this->viewsDir, 0o777, true);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursive($this->tmpDir);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function renderer(string $theme = 'myTheme'): ViewRenderer
    {
        return new ViewRenderer($theme, $this->themesDir, $this->viewsDir);
    }

    private function writeView(string $name, string $content): void
    {
        file_put_contents($this->viewsDir . $name . '.php', $content);
    }

    private function writeThemeOverride(string $theme, string $name, string $content): void
    {
        file_put_contents($this->themesDir . $theme . '/views/' . $name . '.php', $content);
    }

    private function capture(ViewRenderer $renderer, string $template, array $data = []): string
    {
        ob_start();
        try {
            $renderer->render($template, $data);
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }

    private function rmdirRecursive(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir . '/' . $entry;
            is_dir($path) ? $this->rmdirRecursive($path) : unlink($path);
        }
        rmdir($dir);
    }

    // ------------------------------------------------------------------
    // Tests
    // ------------------------------------------------------------------

    public function testRendersTemplateFromViewsDirectory(): void
    {
        $this->writeView('hello', '<p><?php echo $greeting; ?></p>');

        $output = $this->capture($this->renderer(), 'hello', ['greeting' => 'world']);

        $this->assertSame('<p>world</p>', $output);
    }

    public function testThemeOverrideHasPriorityOverViewsDirectory(): void
    {
        $this->writeView('shared', 'from-views');
        $this->writeThemeOverride('myTheme', 'shared', 'from-theme-override');

        $output = $this->capture($this->renderer(), 'shared');

        $this->assertSame('from-theme-override', $output);
    }

    public function testFallsBackToViewsWhenNoThemeOverrideExists(): void
    {
        $this->writeView('noOverride', 'from-views');
        // No theme override for 'noOverride'

        $output = $this->capture($this->renderer(), 'noOverride');

        $this->assertSame('from-views', $output);
    }

    public function testThrowsWhenTemplateNotFoundAnywhere(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/missing/');

        $this->renderer()->render('missing');
    }

    public function testDataIsExtractedIntoTemplateScope(): void
    {
        $this->writeView('vars', '<?php echo $a . $b; ?>');

        $output = $this->capture($this->renderer(), 'vars', ['a' => 'foo', 'b' => 'bar']);

        $this->assertSame('foobar', $output);
    }

    public function testExtrSkipPreventsOverwritingExistingLocals(): void
    {
        $this->writeView('noop', '');
        $renderer = $this->renderer();

        // Passing 'renderer' key must not overwrite local $renderer via EXTR_SKIP.
        $this->capture($renderer, 'noop', ['renderer' => 'injected-string']);

        $this->assertInstanceOf(ViewRenderer::class, $renderer);
    }

    public function testDifferentThemesCanHaveDifferentOverrides(): void
    {
        $this->writeView('page', 'default-view');
        $this->writeThemeOverride('myTheme', 'page', 'myTheme-view');

        mkdir($this->themesDir . 'otherTheme/views', 0o777, true);
        // otherTheme has no override → falls back to views/

        $outMy    = $this->capture(new ViewRenderer('myTheme', $this->themesDir, $this->viewsDir), 'page');
        $outOther = $this->capture(new ViewRenderer('otherTheme', $this->themesDir, $this->viewsDir), 'page');

        $this->assertSame('myTheme-view', $outMy);
        $this->assertSame('default-view', $outOther);
    }
}
