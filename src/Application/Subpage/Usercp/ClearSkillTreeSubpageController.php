<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Character\Character;
use Darkheim\Application\Language\Translator;

final class ClearSkillTreeSubpageController extends AbstractCharacterActionTableSubpageController
{
    protected function pageTitle(): string { return Translator::phrase('module_titles_txt_19'); }
    protected function cardTitle(): string { return Translator::phrase('module_titles_txt_19'); }
    protected function cardIconClass(): string { return 'bi bi-lightning-fill'; }

    protected function tableHeaders(): array
    {
        return ['', Translator::phrase('clearst_txt_1'), Translator::phrase('clearst_txt_2'), Translator::phrase('clearst_txt_5'), Translator::phrase('clearst_txt_3'), ''];
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
            'buttonLabel' => Translator::phrase('clearst_txt_4'),
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
        if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('required_level') > 0) {
            $lines[] = Translator::phraseFmt('clearst_txt_8', [number_format((float) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('required_level'))]);
        }
        if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('required_master_level') > 0) {
            $lines[] = Translator::phraseFmt('clearst_txt_6', [number_format((float) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('required_master_level'))]);
        }
        if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('zen_cost') > 0) {
            $lines[] = Translator::phraseFmt('clearst_txt_7', [number_format((float) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('zen_cost'))]);
        }
        return $lines;
    }
}

