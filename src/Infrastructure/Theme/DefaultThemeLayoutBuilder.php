<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Theme;

use Darkheim\Application\Auth\AdminGuard;
use Darkheim\Application\Auth\SessionManager;
use Darkheim\Application\CastleSiege\CastleSiege;
use Darkheim\Application\Game\GameHelper;
use Darkheim\Application\Language\Translator;
use Darkheim\Application\News\NewsRepository;
use Darkheim\Application\Profile\ProfileRenderer;
use Darkheim\Application\Rankings\RankingRepository;
use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Cache\CacheRepository;
use Darkheim\Infrastructure\Http\GeoIpService;

final class DefaultThemeLayoutBuilder
{
    /**
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function build(string $currentPage, string $currentSubpage): array
    {
        $cmsConfig  = BootstrapContext::configProvider()?->cms() ?? [];
        $serverInfo = $this->serverInfoData();
        $isLoggedIn = $this->isLoggedInStrict();
        $serverName = (string) ($cmsConfig['server_name'] ?? '');

        return [
            'htmlLang'               => htmlspecialchars((string) ($cmsConfig['language_default'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'seoTitle'               => htmlspecialchars((string) ($cmsConfig['website_title'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'seoDescription'         => htmlspecialchars((string) ($cmsConfig['website_meta_description'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'seoKeywords'            => htmlspecialchars((string) ($cmsConfig['website_meta_keywords'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'seoSiteName'            => htmlspecialchars($serverName, ENT_QUOTES, 'UTF-8'),
            'seoUrl'                 => htmlspecialchars(__BASE_URL__, ENT_QUOTES, 'UTF-8'),
            'seoImage'               => htmlspecialchars(__PATH_IMG__ . 'brand.jpg', ENT_QUOTES, 'UTF-8'),
            'stylesheetHrefs'        => $this->stylesheetHrefs(),
            'baseUrl'                => __BASE_URL__,
            'showLanguageSwitcher'   => (bool) ($cmsConfig['language_switch_active'] ?? false),
            'languageSwitcherHtml'   => $this->renderLanguageSwitcherHtml(),
            'topBarIsLoggedIn'       => $isLoggedIn,
            'topBarShowAdmincp'      => $isLoggedIn && AdminGuard::canAccess((string) ($_SESSION['username'] ?? '')),
            'topBarUsercpLabel'      => Translator::phrase('module_titles_txt_3'),
            'topBarLogoutLabel'      => Translator::phrase('menu_txt_6'),
            'topBarRegisterLabel'    => Translator::phrase('menu_txt_3'),
            'topBarLoginLabel'       => Translator::phrase('menu_txt_4'),
            'usercpUrl'              => __BASE_URL__ . 'usercp/',
            'admincpUrl'             => __BASE_URL__ . 'admincp/',
            'logoutUrl'              => __BASE_URL__ . 'logout/',
            'registerUrl'            => __BASE_URL__ . 'register/',
            'loginUrl'               => __BASE_URL__ . 'login/',
            'navbarHtml'             => $this->renderNavbarHtml(),
            'brandHomeUrl'           => __BASE_URL__,
            'brandLogoUrl'           => __PATH_THEME_IMG__ . 'logo.png',
            'brandTitleAttr'         => htmlspecialchars($serverName, ENT_QUOTES, 'UTF-8'),
            'brandAlt'               => htmlspecialchars($serverName, ENT_QUOTES, 'UTF-8'),
            'showOnlineCounter'      => $serverInfo['showOnlineCounter'],
            'onlineLabel'            => Translator::phrase('sidebar_srvinfo_txt_5'),
            'onlinePlayersFormatted' => number_format($serverInfo['onlinePlayers']),
            'onlinePlayersPercent'   => $serverInfo['onlinePlayersPercent'],
            'serverTimeLabel'        => Translator::phrase('server_time'),
            'userTimeLabel'          => Translator::phrase('user_time'),
            'showSidebarLayout'      => $currentPage === 'usercp' && $currentSubpage !== '',
            'moduleColumnClass'      => $currentPage === 'usercp' && $currentSubpage !== '' ? 'col-xs-8' : 'col-xs-12',
            'sidebarColumnClass'     => 'col-xs-4',
            'sidebarData'            => $this->sidebarData($serverInfo, $isLoggedIn),
            'footerData'             => $this->footerData(),
            'mainJsUrl'              => $this->versionedUrl(__PATH_THEME_JS__, __PATH_THEME_ROOT__ . 'js/main.js'),
            'eventsJsUrl'            => $this->versionedUrl(__PATH_THEME_JS__, __PATH_THEME_ROOT__ . 'js/events.js'),
            'componentsJsUrl'        => $this->versionedUrl(__PATH_ASSETS_JS__, __PUBLIC_DIR__ . 'assets/js/components.js'),
            'widgetData'             => $this->widgetData($currentPage, $currentSubpage),
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
        $assetFiles   = ['variables', 'toast', 'auth', 'ucp', 'myaccount', 'profiles', 'info', 'tos', 'news', 'rankings', 'panels', 'paypal', 'downloads', 'castlesiege'];
        foreach ($assetFiles as $assetFile) {
            $path = $assetsCssDir . $assetFile . '.css';
            if (! is_file($path)) {
                continue;
            }
            $stylesheets[] = $this->versionedUrl(__PATH_ASSETS_CSS__, $path, $assetFile . '.css');
        }

        $stylesheets[] = $this->versionedUrl(__PATH_THEME_CSS__, __PATH_THEME_ROOT__ . 'css/override.css');

        return array_values(array_filter($stylesheets, static fn(?string $href): bool => is_string($href) && $href !== ''));
    }

    /**
     * @return array{showOnlineCounter:bool,onlinePlayers:int,onlinePlayersPercent:float,rows:array<int,array{label:string,value:string,valueStyle:string}>}
     * @throws \Exception
     */
    private function serverInfoData(): array
    {
        $rows            = [];
        $serverInfoCache = new CacheRepository(__PATH_CACHE__)->loadLegacyText('server_info.cache');
        $srvInfo         = null;
        if (is_array($serverInfoCache) && isset($serverInfoCache[1][0]) && is_string($serverInfoCache[1][0])) {
            $srvInfo = explode('|', $serverInfoCache[1][0]);
        }

        $onlinePlayers        = isset($srvInfo[3]) && is_numeric($srvInfo[3]) ? (int) $srvInfo[3] : 0;
        $cmsConfig            = BootstrapContext::configProvider()?->cms() ?? [];
        $maxOnlineRaw         = (string) ($cmsConfig['maximum_online'] ?? '');
        $maxOnline            = is_numeric($maxOnlineRaw) ? (float) $maxOnlineRaw : 0.0;
        $showOnlineCounter    = Validator::hasValue($maxOnlineRaw);
        $onlinePlayersPercent = $showOnlineCounter && $maxOnline > 0 ? ($onlinePlayers * 100 / $maxOnline) : 0.0;

        $configRowMap = [
            'server_info_season'    => 'sidebar_srvinfo_txt_6',
            'server_info_exp'       => 'sidebar_srvinfo_txt_7',
            'server_info_masterexp' => 'sidebar_srvinfo_txt_8',
            'server_info_drop'      => 'sidebar_srvinfo_txt_9',
        ];

        foreach ($configRowMap as $configKey => $phraseKey) {
            $value = (string) ($cmsConfig[$configKey] ?? '');
            if (! Validator::hasValue($value)) {
                continue;
            }
            $rows[] = [
                'label'      => Translator::phrase($phraseKey),
                'value'      => $value,
                'valueStyle' => '',
            ];
        }

        if (is_array($srvInfo)) {
            $rows[] = [
                'label'      => Translator::phrase('sidebar_srvinfo_txt_2'),
                'value'      => number_format((int) $srvInfo[0]),
                'valueStyle' => 'font-weight:bold;',
            ];
            $rows[] = [
                'label'      => Translator::phrase('sidebar_srvinfo_txt_3'),
                'value'      => number_format((int) ($srvInfo[1] ?? 0)),
                'valueStyle' => 'font-weight:bold;',
            ];
            $rows[] = [
                'label'      => Translator::phrase('sidebar_srvinfo_txt_4'),
                'value'      => number_format((int) ($srvInfo[2] ?? 0)),
                'valueStyle' => 'font-weight:bold;',
            ];
        }

        if ($showOnlineCounter) {
            $rows[] = [
                'label'      => Translator::phrase('sidebar_srvinfo_txt_5'),
                'value'      => number_format($onlinePlayers),
                'valueStyle' => 'color:#00aa00;font-weight:bold;',
            ];
        }

        return [
            'showOnlineCounter'    => $showOnlineCounter,
            'onlinePlayers'        => $onlinePlayers,
            'onlinePlayersPercent' => $onlinePlayersPercent,
            'rows'                 => $rows,
        ];
    }

    /**
     * @param array{showOnlineCounter:bool,onlinePlayers:int,onlinePlayersPercent:float,rows:array<int,array{label:string,value:string,valueStyle:string}>} $serverInfo
     * @return array<string, mixed>
     */
    private function sidebarData(array $serverInfo, bool $isLoggedIn): array
    {
        return [
            'showLoginPanel'         => ! $isLoggedIn,
            'loginTitle'             => Translator::phrase('module_titles_txt_2'),
            'forgotPasswordUrl'      => __BASE_URL__ . 'forgotpassword',
            'forgotPasswordLabel'    => Translator::phrase('login_txt_4'),
            'loginActionUrl'         => __BASE_URL__ . 'login',
            'loginUsernameLabel'     => Translator::phrase('login_txt_1'),
            'loginPasswordLabel'     => Translator::phrase('login_txt_2'),
            'loginButtonLabel'       => Translator::phrase('login_txt_3'),
            'showUsercpPanel'        => $isLoggedIn,
            'usercpTitle'            => Translator::phrase('usercp_menu_title'),
            'sidebarLogoutUrl'       => __BASE_URL__ . 'logout',
            'sidebarLogoutLabel'     => Translator::phrase('login_txt_6'),
            'usercpMenuHtml'         => $isLoggedIn ? $this->renderUsercpMenuHtml() : '',
            'joinBannerUrl'          => __BASE_URL__ . 'register',
            'joinBannerImageUrl'     => __PATH_THEME_IMG__ . 'sidebar_banner_join.jpg',
            'joinBannerAlt'          => Translator::phrase('menu_txt_3'),
            'downloadBannerUrl'      => __BASE_URL__ . 'downloads',
            'downloadBannerImageUrl' => __PATH_THEME_IMG__ . 'sidebar_banner_download.jpg',
            'downloadBannerAlt'      => Translator::phrase('module_titles_txt_8'),
            'showServerInfoPanel'    => $serverInfo['rows'] !== [],
            'serverInfoTitle'        => Translator::phrase('sidebar_srvinfo_txt_1'),
            'serverInfoRows'         => $serverInfo['rows'],
            'castleSiegeWidgetHtml'  => $this->renderCastleSiegeWidgetHtml(),
        ];
    }

    /**
     * @return array<string, mixed>
     * @throws \Exception
     */
    private function footerData(): array
    {
        $cmsConfig = BootstrapContext::configProvider()?->cms() ?? [];
        return [
            'links' => [
                ['url' => __BASE_URL__ . 'tos/', 'label' => Translator::phrase('footer_terms')],
                ['url' => __BASE_URL__ . 'privacy/', 'label' => Translator::phrase('footer_privacy')],
                ['url' => __BASE_URL__ . 'refunds/', 'label' => Translator::phrase('footer_refund')],
                ['url' => __BASE_URL__ . 'info/', 'label' => Translator::phrase('footer_info')],
                ['url' => __BASE_URL__ . 'contact/', 'label' => Translator::phrase('footer_contact')],
            ],
            'copyrightText' => Translator::phraseFmt('footer_copyright', [(string) ($cmsConfig['server_name'] ?? ''), date('Y')]),
            'poweredByHtml' => '<p style="font-size:11px;color:#aaa;">Powered by <a href="https://darkheim.net" target="_blank" style="color:#aaa;">DarkCore CMS</a> v' . __CMS_VERSION__ . '</p>',
            'socialLinks'   => [
                ['url' => (string) ($cmsConfig['social_link_facebook'] ?? ''), 'imageUrl' => __PATH_THEME_IMG__ . 'social/facebook.svg', 'alt' => 'Facebook'],
                ['url' => (string) ($cmsConfig['social_link_instagram'] ?? ''), 'imageUrl' => __PATH_THEME_IMG__ . 'social/instagram.svg', 'alt' => 'Instagram'],
                ['url' => (string) ($cmsConfig['social_link_discord'] ?? ''), 'imageUrl' => __PATH_THEME_IMG__ . 'social/discord.svg', 'alt' => 'Discord'],
            ],
        ];
    }

    private function renderNavbarHtml(): string
    {
        return $this->renderMenuHtml('navbar');
    }

    private function renderMenuHtml(string $configName, bool $withIcons = false): string
    {
        $config = BootstrapContext::configProvider()?->config($configName);
        if (! is_array($config)) {
            return '';
        }

        $html = '<ul>';
        foreach ($config as $element) {
            if (! is_array($element)) {
                continue;
            }
            if (empty($element['active'])) {
                continue;
            }
            if (! $this->isVisibleForCurrentUser((string) ($element['visibility'] ?? 'guest'))) {
                continue;
            }

            $title = Validator::hasValue(Translator::phrase((string) ($element['phrase'] ?? '')))
                ? Translator::phrase((string) $element['phrase'])
                : 'Unk_phrase';
            $link = ((string) ($element['type'] ?? '') === 'internal')
                ? __BASE_URL__ . ($element['link'] ?? '')
                : (string) ($element['link'] ?? '');
            $target   = ! empty($element['newtab']) ? ' target="_blank"' : '';
            $iconHtml = '';
            if ($withIcons) {
                $icon = Validator::hasValue((string) ($element['icon'] ?? ''))
                    ? __PATH_THEME_IMG__ . 'icons/' . $element['icon']
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
        return ! ($visibility === 'user' && ! $isLoggedIn)


        ;
    }

    private function isLoggedInStrict(): bool
    {
        return new SessionManager()->isWebsiteAuthenticated(BootstrapContext::configProvider()?->moduleConfig('login'));
    }

    private function renderCastleSiegeWidgetHtml(): string
    {
        $castleSiege = new CastleSiege();
        if (! $castleSiege->showWidget()) {
            return '';
        }

        $siegeData = $castleSiege->siegeData();
        if (! is_array($siegeData) || ! is_array($siegeData['castle_data'] ?? null)) {
            return '';
        }

        if (($siegeData['castle_data'][_CLMN_MCD_OCCUPY_] ?? 0) == 1) {
            $guildOwner     = ProfileRenderer::guild((string) $siegeData['castle_data'][_CLMN_MCD_GUILD_OWNER_]);
            $guildOwnerMark = (string) ($siegeData['castle_owner_alliance'][0][_CLMN_GUILD_LOGO_] ?? '');
            $guildMaster    = ProfileRenderer::player((string) ($siegeData['castle_owner_alliance'][0][_CLMN_GUILD_MASTER_] ?? ''));
        } else {
            $guildOwner     = '-';
            $guildOwnerMark = '1111111111111111111111111114411111144111111111111111111111111111';
            $guildMaster    = '-';
        }

        $html = '<div class="panel castle-owner-widget">';
        $html .= '<div class="panel-heading"><h3 class="panel-title">' . htmlspecialchars(
            Translator::phrase('castlesiege_widget_title'),
            ENT_QUOTES,
            'UTF-8',
        ) . '</h3></div>';
        $html .= '<div class="panel-body">';
        $html .= '<div class="row">';
        $html .= '<div class="col-sm-6 text-center">' . GameHelper::guildLogo($guildOwnerMark, 100) . '</div>';
        $html .= '<div class="col-sm-6">';
        $html .= '<span class="alt">' . htmlspecialchars(
            Translator::phrase('castlesiege_txt_2'),
            ENT_QUOTES,
            'UTF-8',
        ) . '</span><br />';
        $html .= $guildOwner . '<br /><br />';
        $html .= '<span class="alt">' . htmlspecialchars(
            Translator::phrase('castlesiege_txt_12'),
            ENT_QUOTES,
            'UTF-8',
        ) . '</span><br />';
        $html .= $guildMaster;
        $html .= '</div></div>';
        $html .= '<div class="row" style="margin-top: 20px;">';
        $html .= '<div class="col-sm-12 text-center">';
        $html .= '<span class="alt">' . htmlspecialchars(
            Translator::phrase('castlesiege_txt_21'),
            ENT_QUOTES,
            'UTF-8',
        ) . '</span><br />';
        $html .= htmlspecialchars((string) ($siegeData['current_stage']['title'] ?? ''), ENT_QUOTES, 'UTF-8') . '<br /><br />';
        $html .= '<span class="alt">' . htmlspecialchars(
            Translator::phrase('castlesiege_txt_1'),
            ENT_QUOTES,
            'UTF-8',
        ) . '</span><br />';
        $html .= htmlspecialchars((string) ($siegeData['warfare_stage_countdown'] ?? ''), ENT_QUOTES, 'UTF-8') . '<br /><br />';
        $html .= '<a href="' . htmlspecialchars(__BASE_URL__ . 'castlesiege', ENT_QUOTES, 'UTF-8') . '" class="btn btn-castlewidget btn-xs">' . htmlspecialchars(
            Translator::phrase('castlesiege_txt_7'),
            ENT_QUOTES,
            'UTF-8',
        ) . '</a>';
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
            : (string) BootstrapContext::cmsValue('language_default', 'en');
        if (! isset($languageList[$currentLanguage])) {
            $currentLanguage = 'en';
        }

        $html        = '<ul class="dh-lang-switcher">';
        $currentInfo = $languageList[$currentLanguage];
        $html .= '<li><a href="' . htmlspecialchars(__BASE_URL__ . 'language/switch/to/' . strtolower($currentLanguage), ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars($currentInfo[0], ENT_QUOTES, 'UTF-8') . '"><img src="' . htmlspecialchars(GeoIpService::flagUrl($currentInfo[1]), ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($currentInfo[0], ENT_QUOTES, 'UTF-8') . '" /> ' . htmlspecialchars(strtoupper($currentLanguage), ENT_QUOTES, 'UTF-8') . '</a></li>';
        foreach ($languageList as $language => $languageInfo) {
            if ($language === $currentLanguage) {
                continue;
            }
            $html .= '<li><a href="' . htmlspecialchars(__BASE_URL__ . 'language/switch/to/' . strtolower($language), ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars($languageInfo[0], ENT_QUOTES, 'UTF-8') . '"><img src="' . htmlspecialchars(GeoIpService::flagUrl($languageInfo[1]), ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($languageInfo[0], ENT_QUOTES, 'UTF-8') . '" /> ' . htmlspecialchars(strtoupper($language), ENT_QUOTES, 'UTF-8') . '</a></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * Ranking widget data for the darkheim theme home page and sidebar.
     * Follows the same pattern as sidebarData(): all data prepared here,
     * templates stay echo-only.
     *
     * @return array{topPlayers:array<int,array<string,mixed>>,topGuilds:array<int,array<string,mixed>>,topGens:array<int,array<string,mixed>>,sidebarTopResets:array<int,array<string,mixed>>,newsItems:array<int,array<string,string>>,usercpSidebar:array<string,mixed>}
     */
    private function widgetData(string $currentPage, string $currentSubpage): array
    {
        BootstrapContext::loadModuleConfig('rankings');

        $showCountry        = (bool) BootstrapContext::moduleValue('show_country_flags');
        $repo               = new RankingRepository(new CacheRepository(__PATH_CACHE__));
        $characterCountries = $showCountry ? $repo->loadCharacterCountries() : [];
        if ($characterCountries === []) {
            $showCountry = false;
        }

        return [
            'topPlayers'       => $this->buildWidgetTopPlayers($repo, $showCountry, $characterCountries),
            'topGuilds'        => $this->buildWidgetTopGuilds($repo),
            'topGens'          => $this->buildWidgetTopGens($repo, $showCountry, $characterCountries),
            'sidebarTopResets' => $this->buildWidgetSidebarResets($repo, $showCountry, $characterCountries),
            'newsItems'        => $this->buildNewsItems(),
            'usercpSidebar'    => $this->buildUsercpSidebarData($currentPage, $currentSubpage),
        ];
    }

    /**
     * Latest news titles for the home-page news widget.
     *
     * @return array<int,array{title:string,url:string,date:string}>
     */
    private function buildNewsItems(): array
    {
        $newsRepo = new NewsRepository(
            new CacheRepository(__PATH_CACHE__),
            defined('__PATH_NEWS_CACHE__') ? __PATH_NEWS_CACHE__ : '',
        );

        $language = (BootstrapContext::cmsValue('language_switch_active', true) && isset($_SESSION['language_display']))
            ? (string) $_SESSION['language_display']
            : '';

        $result = [];
        foreach (array_slice($newsRepo->findAll(), 0, 10) as $item) {
            $result[] = [
                'title' => $item->titleForLanguage($language),
                'url'   => $item->url(__BASE_URL__),
                'date'  => date('m/d/Y', $item->date),
            ];
        }

        return $result;
    }

    /**
     * Builds structured sidebar data for the darkheim UserCP.
     * Maps icon filenames from usercp-menu.json → Bootstrap Icon classes.
     *
     * @return array<string,mixed>
     */
    private function buildUsercpSidebarData(string $currentPage, string $currentSubpage): array
    {
        $iconMap = [
            'account.png'  => 'bi-person-circle',
            'reset.png'    => 'bi-arrow-repeat',
            'unstick.png'  => 'bi-geo-alt-fill',
            'clearpk.png'  => 'bi-shield-x',
            'fixstats.png' => 'bi-arrow-clockwise',
            'addstats.png' => 'bi-plus-circle-fill',
            'clearst.png'  => 'bi-layers',
            'vote.png'     => 'bi-star-fill',
            'donate.png'   => 'bi-currency-dollar',
            'zen.png'      => 'bi-coin',
        ];

        $menuConfig = BootstrapContext::configProvider()?->config('usercp') ?? [];
        $isLoggedIn = $this->isLoggedInStrict();
        $username   = $isLoggedIn ? (string) ($_SESSION['username'] ?? '') : '';

        $items = [];
        foreach ((array) $menuConfig as $element) {
            if (! is_array($element) || empty($element['active'])) {
                continue;
            }
            $link = (string) ($element['link'] ?? '');
            // My Account has its own fixed slot in the sidebar template
            if ($link === 'usercp/myaccount') {
                continue;
            }
            if (! $this->isVisibleForCurrentUser((string) ($element['visibility'] ?? ''))) {
                continue;
            }

            $href      = ((string) ($element['type'] ?? '') === 'internal') ? __BASE_URL__ . $link : $link;
            $iconClass = $iconMap[(string) ($element['icon'] ?? '')] ?? 'bi-circle';
            $title     = Translator::phrase((string) ($element['phrase'] ?? ''));

            // Active: subpage matches the slug after 'usercp/'
            $subpageSlug = str_starts_with($link, 'usercp/') ? substr($link, strlen('usercp/')) : '';
            $isActive    = $currentPage === 'usercp' && $subpageSlug !== '' && $currentSubpage === $subpageSlug;

            $items[] = [
                'href'      => $href,
                'iconClass' => $iconClass,
                'title'     => $title,
                'active'    => $isActive,
                'newTab'    => ! empty($element['newtab']),
            ];
        }

        return [
            'username'        => $username,
            'menuTitle'       => Translator::phrase('usercp_menu_title'),
            'dashboardActive' => $currentPage === 'usercp' && $currentSubpage === '',
            'dashboardLabel'  => Translator::phrase('module_titles_txt_3'),
            'myAccountActive' => $currentPage === 'usercp' && $currentSubpage === 'myaccount',
            'myAccountLabel'  => Translator::phrase('usercp_menu_txt_1'),
            'items'           => $items,
            'logoutLabel'     => Translator::phrase('menu_txt_6'),
        ];
    }

    /**
     * @param  array<string,string>  $characterCountries
     * @return array<int,array<string,mixed>>
     */
    private function buildWidgetTopPlayers(RankingRepository $repo, bool $showCountry, array $characterCountries): array
    {
        $cache = $repo->load('rankings_resets.cache');
        if ($cache === null) {
            return [];
        }

        $result   = [];
        $position = 1;
        foreach ($cache->entries as $entry) {
            if ($position > 7) {
                break;
            }
            if (! isset($entry[0], $entry[1], $entry[2], $entry[3])) {
                continue;
            }
            $name     = (string) $entry[0];
            $result[] = [
                'position'       => $position,
                'countryFlagUrl' => GeoIpService::flagUrl($showCountry ? ($characterCountries[$name] ?? 'default') : 'default'),
                'profileHtml'    => ProfileRenderer::player($name),
                'level'          => (int) $entry[3],
                'resets'         => (int) $entry[2],
            ];
            $position++;
        }

        return $result;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function buildWidgetTopGuilds(RankingRepository $repo): array
    {
        $cache = $repo->load('rankings_guilds.cache');
        if ($cache === null) {
            return [];
        }

        $multiplier = (int) BootstrapContext::moduleValue('guild_score_formula') === 1
            ? 1
            : (int) BootstrapContext::moduleValue('guild_score_multiplier');

        $result   = [];
        $position = 1;
        foreach ($cache->entries as $entry) {
            if ($position > 7) {
                break;
            }
            if (! isset($entry[0], $entry[1], $entry[2], $entry[3])) {
                continue;
            }
            $result[] = [
                'position'  => $position,
                'logoHtml'  => GameHelper::guildLogo((string) $entry[3], 20),
                'guildHtml' => ProfileRenderer::guild((string) $entry[0]),
                'score'     => number_format((int) floor((float) $entry[2] * $multiplier)),
            ];
            $position++;
        }

        return $result;
    }

    /**
     * @param  array<string,string>  $characterCountries
     * @return array<int,array<string,mixed>>
     */
    private function buildWidgetTopGens(RankingRepository $repo, bool $showCountry, array $characterCountries): array
    {
        $cache = $repo->load('rankings_gens.cache');
        if ($cache === null) {
            return [];
        }

        $result   = [];
        $position = 1;
        foreach ($cache->entries as $entry) {
            if ($position > 7) {
                break;
            }
            if (! isset($entry[0], $entry[1], $entry[2], $entry[3])) {
                continue;
            }
            $name     = (string) $entry[0];
            $result[] = [
                'position'       => $position,
                'countryFlagUrl' => GeoIpService::flagUrl($showCountry ? ($characterCountries[$name] ?? 'default') : 'default'),
                'profileHtml'    => ProfileRenderer::player($name),
                'contribution'   => (int) $entry[2],
                'gensFamily'     => (int) $entry[1] === 1 ? 'Duprian' : 'Vantarion',
            ];
            $position++;
        }

        return $result;
    }

    /**
     * @param  array<string,string>  $characterCountries
     * @return array<int,array<string,mixed>>
     */
    private function buildWidgetSidebarResets(RankingRepository $repo, bool $showCountry, array $characterCountries): array
    {
        $cache = $repo->load('rankings_resets.cache');
        if ($cache === null) {
            return [];
        }

        $result   = [];
        $position = 1;
        foreach ($cache->entries as $entry) {
            if ($position > 5) {
                break;
            }
            if (! isset($entry[0], $entry[1], $entry[2])) {
                continue;
            }
            $result[] = [
                'classAvatarUrl' => GameHelper::playerClassAvatar((int) $entry[1], false),
                'profileHtml'    => ProfileRenderer::player((string) $entry[0]),
                'resets'         => (int) $entry[2],
            ];
            $position++;
        }

        return $result;
    }

    private function versionedUrl(string $publicBase, string $absolutePath, ?string $fileName = null): string
    {
        $resolvedFileName = $fileName ?? basename($absolutePath);
        $version          = is_file($absolutePath) ? (string) filemtime($absolutePath) : '1';
        return $publicBase . $resolvedFileName . '?v=' . $version;
    }
}
