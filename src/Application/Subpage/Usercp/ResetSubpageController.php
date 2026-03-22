<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Character\Character;
use Darkheim\Application\Language\Translator;

final class ResetSubpageController extends AbstractCharacterActionTableSubpageController
{
    protected function pageTitle(): string { return Translator::phrase('module_titles_txt_12'); }
    protected function cardTitle(): string { return Translator::phrase('module_titles_txt_12'); }
    protected function cardIconClass(): string { return 'bi bi-person-dash-fill'; }

    protected function tableHeaders(): array
    {
        return ['', Translator::phrase('resetcharacter_txt_1'), Translator::phrase('resetcharacter_txt_2'), Translator::phrase('resetcharacter_txt_3'), Translator::phrase('resetcharacter_txt_4'), ''];
    }

    protected function buildRow(Character $characterService, string $characterName): ?array
    {
        $data = $characterService->CharacterData($characterName);
        if (!is_array($data)) {
            return null;
        }

        return [
            'character' => (string) $data[_CLMN_CHR_NAME_],
            'cells' => [
                $characterService->GenerateCharacterClassAvatar((int) $data[_CLMN_CHR_CLASS_]),
                htmlspecialchars((string) $data[_CLMN_CHR_NAME_], ENT_QUOTES, 'UTF-8'),
                number_format((float) $data[_CLMN_CHR_LVL_]),
                number_format((float) $data[_CLMN_CHR_ZEN_]),
                number_format((float) $data[_CLMN_CHR_RSTS_]),
            ],
            'buttonLabel' => Translator::phrase('resetcharacter_txt_5'),
        ];
    }

    protected function handleSubmit(Character $characterService): void
    {
        $characterService->setUserid($_SESSION['userid']);
        $characterService->setUsername($_SESSION['username']);
        $characterService->_character = (string) ($_POST['character'] ?? '');
        $characterService->CharacterReset();
    }

    protected function requirementsLines(): array
    {
        $lines = [];
        if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('required_level') >= 1) {
            $lines[] = Translator::phraseFmt('resetcharacter_txt_6', [(string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('required_level')]);
        }
        if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('zen_cost') >= 1) {
            $lines[] = Translator::phraseFmt('resetcharacter_txt_7', [number_format((float) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('zen_cost'))]);
        }
        if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('credit_cost') >= 1) {
            $lines[] = Translator::phraseFmt('resetcharacter_txt_9', [number_format((float) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('credit_cost'))]);
        }
        if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('maximum_resets') >= 1) {
            $lines[] = Translator::phraseFmt('resetcharacter_txt_10', [number_format((float) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('maximum_resets'))]);
        }
        if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('credit_reward') >= 1) {
            $lines[] = Translator::phraseFmt('resetcharacter_txt_8', [number_format((float) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('credit_reward'))]);
        }
        if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('clear_inventory') == 1) {
            $lines[] = Translator::phrase('resetcharacter_txt_11');
        }
        return $lines;
    }
}

