<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage;

use Darkheim\Domain\Validator;

final class LanguageSwitchSubpageController
{
    public function render(): void
    {
        try {
            if (!config('language_switch_active', true)) {
                throw new \Exception(lang('error_62'));
            }

            $target = (string) ($_GET['to'] ?? '');
            if (strlen($target) !== 2 || !Validator::Alpha($target)) {
                throw new \Exception(lang('error_63'));
            }
            if (!is_file(__PATH_LANGUAGES__ . $target . '/language.php')) {
                throw new \Exception(lang('error_65'));
            }

            $_SESSION['language_display'] = $target;
            redirect();
        } catch (\Exception $ex) {
            if (!config('error_reporting', true)) {
                redirect();
                return;
            }
            inline_message('error', $ex->getMessage());
        }
    }
}

