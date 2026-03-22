<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage;

use Darkheim\Application\Language\Translator;
use Darkheim\Domain\Validator;

final class LanguageSwitchSubpageController
{
    public function render(): void
    {
        try {
            if (!\Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('language_switch_active', true)) {
                throw new \Exception(Translator::phrase('error_62'));
            }

            $target = (string) ($_GET['to'] ?? '');
            if (strlen($target) !== 2 || !Validator::Alpha($target)) {
                throw new \Exception(Translator::phrase('error_63'));
            }
            if (!is_file(__PATH_LANGUAGES__ . $target . '/language.php')) {
                throw new \Exception(Translator::phrase('error_65'));
            }

            $_SESSION['language_display'] = $target;
            \Darkheim\Infrastructure\Http\Redirector::go();
        } catch (\Exception $ex) {
            if (!\Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('error_reporting', true)) {
                \Darkheim\Infrastructure\Http\Redirector::go();
                return;
            }
            \Darkheim\Application\View\MessageRenderer::inline('error', $ex->getMessage());
        }
    }
}

