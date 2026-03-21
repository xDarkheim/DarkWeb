<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Character\Character;
use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\View\ViewRenderer;

final class BuyZenSubpageController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        if (!isLoggedIn()) {
            redirect(1, 'login');
            return;
        }

        try {
            if (!mconfig('active')) {
                throw new \Exception(lang('error_47', true));
            }

            $characterService = new Character();
            $accountCharacters = $characterService->AccountCharacter($_SESSION['username']);
            if (!is_array($accountCharacters)) {
                throw new \Exception(lang('error_46', true));
            }

            $maxZen = (int) mconfig('max_zen');
            $exchangeRatio = (int) mconfig('exchange_ratio');
            $incrementRate = (int) mconfig('increment_rate');
            $buyOptions = $this->buildBuyOptions($maxZen, $exchangeRatio, $incrementRate);

            if (isset($_POST['submit'], $_POST['character'], $_POST['credits'])) {
                try {
                    $this->handleSubmit($characterService, $accountCharacters, $buyOptions, $maxZen, $exchangeRatio);
                } catch (\Exception $ex) {
                    message('error', $ex->getMessage());
                }
            }

            $this->view->render('subpages/usercp/buyzen', [
                'pageTitle' => lang('module_titles_txt_28', true),
                'cardTitle' => lang('module_titles_txt_28', true),
                'characterOptions' => array_map(
                    static fn (string $characterName): array => ['value' => $characterName, 'label' => $characterName],
                    $accountCharacters
                ),
                'buyOptions' => $buyOptions,
                'submitLabel' => lang('buyzen_txt_5', true),
            ]);
        } catch (\Exception $ex) {
            inline_message('error', $ex->getMessage());
        }
    }

    /**
     * @return array<int,array{credits:int,zen:int,label:string}>
     */
    private function buildBuyOptions(int $maxZen, int $exchangeRatio, int $incrementRate): array
    {
        if ($maxZen <= 0 || $exchangeRatio <= 0 || $incrementRate <= 0) {
            throw new \RuntimeException('Invalid Buy Zen configuration.');
        }

        $options = [];
        $limit = (int) floor($maxZen / $incrementRate);
        for ($multiplier = 1; $multiplier <= $limit; $multiplier++) {
            $zenAmount = $multiplier * $incrementRate;
            $creditAmount = (int) ceil($zenAmount / $exchangeRatio);
            if ($zenAmount > $maxZen) {
                continue;
            }

            $options[] = [
                'credits' => $creditAmount,
                'zen' => $zenAmount,
                'label' => number_format($zenAmount) . ' Zen — ' . $creditAmount . ' ' . lang('buyzen_txt_6', true),
            ];
        }

        return $options;
    }

    /**
     * @param array<int,string> $accountCharacters
     * @param array<int,array{credits:int,zen:int,label:string}> $buyOptions
     */
    private function handleSubmit(
        Character $characterService,
        array $accountCharacters,
        array $buyOptions,
        int $maxZen,
        int $exchangeRatio,
    ): void {
        $common = new Common();
        if ($common->accountOnline($_SESSION['username'])) {
            throw new \Exception(lang('error_28', true));
        }

        $creditInput = (string) ($_POST['credits'] ?? '');
        if (!Validator::UnsignedNumber($creditInput)) {
            throw new \Exception(lang('error_25', true));
        }

        $credits = (int) $creditInput;
        $allowedCredits = array_column($buyOptions, 'credits');
        if (!in_array($credits, $allowedCredits, true)) {
            throw new \Exception(lang('error_24', true));
        }

        $characterName = (string) ($_POST['character'] ?? '');
        $zen = $credits * $exchangeRatio;
        if ($zen > $maxZen) {
            throw new \Exception(lang('error_25', true));
        }
        if (!in_array($characterName, $accountCharacters, true)) {
            throw new \Exception(lang('error_24', true));
        }

        $characterData = $characterService->CharacterData($characterName);
        if (!is_array($characterData)) {
            throw new \Exception(lang('error_25', true));
        }

        $characterZen = (int) ($characterData[_CLMN_CHR_ZEN_] ?? 0);
        if ($characterZen + $zen > $maxZen) {
            throw new \Exception(lang('error_55', true));
        }

        $creditSystem = new CreditSystem();
        $creditSystem->setConfigId((int) mconfig('credit_config'));
        $configSettings = $creditSystem->showConfigs(true);
        switch ($configSettings['config_user_col_id'] ?? '') {
            case 'userid':
                $creditSystem->setIdentifier($_SESSION['userid']);
                break;
            case 'username':
                $creditSystem->setIdentifier($_SESSION['username']);
                break;
            case 'character':
                $creditSystem->setIdentifier($characterName);
                break;
            default:
                throw new \Exception('Invalid identifier (credit system).');
        }
        $creditSystem->subtractCredits($credits);

        $db = Connection::Database('MuOnline');
        $db->query(
            'UPDATE ' . _TBL_CHR_ . ' SET ' . _CLMN_CHR_ZEN_ . ' = ' . _CLMN_CHR_ZEN_ . ' + ? WHERE ' . _CLMN_CHR_NAME_ . ' = ?',
            [$zen, $characterData[_CLMN_CHR_NAME_]]
        );

        message('success', lang('success_21', true));
        message('info', number_format($zen) . lang('buyzen_txt_2', true) . $characterName);
    }
}

