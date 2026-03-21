<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\View;

/**
 * Renders a named view template.
 *
 * Template lookup order (first match wins):
 *   1. public/themes/{activeTheme}/views/{template}.php   — optional theme override
 *   2. views/{template}.php                               — permanent, theme-agnostic default
 *
 * This separation keeps HTML module templates in one permanent place (views/)
 * so frontend developers write each template once.
 * A theme only needs to supply overrides when it intentionally changes a module's markup.
 *
 * Variables passed in $data are extracted into the template scope (EXTR_SKIP).
 */
final class ViewRenderer
{
    private string $theme;
    private ?string $themesPath;
    private ?string $viewsPath;

    /**
     * @param string|null $theme      Active theme name; defaults to config('website_theme').
     * @param string|null $themesPath Absolute path to themes/ dir (trailing slash). Defaults to __PATH_THEMES__.
     * @param string|null $viewsPath  Absolute path to views/  dir (trailing slash). Defaults to __PATH_VIEWS__.
     *                                Inject custom paths in tests to avoid redefining constants.
     */
    public function __construct(
        ?string $theme      = null,
        ?string $themesPath = null,
        ?string $viewsPath  = null,
    ) {
        $this->theme      = $theme      ?? (string) config('website_theme', true);
        $this->themesPath = $themesPath;
        $this->viewsPath  = $viewsPath;
    }

    /**
     * @param array<string, mixed> $data Variables to expose inside the template.
     * @throws \RuntimeException When the template cannot be found.
     */
    public function render(string $template, array $data = []): void
    {
        $file = $this->resolve($template);

        if ($file === null) {
            throw new \RuntimeException(
                "View template '{$template}' not found. "
                . "Checked theme override and views/ directory."
            );
        }

        if ($data !== []) {
            extract($data, EXTR_SKIP);
        }

        include $file;
    }

    private function resolve(string $template): ?string
    {
        $tpl = $template . '.php';

        // 1. Optional theme override: public/themes/{theme}/views/{template}.php
        $themesBase = $this->themesPath ?? (defined('__PATH_THEMES__') ? __PATH_THEMES__ : null);
        if ($themesBase !== null) {
            $override = $themesBase . $this->theme . '/views/' . $tpl;
            if (is_file($override)) {
                return $override;
            }
        }

        // 2. Permanent default: views/{template}.php
        $viewsBase = $this->viewsPath ?? (defined('__PATH_VIEWS__') ? __PATH_VIEWS__ : null);
        if ($viewsBase !== null) {
            $default = $viewsBase . $tpl;
            if (is_file($default)) {
                return $default;
            }
        }

        return null;
    }
}
