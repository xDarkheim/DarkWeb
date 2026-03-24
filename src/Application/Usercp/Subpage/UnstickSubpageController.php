<?php

declare(strict_types=1);

namespace Darkheim\Application\Usercp\Subpage;

use Darkheim\Application\Character\Character;
use Darkheim\Application\Shared\Language\Translator;

final class UnstickSubpageController extends AbstractCharacterActionTableSubpageController
{
    protected function pageTitle(): string
    {
        return Translator::phrase('module_titles_txt_16');
    }
    protected function cardTitle(): string
    {
        return Translator::phrase('module_titles_txt_16');
    }
    protected function cardIconClass(): string
    {
        return 'bi bi-geo-alt-fill';
    }

    protected function tableHeaders(): array
    {
        return ['', Translator::phrase('unstickcharacter_txt_1'), Translator::phrase('unstickcharacter_txt_2'), ''];
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
            ],
            'buttonLabel' => Translator::phrase('unstickcharacter_txt_3'),
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
        if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('zen_cost') > 0) {
            $lines[] = Translator::phraseFmt('unstickcharacter_txt_4', [number_format((float) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('zen_cost'))]);
        }
        return $lines;
    }
}
