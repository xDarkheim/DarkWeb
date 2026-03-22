<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Theme;

use Darkheim\Application\CastleSiege\CastleSiege;

final class DefaultThemeLayoutBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(string $currentPage, string $currentSubpage): array
    {
        $serverInfo = $this->serverInfoData();
        $isLoggedIn = $this->isLoggedInStrict();
        $serverName = (string) config('server_name', true);

        return [
            'htmlLang' => htmlspecialchars((string) config('language_default', true), ENT_QUOTES, 'UTF-8'),
            'seoTitle' => htmlspecialchars((string) config('website_title', true), ENT_QUOTES, 'UTF-8'),
            'seoDescription' => htmlspecialchars((string) config('website_meta_description', true), ENT_QUOTES, 'UTF-8'),
            'seoKeywords' => htmlspecialchars((string) config('website_meta_keywords', true), ENT_QUOTES, 'UTF-8'),
            'seoSiteName' => htmlspecialchars($serverName, ENT_QUOTES, 'UTF-8'),
            'seoUrl' => htmlspecialchars(__BASE_URL__, ENT_QUOTES, 'UTF-8'),
            'seoImage' => htmlspecialchars(__PATH_IMG__ . 'brand.jpg', ENT_QUOTES, 'UTF-8'),
            'stylesheetHrefs' => $this->stylesheetHrefs(),
            'baseUrl' => __BASE_URL__,
            'showLanguageSwitcher' => (bool) config('language_switch_active', true),
            'languageSwitcherHtml' => $this->renderLanguageSwitcherHtml(),
            'topBarIsLoggedIn' => $isLoggedIn,
            'topBarShowAdmincp' => $isLoggedIn && canAccessAdminCP($_SESSION['username'] ?? ''),
            'topBarUsercpLabel' => (string) lang('module_titles_txt_3'),
            'topBarLogoutLabel' => (string) lang('menu_txt_6'),
            'topBarRegisterLabel' => (string) lang('menu_txt_3'),
            'topBarLoginLabel' => (string) lang('menu_txt_4'),
            'usercpUrl' => __BASE_URL__ . 'usercp/',
            'admincpUrl' => __BASE_URL__ . 'admincp/',
            'logoutUrl' => __BASE_URL__ . 'logout/',
            'registerUrl' => __BASE_URL__ . 'register/',
            'loginUrl' => __BASE_URL__ . 'login/',
            'navbarHtml' => $this->renderNavbarHtml(),
            'brandHomeUrl' => __BASE_URL__,
            'brandLogoUrl' => __PATH_THEME_IMG__ . 'logo.png',
            'brandTitleAttr' => htmlspecialchars($serverName, ENT_QUOTES, 'UTF-8'),
            'brandAlt' => htmlspecialchars($serverName, ENT_QUOTES, 'UTF-8'),
            'showOnlineCounter' => $serverInfo['showOnlineCounter'],
            'onlineLabel' => (string) lang('sidebar_srvinfo_txt_5'),
            'onlinePlayersFormatted' => number_format($serverInfo['onlinePlayers']),
            'onlinePlayersPercent' => $serverInfo['onlinePlayersPercent'],
            'serverTimeLabel' => (string) lang('server_time'),
            'userTimeLabel' => (string) lang('user_time'),
            'showSidebarLayout' => $currentPage === 'usercp' && $currentSubpage !== '',
            'moduleColumnClass' => $currentPage === 'usercp' && $currentSubpage !== '' ? 'col-xs-8' : 'col-xs-12',
            'sidebarColumnClass' => 'col-xs-4',
            'sidebarData' => $this->sidebarData($serverInfo, $isLoggedIn),
            'footerData' => $this->footerData(),
            'mainJsUrl' => $this->versionedUrl(__PATH_THEME_JS__, __PATH_THEME_ROOT__ . 'js/main.js'),
            'eventsJsUrl' => $this->versionedUrl(__PATH_THEME_JS__, __PATH_THEME_ROOT__ . 'js/events.js'),
            'componentsJsUrl' => $this->versionedUrl(__PATH_ASSETS_JS__, __PUBLIC_DIR__ . 'assets/js/components.js'),
        ];
    }

    public function renderUsercpMenuHtml(): string
    {
        return $this->renderMenuHtml('usercp', true);
    }

    /**
     * @return array<int, string>
     */
    private function stylesheetHrefs(): array
    {
        $stylesheets = [
            $this->versionedUrl(__PATH_THEME_CSS__, __PATH_THEME_ROOT__ . 'css/style.css'),
            $this->versionedUrl(__PATH_THEME_CSS__, __PATH_THEME_ROOT__ . 'css/profiles.css'),
            $this->versionedUrl(__PATH_THEME_CSS__, __PATH_THEME_ROOT__ . 'css/castle-siege.css'),
        ];

        $assetsCssDir = __PUBLIC_DIR__ . 'assets/css/';
        $assetFiles = ['variables', 'toast', 'auth', 'ucp', 'myaccount', 'profiles', 'info', 'tos', 'news', 'rankings', 'panels', 'paypal', 'downloads', 'castlesiege'];
        foreach ($assetFiles as $assetFile) {
            $path = $assetsCssDir . $assetFile . '.css';
            if (!is_file($path)) {
                continue;
            }
            $stylesheets[] = $this->versionedUrl(__PATH_ASSETS_CSS__, $path, $assetFile . '.css');
        }

        $stylesheets[] = $this->versionedUrl(__PATH_THEME_CSS__, __PATH_THEME_ROOT__ . 'css/override.css');

        return array_values(array_filter($stylesheets, static fn (?string $href): bool => is_string($href) && $href !== ''));
    }

    /**
     * @return array{showOnlineCounter:bool,onlinePlayers:int,onlinePlayersPercent:float,rows:array<int,array{label:string,value:string,valueStyle:string}>}
     */
    private function serverInfoData(): array
    {
        $rows = [];
        $serverInfoCache = LoadCacheData('server_info.cache');
        $srvInfo = null;
        if (is_array($serverInfoCache) && isset($serverInfoCache[1][0]) && is_string($serverInfoCache[1][0])) {
            $srvInfo = explode('|', $serverInfoCache[1][0]);
        }

        $onlinePlayers = isset($srvInfo[3]) && is_numeric($srvInfo[3]) ? (int) $srvInfo[3] : 0;
        $maxOnlineRaw = config('maximum_online', true);
        $maxOnline = is_numeric($maxOnlineRaw) ? (float) $maxOnlineRaw : 0.0;
        $showOnlineCounter = check_value($maxOnlineRaw);
        $onlinePlayersPercent = $showOnlineCounter && $maxOnline > 0 ? ($onlinePlayers * 100 / $maxOnline) : 0.0;

        $configRowMap = [
            'server_info_season' => 'sidebar_srvinfo_txt_6',
            'server_info_exp' => 'sidebar_srvinfo_txt_7',
            'server_info_masterexp' => 'sidebar_srvinfo_txt_8',
            'server_info_drop' => 'sidebar_srvinfo_txt_9',
        ];

        foreach ($configRowMap as $configKey => $phraseKey) {
            $value = config($configKey, true);
            if (!check_value($value)) {
                continue;
            }
            $rows[] = [
                'label' => (string) lang($phraseKey),
                'value' => (string) $value,
                'valueStyle' => '',
            ];
        }

        if (is_array($srvInfo)) {
            $rows[] = [
                'label' => (string) lang('sidebar_srvinfo_txt_2'),
                'value' => number_format((int) $srvInfo[0]),
                'valueStyle' => 'font-weight:bold;',
            ];
            $rows[] = [
                'label' => (string) lang('sidebar_srvinfo_txt_3'),
                'value' => number_format((int) ($srvInfo[1] ?? 0)),
                'valueStyle' => 'font-weight:bold;',
            ];
            $rows[] = [
                'label' => (string) lang('sidebar_srvinfo_txt_4'),
                'value' => number_format((int) ($srvInfo[2] ?? 0)),
                'valueStyle' => 'font-weight:bold;',
            ];
        }

        if ($showOnlineCounter) {
            $rows[] = [
                'label' => (string) lang('sidebar_srvinfo_txt_5'),
                'value' => number_format($onlinePlayers),
                'valueStyle' => 'color:#00aa00;font-weight:bold;',
            ];
        }

        return [
            'showOnlineCounter' => $showOnlineCounter,
            'onlinePlayers' => $onlinePlayers,
            'onlinePlayersPercent' => $onlinePlayersPercent,
            'rows' => $rows,
        ];
    }

    /**
     * @param array{showOnlineCounter:bool,onlinePlayers:int,onlinePlayersPercent:float,rows:array<int,array{label:string,value:string,valueStyle:string}>} $serverInfo
     * @return array<string, mixed>
     */
    private function sidebarData(array $serverInfo, bool $isLoggedIn): array
    {
        return [
            'showLoginPanel' => !$isLoggedIn,
            'loginTitle' => (string) lang('module_titles_txt_2'),
            'forgotPasswordUrl' => __BASE_URL__ . 'forgotpassword',
            'forgotPasswordLabel' => (string) lang('login_txt_4'),
            'loginActionUrl' => __BASE_URL__ . 'login',
            'loginUsernameLabel' => (string) lang('login_txt_1'),
            'loginPasswordLabel' => (string) lang('login_txt_2'),
            'loginButtonLabel' => (string) lang('login_txt_3'),
            'showUsercpPanel' => $isLoggedIn,
            'usercpTitle' => (string) lang('usercp_menu_title'),
            'sidebarLogoutUrl' => __BASE_URL__ . 'logout',
            'sidebarLogoutLabel' => (string) lang('login_txt_6'),
            'usercpMenuHtml' => $isLoggedIn ? $this->renderUsercpMenuHtml() : '',
            'joinBannerUrl' => __BASE_URL__ . 'register',
            'joinBannerImageUrl' => __PATH_THEME_IMG__ . 'sidebar_banner_join.jpg',
            'joinBannerAlt' => (string) lang('menu_txt_3'),
            'downloadBannerUrl' => __BASE_URL__ . 'downloads',
            'downloadBannerImageUrl' => __PATH_THEME_IMG__ . 'sidebar_banner_download.jpg',
            'downloadBannerAlt' => (string) lang('module_titles_txt_8'),
            'showServerInfoPanel' => $serverInfo['rows'] !== [],
            'serverInfoTitle' => (string) lang('sidebar_srvinfo_txt_1'),
            'serverInfoRows' => $serverInfo['rows'],
            'castleSiegeWidgetHtml' => $this->renderCastleSiegeWidgetHtml(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footerData(): array
    {
        return [
            'links' => [
                ['url' => __BASE_URL__ . 'tos/', 'label' => (string) lang('footer_terms')],
                ['url' => __BASE_URL__ . 'privacy/', 'label' => (string) lang('footer_privacy')],
                ['url' => __BASE_URL__ . 'refunds/', 'label' => (string) lang('footer_refund')],
                ['url' => __BASE_URL__ . 'info/', 'label' => (string) lang('footer_info')],
                ['url' => __BASE_URL__ . 'contact/', 'label' => (string) lang('footer_contact')],
            ],
            'copyrightText' => (string) langf('footer_copyright', [(string) config('server_name', true), date('Y')]),
            'poweredByHtml' => '<p style="font-size:11px;color:#aaa;">Powered by <a href="https://darkheim.net" target="_blank" style="color:#aaa;">DarkCore CMS</a> v' . __CMS_VERSION__ . '</p>',
            'socialLinks' => [
                ['url' => (string) config('social_link_facebook', true), 'imageUrl' => __PATH_THEME_IMG__ . 'social/facebook.svg', 'alt' => 'Facebook'],
                ['url' => (string) config('social_link_instagram', true), 'imageUrl' => __PATH_THEME_IMG__ . 'social/instagram.svg', 'alt' => 'Instagram'],
                ['url' => (string) config('social_link_discord', true), 'imageUrl' => __PATH_THEME_IMG__ . 'social/discord.svg', 'alt' => 'Discord'],
            ],
        ];
    }

    private function renderNavbarHtml(): string
    {
        return $this->renderMenuHtml('navbar');
    }

    private function renderMenuHtml(string $configName, bool $withIcons = false): string
    {
        $config = loadConfig($configName);
        if (!is_array($config)) {
            return '';
        }

        $html = '<ul>';
        foreach ($config as $element) {
            if (!is_array($element)) {
                continue;
            }
            if (empty($element['active'])) {
                continue;
            }
            if (!$this->isVisibleForCurrentUser((string) ($element['visibility'] ?? 'guest'))) {
                continue;
            }

            $title = check_value(lang((string) ($element['phrase'] ?? ''), true))
                ? (string) lang((string) $element['phrase'], true)
                : 'Unk_phrase';
            $link = ((string) ($element['type'] ?? '') === 'internal')
                ? __BASE_URL__ . (string) ($element['link'] ?? '')
                : (string) ($element['link'] ?? '');
            $target = !empty($element['newtab']) ? ' target="_blank"' : '';
            $iconHtml = '';
            if ($withIcons) {
                $icon = check_value((string) ($element['icon'] ?? ''))
                    ? __PATH_THEME_IMG__ . 'icons/' . (string) $element['icon']
                    : __PATH_THEME_IMG__ . 'icons/usercp_default.png';
                $iconHtml = '<img src="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '">';
            }

            $html .= '<li>' . $iconHtml . '<a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '"' . $target . '>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</a></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    private function isVisibleForCurrentUser(string $visibility): bool
    {
        $isLoggedIn = $this->isLoggedInStrict();
        if ($visibility === 'guest' && $isLoggedIn) {
            return false;
        }
        if ($visibility === 'user' && !$isLoggedIn) {
            return false;
        }
        return true;
    }

    private function isLoggedInStrict(): bool
    {
        return isLoggedIn() === true;
    }

    private function renderCastleSiegeWidgetHtml(): string
    {
        $castleSiege = new CastleSiege();
        if (!$castleSiege->showWidget()) {
            return '';
        }

        $siegeData = $castleSiege->siegeData();
        if (!is_array($siegeData) || !is_array($siegeData['castle_data'] ?? null)) {
            return '';
        }

        if (($siegeData['castle_data'][_CLMN_MCD_OCCUPY_] ?? 0) == 1) {
            $guildOwner = guildProfile((string) $siegeData['castle_data'][_CLMN_MCD_GUILD_OWNER_]);
            $guildOwnerMark = (string) ($siegeData['castle_owner_alliance'][0][_CLMN_GUILD_LOGO_] ?? '');
            $guildMaster = playerProfile((string) ($siegeData['castle_owner_alliance'][0][_CLMN_GUILD_MASTER_] ?? ''));
        } else {
            $guildOwner = '-';
            $guildOwnerMark = '1111111111111111111111111114411111144111111111111111111111111111';
            $guildMaster = '-';
        }

        $html = '<div class="panel castle-owner-widget">';
        $html .= '<div class="panel-heading"><h3 class="panel-title">' . htmlspecialchars((string) lang('castlesiege_widget_title'), ENT_QUOTES, 'UTF-8') . '</h3></div>';
        $html .= '<div class="panel-body">';
        $html .= '<div class="row">';
        $html .= '<div class="col-sm-6 text-center">' . returnGuildLogo($guildOwnerMark, 100) . '</div>';
        $html .= '<div class="col-sm-6">';
        $html .= '<span class="alt">' . htmlspecialchars((string) lang('castlesiege_txt_2'), ENT_QUOTES, 'UTF-8') . '</span><br />';
        $html .= $guildOwner . '<br /><br />';
        $html .= '<span class="alt">' . htmlspecialchars((string) lang('castlesiege_txt_12'), ENT_QUOTES, 'UTF-8') . '</span><br />';
        $html .= $guildMaster;
        $html .= '</div></div>';
        $html .= '<div class="row" style="margin-top: 20px;">';
        $html .= '<div class="col-sm-12 text-center">';
        $html .= '<span class="alt">' . htmlspecialchars((string) lang('castlesiege_txt_21'), ENT_QUOTES, 'UTF-8') . '</span><br />';
        $html .= htmlspecialchars((string) ($siegeData['current_stage']['title'] ?? ''), ENT_QUOTES, 'UTF-8') . '<br /><br />';
        $html .= '<span class="alt">' . htmlspecialchars((string) lang('castlesiege_txt_1'), ENT_QUOTES, 'UTF-8') . '</span><br />';
        $html .= htmlspecialchars((string) ($siegeData['warfare_stage_countdown'] ?? ''), ENT_QUOTES, 'UTF-8') . '<br /><br />';
        $html .= '<a href="' . htmlspecialchars(__BASE_URL__ . 'castlesiege', ENT_QUOTES, 'UTF-8') . '" class="btn btn-castlewidget btn-xs">' . htmlspecialchars((string) lang('castlesiege_txt_7'), ENT_QUOTES, 'UTF-8') . '</a>';
        $html .= '</div></div></div></div>';

        return $html;
    }

    private function renderLanguageSwitcherHtml(): string
    {
        $languageList = [
            'en' => ['English', 'US'],
            'es' => ['Español', 'ES'],
            'br' => ['Português', 'BR'],
            'ro' => ['Romanian', 'RO'],
            'cn' => ['Simplified Chinese', 'CN'],
            'ru' => ['Russian', 'RU'],
        ];

        $currentLanguage = isset($_SESSION['language_display'])
            ? (string) $_SESSION['language_display']
            : (string) config('language_default', true);
        if (!isset($languageList[$currentLanguage])) {
            $currentLanguage = 'en';
        }

        $html = '<ul class="dh-lang-switcher">';
        $currentInfo = $languageList[$currentLanguage];
        $html .= '<li><a href="' . htmlspecialchars(__BASE_URL__ . 'language/switch/to/' . strtolower($currentLanguage), ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars($currentInfo[0], ENT_QUOTES, 'UTF-8') . '"><img src="' . htmlspecialchars(getCountryFlag($currentInfo[1]), ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($currentInfo[0], ENT_QUOTES, 'UTF-8') . '" /> ' . htmlspecialchars(strtoupper($currentLanguage), ENT_QUOTES, 'UTF-8') . '</a></li>';
        foreach ($languageList as $language => $languageInfo) {
            if ($language === $currentLanguage) {
                continue;
            }
            $html .= '<li><a href="' . htmlspecialchars(__BASE_URL__ . 'language/switch/to/' . strtolower($language), ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars($languageInfo[0], ENT_QUOTES, 'UTF-8') . '"><img src="' . htmlspecialchars(getCountryFlag($languageInfo[1]), ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($languageInfo[0], ENT_QUOTES, 'UTF-8') . '" /> ' . htmlspecialchars(strtoupper($language), ENT_QUOTES, 'UTF-8') . '</a></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    private function versionedUrl(string $publicBase, string $absolutePath, ?string $fileName = null): string
    {
        $resolvedFileName = $fileName ?? basename($absolutePath);
        $version = is_file($absolutePath) ? (string) filemtime($absolutePath) : '1';
        return $publicBase . $resolvedFileName . '?v=' . $version;
    }
}

