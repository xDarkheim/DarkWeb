<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Character\Character;
use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Application\Game\GameHelper;
use Darkheim\Application\Language\Translator;
use Darkheim\Application\Profile\ProfileRenderer;
use Darkheim\Infrastructure\Cache\CacheRepository;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\View\ViewRenderer;

final class MyAccountSubpageController
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

        if (! \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active')) {
            \Darkheim\Application\View\MessageRenderer::inline('error', Translator::phrase('error_12'));
            return;
        }

        $common      = new Common();
        $accountInfo = $common->accountInformation($_SESSION['userid']);
        if (! is_array($accountInfo)) {
            \Darkheim\Application\View\MessageRenderer::inline('error', Translator::phrase('error_12'));
            return;
        }

        $isOnlineAccount = $common->accountOnline($_SESSION['username']);
        $isBlocked       = ((int) ($accountInfo[_CLMN_BLOCCODE_] ?? 0) === 1);

        $characterService  = new Character();
        $accountCharacters = $characterService->AccountCharacter($_SESSION['username']);
        $accountCharacters = is_array($accountCharacters) ? $accountCharacters : [];
        $onlineCharacters  = new CacheRepository(__PATH_CACHE__)->load('online_characters.cache');
        $onlineCharacters  = is_array($onlineCharacters) ? $onlineCharacters : [];

        $creditRows = [];
        try {
            $creditSystem     = new CreditSystem();
            $creditConfigList = $creditSystem->showConfigs();
            if (is_array($creditConfigList)) {
                foreach ($creditConfigList as $creditCfg) {
                    if (! is_array($creditCfg) || empty($creditCfg['config_display'])) {
                        continue;
                    }
                    $creditSystem->setConfigId((int) $creditCfg['config_id']);
                    switch ((string) ($creditCfg['config_user_col_id'] ?? '')) {
                        case 'userid':
                            $creditSystem->setIdentifier((string) ($accountInfo[_CLMN_MEMBID_] ?? ''));
                            break;
                        case 'username':
                            $creditSystem->setIdentifier((string) ($accountInfo[_CLMN_USERNM_] ?? ''));
                            break;
                        case 'email':
                            $creditSystem->setIdentifier((string) ($accountInfo[_CLMN_EMAIL_] ?? ''));
                            break;
                        default:
                            continue 2;
                    }
                    $creditRows[] = [
                        'title'  => htmlspecialchars((string) ($creditCfg['config_title'] ?? ''), ENT_QUOTES, 'UTF-8'),
                        'amount' => number_format((float) $creditSystem->getCredits()),
                    ];
                }
            }
        } catch (\Exception $ex) {
            // Non-fatal.
        }

        $characterCards = [];
        foreach ($accountCharacters as $characterName) {
            $cd = $characterService->CharacterData((string) $characterName);
            if (! is_array($cd)) {
                continue;
            }

            $displayLevel = (int) ($cd[_CLMN_CHR_LVL_] ?? 0);
            if (defined('_TBL_MASTERLVL_')) {
                $mlData = $characterService->getMasterLevelInfo((string) $characterName);
                if (is_array($mlData)) {
                    $displayLevel += (int) ($mlData[_CLMN_ML_LVL_] ?? 0);
                } elseif (array_key_exists(_CLMN_ML_LVL_, $cd)) {
                    $displayLevel += (int) ($cd[_CLMN_ML_LVL_] ?? 0);
                }
            }

            $characterCards[] = [
                'isOnline'   => in_array($characterName, $onlineCharacters, true),
                'profileUrl' => ProfileRenderer::player((string) $characterName, true),
                'avatarUrl'  => GameHelper::playerClassAvatar((int) ($cd[_CLMN_CHR_CLASS_] ?? 0), false),
                'nameHtml'   => ProfileRenderer::player((string) $characterName),
                'className'  => GameHelper::playerClass((int) ($cd[_CLMN_CHR_CLASS_] ?? 0)),
                'level'      => $displayLevel,
                'location'   => GameHelper::mapName((int) ($cd[_CLMN_CHR_MAP_] ?? 0)),
            ];
        }

        $connectionHistoryRows = [];
        $hasConnectionHistory  = defined('_TBL_CH_')
            && defined('_CLMN_CH_ACCID_')
            && defined('_CLMN_CH_ID_')
            && defined('_CLMN_CH_DATE_')
            && defined('_CLMN_CH_SRVNM_')
            && defined('_CLMN_CH_IP_')
            && defined('_CLMN_CH_STATE_');

        if ($hasConnectionHistory) {
            $db   = Connection::Database('MuOnline');
            $rows = $db->query_fetch(
                'SELECT TOP 10 * FROM ' . constant('_TBL_CH_') . ' WHERE ' . constant('_CLMN_CH_ACCID_') . ' = ? ORDER BY ' . constant('_CLMN_CH_ID_') . ' DESC',
                [$_SESSION['username']],
            );
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $connectionHistoryRows[] = [
                        'date'   => (string) ($row[constant('_CLMN_CH_DATE_')] ?? ''),
                        'server' => (string) ($row[constant('_CLMN_CH_SRVNM_')] ?? ''),
                        'ip'     => (string) ($row[constant('_CLMN_CH_IP_')] ?? ''),
                        'state'  => (string) ($row[constant('_CLMN_CH_STATE_')] ?? ''),
                    ];
                }
            }
        }

        $this->view->render('subpages/usercp/myaccount', [
            'username'               => htmlspecialchars((string) ($accountInfo[_CLMN_USERNM_] ?? ''), ENT_QUOTES, 'UTF-8'),
            'statusPillClass'        => $isBlocked ? 'ma-pill-banned' : 'ma-pill-active',
            'statusPillText'         => $isBlocked ? Translator::phrase('myaccount_txt_8') : Translator::phrase('myaccount_txt_7'),
            'onlinePillClass'        => $isOnlineAccount ? 'ma-pill-online' : 'ma-pill-offline',
            'onlinePillText'         => $isOnlineAccount ? Translator::phrase('myaccount_txt_9') : Translator::phrase('myaccount_txt_10'),
            'email'                  => htmlspecialchars((string) ($accountInfo[_CLMN_EMAIL_] ?? ''), ENT_QUOTES, 'UTF-8'),
            'creditRows'             => $creditRows,
            'myEmailUrl'             => __BASE_URL__ . 'usercp/myemail/',
            'myPasswordUrl'          => __BASE_URL__ . 'usercp/mypassword/',
            'characterCards'         => $characterCards,
            'hasCharacters'          => $characterCards !== [],
            'emptyCharactersMessage' => Translator::phrase('error_46'),
            'hasConnectionHistory'   => $hasConnectionHistory,
            'connectionHistoryRows'  => $connectionHistoryRows,
        ]);
    }
}
