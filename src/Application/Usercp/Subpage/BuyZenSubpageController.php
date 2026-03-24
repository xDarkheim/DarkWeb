<?php

declare(strict_types=1);

namespace Darkheim\Application\Usercp\Subpage;

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Character\Character;
use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Application\Shared\Language\Translator;
use Darkheim\Domain\Validation\Validator;
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
        if (! \Darkheim\Application\Auth\SessionManager::websiteAuthenticated()) {
            \Darkheim\Infrastructure\Http\Redirector::go(1, 'login');
            return;
        }

        try {
            if (! \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active')) {
                throw new \Exception(Translator::phrase('error_47'));
            }

            $characterService  = new Character();
            $accountCharacters = $characterService->AccountCharacter($_SESSION['username']);
            if (! is_array($accountCharacters)) {
                throw new \Exception(Translator::phrase('error_46'));
            }

            $maxZen        = (int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('max_zen');
            $exchangeRatio = (int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('exchange_ratio');
            $incrementRate = (int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('increment_rate');
            $buyOptions    = $this->buildBuyOptions($maxZen, $exchangeRatio, $incrementRate);

            if (isset($_POST['submit'], $_POST['character'], $_POST['credits'])) {
                try {
                    $this->handleSubmit($characterService, $accountCharacters, $buyOptions, $maxZen, $exchangeRatio);
                } catch (\Exception $ex) {
                    \Darkheim\Application\Shared\UI\MessageRenderer::toast('error', $ex->getMessage());
                }
            }

            $this->view->render('subpages/usercp/buyzen', [
                'pageTitle'        => Translator::phrase('module_titles_txt_28'),
                'cardTitle'        => Translator::phrase('module_titles_txt_28'),
                'characterOptions' => array_map(
                    static fn(string $characterName): array => ['value' => $characterName, 'label' => $characterName],
                    $accountCharacters,
                ),
                'buyOptions'  => $buyOptions,
                'submitLabel' => Translator::phrase('buyzen_txt_5'),
            ]);
        } catch (\Exception $ex) {
            \Darkheim\Application\Shared\UI\MessageRenderer::inline('error', $ex->getMessage());
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
        $limit   = (int) floor($maxZen / $incrementRate);
        for ($multiplier = 1; $multiplier <= $limit; $multiplier++) {
            $zenAmount    = $multiplier * $incrementRate;
            $creditAmount = (int) ceil($zenAmount / $exchangeRatio);
            if ($zenAmount > $maxZen) {
                continue;
            }

            $options[] = [
                'credits' => $creditAmount,
                'zen'     => $zenAmount,
                'label'   => number_format($zenAmount) . ' Zen — ' . $creditAmount . ' ' . Translator::phrase('buyzen_txt_6'),
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
            throw new \Exception(Translator::phrase('error_28'));
        }

        $creditInput = (string) ($_POST['credits'] ?? '');
        if (! Validator::UnsignedNumber($creditInput)) {
            throw new \Exception(Translator::phrase('error_25'));
        }

        $credits        = (int) $creditInput;
        $allowedCredits = array_column($buyOptions, 'credits');
        if (! in_array($credits, $allowedCredits, true)) {
            throw new \Exception(Translator::phrase('error_24'));
        }

        $characterName = (string) ($_POST['character'] ?? '');
        $zen           = $credits * $exchangeRatio;
        if ($zen > $maxZen) {
            throw new \Exception(Translator::phrase('error_25'));
        }
        if (! in_array($characterName, $accountCharacters, true)) {
            throw new \Exception(Translator::phrase('error_24'));
        }

        $characterData = $characterService->CharacterData($characterName);
        if (! is_array($characterData)) {
            throw new \Exception(Translator::phrase('error_25'));
        }

        $characterZen = (int) ($characterData[_CLMN_CHR_ZEN_] ?? 0);
        if ($characterZen + $zen > $maxZen) {
            throw new \Exception(Translator::phrase('error_55'));
        }

        $creditSystem = new CreditSystem();
        $creditSystem->setConfigId((int) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('credit_config'));
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
            [$zen, $characterData[_CLMN_CHR_NAME_]],
        );

        \Darkheim\Application\Shared\UI\MessageRenderer::toast('success', Translator::phrase('success_21'));
        \Darkheim\Application\Shared\UI\MessageRenderer::toast('info', number_format($zen) . Translator::phrase('buyzen_txt_2') . $characterName);
    }
}
