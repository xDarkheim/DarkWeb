<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Character\Character;
use Darkheim\Application\Game\GameHelper;
use Darkheim\Application\Language\Translator;

final class ClearPkSubpageController extends AbstractCharacterActionTableSubpageController
{
    protected function pageTitle(): string
    {
        return Translator::phrase('module_titles_txt_13');
    }
    protected function cardTitle(): string
    {
        return Translator::phrase('module_titles_txt_13');
    }
    protected function cardIconClass(): string
    {
        return 'bi bi-shield-x';
    }

    protected function tableHeaders(): array
    {
        return ['', Translator::phrase('clearpk_txt_1'), Translator::phrase('clearpk_txt_2'), Translator::phrase('clearpk_txt_3'), ''];
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
                number_format((float) $data[_CLMN_CHR_ZEN_]),
                GameHelper::pkLevel((int) $data[_CLMN_CHR_PK_LEVEL_]),
            ],
            'buttonLabel' => Translator::phrase('clearpk_txt_4'),
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
        if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('zen_cost') > 0) {
            $lines[] = Translator::phraseFmt('clearpk_txt_5', [number_format((float) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('zen_cost'))]);
        }
        return $lines;
    }
}
