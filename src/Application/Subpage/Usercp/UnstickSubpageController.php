<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Character\Character;

final class UnstickSubpageController extends AbstractCharacterActionTableSubpageController
{
    protected function pageTitle(): string { return lang('module_titles_txt_16', true); }
    protected function cardTitle(): string { return lang('module_titles_txt_16', true); }
    protected function cardIconClass(): string { return 'bi bi-geo-alt-fill'; }

    protected function tableHeaders(): array
    {
        return ['', lang('unstickcharacter_txt_1', true), lang('unstickcharacter_txt_2', true), ''];
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
            ],
            'buttonLabel' => lang('unstickcharacter_txt_3', true),
        ];
    }

    protected function handleSubmit(Character $characterService): void
    {
        $characterService->setUserid($_SESSION['userid']);
        $characterService->setUsername($_SESSION['username']);
        $characterService->_character = (string) ($_POST['character'] ?? '');
        $characterService->CharacterUnstick();
    }

    protected function requirementsLines(): array
    {
        $lines = [];
        if (mconfig('zen_cost') > 0) {
            $lines[] = langf('unstickcharacter_txt_4', [number_format((float) mconfig('zen_cost'))]);
        }
        return $lines;
    }
}

