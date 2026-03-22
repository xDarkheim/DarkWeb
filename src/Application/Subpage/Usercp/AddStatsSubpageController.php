<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Character\Character;
use Darkheim\Application\Language\Translator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\View\ViewRenderer;

final class AddStatsSubpageController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        if (! \Darkheim\Application\Auth\SessionManager::websiteAuthenticated()) {
            \Darkheim\Infrastructure\Http\Redirector::go(1, 'login');
            return;
        }

        try {
            if (! \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active')) {
                throw new \Exception(Translator::phrase('error_47'));
            }

            $commandClasses = (BootstrapContext::runtimeState()?->customConfig() ?? [])['character_cmd'] ?? null;
            if (! is_array($commandClasses)) {
                throw new \Exception(Translator::phrase('error_59'));
            }

            $characterService  = new Character();
            $accountCharacters = $characterService->AccountCharacter($_SESSION['username']);
            if (! is_array($accountCharacters)) {
                throw new \Exception(Translator::phrase('error_46'));
            }

            if (isset($_POST['submit'])) {
                try {
                    $this->handleSubmit($characterService);
                } catch (\Exception $ex) {
                    \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
                }
            }

            $characters = [];
            foreach ($accountCharacters as $characterName) {
                $characterData = $characterService->CharacterData((string) $characterName);
                if (! is_array($characterData)) {
                    continue;
                }

                $classId      = (int) ($characterData[_CLMN_CHR_CLASS_] ?? 0);
                $characters[] = [
                    'name'            => (string) ($characterData[_CLMN_CHR_NAME_] ?? ''),
                    'availablePoints' => number_format((int) ($characterData[_CLMN_CHR_LVLUP_POINT_] ?? 0)),
                    'avatarHtml'      => $characterService->GenerateCharacterClassAvatar($classId),
                    'strength'        => (int) ($characterData[_CLMN_CHR_STAT_STR_] ?? 0),
                    'agility'         => (int) ($characterData[_CLMN_CHR_STAT_AGI_] ?? 0),
                    'vitality'        => (int) ($characterData[_CLMN_CHR_STAT_VIT_] ?? 0),
                    'energy'          => (int) ($characterData[_CLMN_CHR_STAT_ENE_] ?? 0),
                    'command'         => (int) ($characterData[_CLMN_CHR_STAT_CMD_] ?? 0),
                    'showCommand'     => in_array($classId, $commandClasses, true),
                ];
            }

            $requirementsLines = [];
            if ((int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('required_level') > 0) {
                $requirementsLines[] = Translator::phraseFmt('addstats_txt_11', [number_format((int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('required_level'))]);
            }
            if ((int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('required_master_level') > 0) {
                $requirementsLines[] = Translator::phraseFmt('addstats_txt_10', [number_format((int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('required_master_level'))]);
            }
            if ((int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('zen_cost') > 0) {
                $requirementsLines[] = Translator::phraseFmt('addstats_txt_9', [number_format((int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('zen_cost'))]);
            }
            $requirementsLines[] = Translator::phraseFmt('addstats_txt_12', [number_format((int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('max_stats'))]);
            if ((int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('minimum_limit') > 0) {
                $requirementsLines[] = Translator::phraseFmt('addstats_txt_13', [number_format((int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('minimum_limit'))]);
            }

            $this->view->render('subpages/usercp/addstats', [
                'pageTitle'         => Translator::phrase('module_titles_txt_25'),
                'maxStats'          => (int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('max_stats'),
                'characters'        => $characters,
                'requirementsLines' => $requirementsLines,
            ]);
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::inline('error', $ex->getMessage());
        }
    }

    private function handleSubmit(Character $characterService): void
    {
        $characterService->setUserid($_SESSION['userid']);
        $characterService->setUsername($_SESSION['username']);
        $characterService->_character = (string) ($_POST['character'] ?? '');

        $strength = (string) ($_POST['add_str'] ?? '');
        if ($strength !== '' && (int) $strength > 0) {
            $characterService->setStrength($strength);
        }

        $agility = (string) ($_POST['add_agi'] ?? '');
        if ($agility !== '' && (int) $agility > 0) {
            $characterService->setAgility($agility);
        }

        $vitality = (string) ($_POST['add_vit'] ?? '');
        if ($vitality !== '' && (int) $vitality > 0) {
            $characterService->setVitality($vitality);
        }

        $energy = (string) ($_POST['add_ene'] ?? '');
        if ($energy !== '' && (int) $energy > 0) {
            $characterService->setEnergy($energy);
        }

        $command = (string) ($_POST['add_com'] ?? '');
        if ($command !== '' && (int) $command > 0) {
            $characterService->setCommand($command);
        }

        $characterService->CharacterAddStats();
    }
}
