<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Character\Character;

final class ResetStatsSubpageController extends AbstractCharacterActionTableSubpageController
{
    protected function pageTitle(): string { return lang('module_titles_txt_18', true); }
    protected function cardTitle(): string { return lang('module_titles_txt_18', true); }
    protected function cardIconClass(): string { return 'bi bi-bar-chart-fill'; }

    protected function tableHeaders(): array
    {
        return [
            '',
            lang('resetstats_txt_1', true),
            lang('resetstats_txt_2', true),
            lang('resetstats_txt_3', true),
            lang('resetstats_txt_4', true),
            lang('resetstats_txt_5', true),
            lang('resetstats_txt_6', true),
            lang('resetstats_txt_7', true),
            '',
        ];
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
                number_format((float) $data[_CLMN_CHR_STAT_STR_]),
                number_format((float) $data[_CLMN_CHR_STAT_AGI_]),
                number_format((float) $data[_CLMN_CHR_STAT_VIT_]),
                number_format((float) $data[_CLMN_CHR_STAT_ENE_]),
                number_format((float) $data[_CLMN_CHR_STAT_CMD_]),
            ],
            'buttonLabel' => lang('resetstats_txt_8', true),
        ];
    }

    protected function handleSubmit(Character $characterService): void
    {
        $characterService->setUserid($_SESSION['userid']);
        $characterService->setUsername($_SESSION['username']);
        $characterService->_character = (string) ($_POST['character'] ?? '');
        $characterService->CharacterResetStats();
    }

    protected function requirementsLines(): array
    {
        $lines = [];
        if (mconfig('zen_cost') > 0) {
            $lines[] = langf('resetstats_txt_9', [number_format((float) mconfig('zen_cost'))]);
        }
        return $lines;
    }
}

