<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Character\Character;

final class ClearSkillTreeSubpageController extends AbstractCharacterActionTableSubpageController
{
    protected function pageTitle(): string { return lang('module_titles_txt_19', true); }
    protected function cardTitle(): string { return lang('module_titles_txt_19', true); }
    protected function cardIconClass(): string { return 'bi bi-lightning-fill'; }

    protected function tableHeaders(): array
    {
        return ['', lang('clearst_txt_1', true), lang('clearst_txt_2', true), lang('clearst_txt_5', true), lang('clearst_txt_3', true), ''];
    }

    protected function buildRow(Character $characterService, string $characterName): ?array
    {
        $data = $characterService->CharacterData($characterName);
        $mlData = $characterService->getMasterLevelInfo($characterName);
        if (!is_array($data) || !is_array($mlData)) {
            return null;
        }

        return [
            'character' => (string) $data[_CLMN_CHR_NAME_],
            'cells' => [
                $characterService->GenerateCharacterClassAvatar((int) $data[_CLMN_CHR_CLASS_]),
                htmlspecialchars((string) $data[_CLMN_CHR_NAME_], ENT_QUOTES, 'UTF-8'),
                number_format((float) ($mlData[_CLMN_ML_LVL_] ?? 0)),
                number_format((float) ($mlData[_CLMN_ML_POINT_] ?? 0)),
                number_format((float) $data[_CLMN_CHR_ZEN_]),
            ],
            'buttonLabel' => lang('clearst_txt_4', true),
        ];
    }

    protected function handleSubmit(Character $characterService): void
    {
        $characterService->setUserid($_SESSION['userid']);
        $characterService->setUsername($_SESSION['username']);
        $characterService->_character = (string) ($_POST['character'] ?? '');
        $characterService->CharacterClearSkillTree();
    }

    protected function requirementsLines(): array
    {
        $lines = [];
        if (mconfig('required_level') > 0) {
            $lines[] = langf('clearst_txt_8', [number_format((float) mconfig('required_level'))]);
        }
        if (mconfig('required_master_level') > 0) {
            $lines[] = langf('clearst_txt_6', [number_format((float) mconfig('required_master_level'))]);
        }
        if (mconfig('zen_cost') > 0) {
            $lines[] = langf('clearst_txt_7', [number_format((float) mconfig('zen_cost'))]);
        }
        return $lines;
    }
}

