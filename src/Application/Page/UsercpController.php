<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Character\Character;
use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Application\Game\GameHelper;
use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Cache\CacheRepository;
use Darkheim\Infrastructure\View\ViewRenderer;

final class UsercpController
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

        $cfg = BootstrapContext::configProvider()?->config('usercp');
        if (! is_array($cfg)) {
            \Darkheim\Application\View\MessageRenderer::inline('error', 'Could not load usercp, please contact support.');
            return;
        }

        $common      = new Common();
        $accountInfo = $common->accountInformation($_SESSION['userid']);
        if (! is_array($accountInfo)) {
            \Darkheim\Application\View\MessageRenderer::inline('error', 'Could not load account data.');
            return;
        }

        $isOnline  = $common->accountOnline($_SESSION['username']) ? true : false;
        $isBlocked = ((int) ($accountInfo[_CLMN_BLOCCODE_] ?? 0) === 1);

        $characterService  = new Character();
        $accountCharacters = $characterService->AccountCharacter($_SESSION['username']);
        $characterNames    = is_array($accountCharacters) ? $accountCharacters : [];
        $charCount         = count($characterNames);

        $onlineCharacters = new CacheRepository(__PATH_CACHE__)->load('online_characters.cache');
        $onlineCharacters = is_array($onlineCharacters) ? $onlineCharacters : [];
        $charsOnline      = 0;
        foreach ($characterNames as $charName) {
            if (in_array($charName, $onlineCharacters, true)) {
                $charsOnline++;
            }
        }

        $firstCharAvatar = null;
        if ($characterNames !== []) {
            $firstCharData = $characterService->CharacterData($characterNames[0]);
            if (is_array($firstCharData)) {
                $firstCharAvatar = GameHelper::playerClassAvatar((int) ($firstCharData[_CLMN_CHR_CLASS_] ?? 0), false);
            }
        }

        $creditLabel  = '';
        $creditAmount = '';
        try {
            $creditSystem     = new CreditSystem();
            $creditConfigList = $creditSystem->showConfigs();
            if (is_array($creditConfigList)) {
                foreach ($creditConfigList as $cr) {
                    if (! is_array($cr) || empty($cr['config_display'])) {
                        continue;
                    }
                    $creditSystem->setConfigId((int) $cr['config_id']);
                    switch ((string) ($cr['config_user_col_id'] ?? '')) {
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
                    $creditLabel  = (string) ($cr['config_title'] ?? '');
                    $creditAmount = number_format((float) $creditSystem->getCredits());
                    break;
                }
            }
        } catch (\Exception $ex) {
            // Non-fatal: dashboard renders without credit block.
        }

        $tiles = [];
        foreach ($cfg as $element) {
            if (! is_array($element)) {
                continue;
            }
            if (empty($element['active'])) {
                continue;
            }

            $visibility = (string) ($element['visibility'] ?? 'user');
            if ($visibility === 'guest') {
                continue;
            }

            $iconFile = (string) ($element['icon'] ?? '');
            $title    = Validator::hasValue(\Darkheim\Application\Language\Translator::phrase((string) ($element['phrase'] ?? ''), true))
                ? \Darkheim\Application\Language\Translator::phrase((string) ($element['phrase'] ?? ''), true)
                : 'ERROR';

            $tiles[] = [
                'link' => ((string) ($element['type'] ?? '') === 'internal')
                    ? __BASE_URL__ . (string) ($element['link'] ?? '')
                    : (string) ($element['link'] ?? ''),
                'title' => $title,
                'icon'  => Validator::hasValue($iconFile)
                    ? __PATH_THEME_IMG__ . 'icons/' . $iconFile
                    : __PATH_THEME_IMG__ . 'icons/usercp_default.png',
                'newTab'      => ! empty($element['newtab']),
                'biIcon'      => $this->mapBiIcon($iconFile),
                'accentClass' => $this->mapAccentClass($iconFile),
            ];
        }

        $this->view->render('usercp', [
            'username'        => htmlspecialchars((string) $accountInfo[_CLMN_USERNM_], ENT_QUOTES, 'UTF-8'),
            'subtitle'        => \Darkheim\Application\Language\Translator::phrase('usercp_menu_title', true),
            'statusClass'     => $isBlocked ? 'ma-pill-banned' : 'ma-pill-active',
            'statusText'      => $isBlocked ? \Darkheim\Application\Language\Translator::phrase('myaccount_txt_8') : \Darkheim\Application\Language\Translator::phrase('myaccount_txt_7'),
            'onlineClass'     => $isOnline ? 'ma-pill-online' : 'ma-pill-offline',
            'onlineText'      => $isOnline ? \Darkheim\Application\Language\Translator::phrase('myaccount_txt_9') : \Darkheim\Application\Language\Translator::phrase('myaccount_txt_10'),
            'firstCharAvatar' => $firstCharAvatar,
            'charCount'       => $charCount,
            'charsOnline'     => $charsOnline,
            'creditLabel'     => htmlspecialchars($creditLabel, ENT_QUOTES, 'UTF-8'),
            'creditAmount'    => $creditAmount,
            'tiles'           => $tiles,
        ]);
    }

    private function mapBiIcon(string $iconFile): string
    {
        $f = strtolower($iconFile);
        if (str_contains($f, 'account')) {
            return 'bi-person-circle';
        }
        if (str_contains($f, 'password')) {
            return 'bi-key-fill';
        }
        if (str_contains($f, 'email')) {
            return 'bi-envelope-fill';
        }
        if (str_contains($f, 'addstat')) {
            return 'bi-bar-chart-fill';
        }
        if (str_contains($f, 'fixstat') || str_contains($f, 'resetstat')) {
            return 'bi-arrow-counterclockwise';
        }
        if (str_contains($f, 'reset')) {
            return 'bi-person-dash-fill';
        }
        if (str_contains($f, 'vote')) {
            return 'bi-star-fill';
        }
        if (str_contains($f, 'zen')) {
            return 'bi-coin';
        }
        if (str_contains($f, 'donat')) {
            return 'bi-gem';
        }
        if (str_contains($f, 'clearpk') || str_contains($f, 'pk')) {
            return 'bi-shield-x';
        }
        if (str_contains($f, 'skill') || str_contains($f, 'clearst')) {
            return 'bi-lightning-fill';
        }
        if (str_contains($f, 'unstick')) {
            return 'bi-geo-alt-fill';
        }
        if (str_contains($f, 'buy')) {
            return 'bi-bag-fill';
        }
        return 'bi-grid';
    }

    private function mapAccentClass(string $iconFile): string
    {
        $f = strtolower($iconFile);
        if (str_contains($f, 'vote') || str_contains($f, 'donat') || str_contains($f, 'zen') || str_contains($f, 'buy')) {
            return 'ucp-tile-gold';
        }
        if (str_contains($f, 'reset') || str_contains($f, 'unstick')) {
            return 'ucp-tile-red';
        }
        if (str_contains($f, 'stat') || str_contains($f, 'skill')) {
            return 'ucp-tile-blue';
        }
        if (str_contains($f, 'pk')) {
            return 'ucp-tile-purple';
        }
        return 'ucp-tile-default';
    }
}
