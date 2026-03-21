<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Character\Character;

final class ClearPkSubpageController extends AbstractCharacterActionTableSubpageController
{
    protected function pageTitle(): string { return lang('module_titles_txt_13', true); }
    protected function cardTitle(): string { return lang('module_titles_txt_13', true); }
    protected function cardIconClass(): string { return 'bi bi-shield-x'; }

    protected function tableHeaders(): array
    {
        return ['', lang('clearpk_txt_1', true), lang('clearpk_txt_2', true), lang('clearpk_txt_3', true), ''];
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
                number_format((float) $data[_CLMN_CHR_ZEN_]),
                returnPkLevel((int) $data[_CLMN_CHR_PK_LEVEL_]),
            ],
            'buttonLabel' => lang('clearpk_txt_4', true),
        ];
    }

    protected function handleSubmit(Character $characterService): void
    {
        $characterService->setUserid($_SESSION['userid']);
        $characterService->setUsername($_SESSION['username']);
        $characterService->_character = (string) ($_POST['character'] ?? '');
        $characterService->CharacterClearPK();
    }

    protected function requirementsLines(): array
    {
        $lines = [];
        if (mconfig('zen_cost') > 0) {
            $lines[] = langf('clearpk_txt_5', [number_format((float) mconfig('zen_cost'))]);
        }
        return $lines;
    }
}

