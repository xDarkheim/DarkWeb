<?php

declare(strict_types=1);

namespace Darkheim\Application\Language;

use Darkheim\Infrastructure\Bootstrap\BootstrapContext;

/**
 * Language translation service.
 *
 * Replaces the inline logic inside the global \Darkheim\Application\Language\Translator::phrase() / \Darkheim\Application\Language\Translator::phraseFmt() helpers.
 * Use those helpers for backward-compatible call-sites; call this class
 * directly in new code.
 */
final class Translator
{
    /**
     * Returns the translated phrase for $key.
     * When language_debug is enabled the result is wrapped in a <span>
     * whose title attribute exposes the phrase key.
     */
    public static function phrase(string $key): string
    {
        $phrases = BootstrapContext::runtimeState()?->languagePhrases() ?? [];
        $result  = array_key_exists($key, $phrases) ? (string) $phrases[$key] : 'ERROR';

        if (self::debugMode()) {
            return '<span title="' . htmlspecialchars($key, ENT_QUOTES) . '">' . $result . '</span>';
        }

        return $result;
    }

    /**
     * Returns a vsprintf-formatted translated phrase.
     * Falls back to 'ERROR' when the key is missing or formatting fails.
     */
    public static function phraseFmt(string $key, array $args): string
    {
        $phrases  = BootstrapContext::runtimeState()?->languagePhrases() ?? [];
        $template = array_key_exists($key, $phrases) ? (string) $phrases[$key] : null;
        $result   = ($template !== null) ? (@vsprintf($template, $args) ?: 'ERROR') : 'ERROR';

        if (self::debugMode()) {
            return '<span title="' . htmlspecialchars($key, ENT_QUOTES) . '">' . $result . '</span>';
        }

        return $result;
    }

    private static function debugMode(): bool
    {
        try {
            $cms = BootstrapContext::configProvider()?->cms();
        } catch (\Throwable) {
            return false;
        }

        return is_array($cms) && !empty($cms['language_debug']);
    }
}

