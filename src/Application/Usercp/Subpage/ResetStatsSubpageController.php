<?php

declare(strict_types=1);

namespace Darkheim\Application\Usercp\Subpage;

use Darkheim\Application\Character\Character;
use Darkheim\Application\Shared\Language\Translator;

final class ResetStatsSubpageController extends AbstractCharacterActionTableSubpageController
{
    protected function pageTitle(): string
    {
        return Translator::phrase('module_titles_txt_18');
    }
    protected function cardTitle(): string
    {
        return Translator::phrase('module_titles_txt_18');
    }
    protected function cardIconClass(): string
    {
        return 'bi bi-bar-chart-fill';
    }

    protected function tableHeaders(): array
    {
        return [
            '',
            Translator::phrase('resetstats_txt_1'),
            Translator::phrase('resetstats_txt_2'),
            Translator::phrase('resetstats_txt_3'),
            Translator::phrase('resetstats_txt_4'),
            Translator::phrase('resetstats_txt_5'),
            Translator::phrase('resetstats_txt_6'),
            Translator::phrase('resetstats_txt_7'),
            '',
        ];
    }

    protected function buildRow(Character $characterService, string $characterName): ?array
    {
        $data = $characterService->CharacterData($characterName);
        if (! is_array($data)) {
            return null;
        }

        return [
            'character' => (string) $data[_CLMN_CHR_NAME_],
            'cells'     => [
                $characterService->GenerateCharacterClassAvatar((int) $data[_CLMN_CHR_CLASS_]),
                htmlspecialchars((string) $data[_CLMN_CHR_NAME_], ENT_QUOTES, 'UTF-8'),
                number_format((float) $data[_CLMN_CHR_LVL_]),
                number_format((float) $data[_CLMN_CHR_STAT_STR_]),
                number_format((float) $data[_CLMN_CHR_STAT_AGI_]),
                number_format((float) $data[_CLMN_CHR_STAT_VIT_]),
                number_format((float) $data[_CLMN_CHR_STAT_ENE_]),
                number_format((float) $data[_CLMN_CHR_STAT_CMD_]),
            ],
            'buttonLabel' => Translator::phrase('resetstats_txt_8'),
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
        if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('zen_cost') > 0) {
            $lines[] = Translator::phraseFmt('resetstats_txt_9', [number_format((float) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('zen_cost'))]);
        }
        return $lines;
    }
}
