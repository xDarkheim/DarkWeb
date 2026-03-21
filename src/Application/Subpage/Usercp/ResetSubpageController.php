<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Character\Character;

final class ResetSubpageController extends AbstractCharacterActionTableSubpageController
{
    protected function pageTitle(): string { return lang('module_titles_txt_12', true); }
    protected function cardTitle(): string { return lang('module_titles_txt_12', true); }
    protected function cardIconClass(): string { return 'bi bi-person-dash-fill'; }

    protected function tableHeaders(): array
    {
        return ['', lang('resetcharacter_txt_1', true), lang('resetcharacter_txt_2', true), lang('resetcharacter_txt_3', true), lang('resetcharacter_txt_4', true), ''];
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
            'buttonLabel' => lang('resetcharacter_txt_5', true),
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
        if (mconfig('required_level') >= 1) {
            $lines[] = langf('resetcharacter_txt_6', [(string) mconfig('required_level')]);
        }
        if (mconfig('zen_cost') >= 1) {
            $lines[] = langf('resetcharacter_txt_7', [number_format((float) mconfig('zen_cost'))]);
        }
        if (mconfig('credit_cost') >= 1) {
            $lines[] = langf('resetcharacter_txt_9', [number_format((float) mconfig('credit_cost'))]);
        }
        if (mconfig('maximum_resets') >= 1) {
            $lines[] = langf('resetcharacter_txt_10', [number_format((float) mconfig('maximum_resets'))]);
        }
        if (mconfig('credit_reward') >= 1) {
            $lines[] = langf('resetcharacter_txt_8', [number_format((float) mconfig('credit_reward'))]);
        }
        if (mconfig('clear_inventory') == 1) {
            $lines[] = lang('resetcharacter_txt_11');
        }
        return $lines;
    }
}

