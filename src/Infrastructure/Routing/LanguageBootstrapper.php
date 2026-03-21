<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

use Darkheim\Infrastructure\Runtime\SessionStore;

final class LanguageBootstrapper
{
    /**
     * @param array<string, mixed> $config
     */
    public function bootstrap(SessionStore $session, array $config): void
    {
        $defaultLanguage = (string) ($config['language_default'] ?? 'en');

        if (strtolower($defaultLanguage) !== 'en') {
            $this->loadLanguagePhrases('en');
        }

        $this->loadLanguagePhrases($defaultLanguage);

        if (
            ($config['language_switch_active'] ?? false)
            && $session->has('language_display')
            && $session->get('language_display') !== $defaultLanguage
        ) {
            $this->loadLanguagePhrases((string) $session->get('language_display'));
        }
    }

    private function loadLanguagePhrases(string $language): void
    {
        $langFile = __PATH_LANGUAGES__ . $language . '/language.php';
        if (!file_exists($langFile)) {
            return;
        }

        $lang = getLanguagePhrases();
        include $langFile;
        setLanguagePhrases($lang);
    }
}

