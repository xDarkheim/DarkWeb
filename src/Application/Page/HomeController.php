<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\Auth\SessionManager;
use Darkheim\Application\Game\GameHelper;
use Darkheim\Application\Profile\ProfileRenderer;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Cache\CacheRepository;
use Darkheim\Infrastructure\Theme\DefaultThemeLayoutBuilder;
use Darkheim\Infrastructure\View\ViewRenderer;

final class HomeController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $cmsConfig = BootstrapContext::configProvider()?->cms() ?? [];
        $language = ((bool) ($cmsConfig['language_switch_active'] ?? false) && isset($_SESSION['language_display']))
            ? $_SESSION['language_display']
            : '';

        // News feed (from cache)
        $newsItems = [];
        $cacheRepo = new CacheRepository(__PATH_CACHE__);
        $newsList  = $cacheRepo->load('news.cache');
        if (is_array($newsList)) {
            foreach (array_slice($newsList, 0, 7, true) as $article) {
                $title = (string) ($article['news_title'] ?? '');
                if ($language !== '' && isset($article['translations'][$language])) {
                    $decoded = base64_decode($article['translations'][$language], true);
                    if ($decoded !== false && $decoded !== '' && mb_check_encoding($decoded, 'UTF-8')) {
                        $title = $decoded;
                    }
                }
                if (! mb_check_encoding($title, 'UTF-8')) {
                    $conv = @mb_convert_encoding($title, 'UTF-8', 'Windows-1252');
                    if (is_string($conv) && mb_check_encoding($conv, 'UTF-8')) {
                        $title = $conv;
                    }
                }
                $newsTimestamp = is_numeric($article['news_date'] ?? null) ? (int) $article['news_date'] : 0;
                $newsItems[]   = [
                    'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
                    'url'   => __BASE_URL__ . 'news/' . $article['news_id'] . '/',
                    'date'  => $newsTimestamp > 0 ? date('Y/m/d', $newsTimestamp) : '-',
                ];
            }
        }

        // Top Level ranking
        $rankingsConfig = BootstrapContext::configProvider()?->moduleConfig('rankings');
        $topLevelData   = [];
        $levelCache     = $cacheRepo->loadLegacyText('rankings_level.cache');
        if (is_array($levelCache)) {
            foreach (array_slice($levelCache, 1, 10) as $row) {
                $levelValue     = is_numeric($row[2] ?? null) ? (float) $row[2] : 0.0;
                $topLevelData[] = [
                    'name'  => ProfileRenderer::player((string) $row[0]),
                    'class' => GameHelper::playerClass((int) $row[1]),
                    'level' => number_format($levelValue),
                ];
            }
        }

        // Top Guilds ranking
        $topGuildData = [];
        $guildCache   = $cacheRepo->loadLegacyText('rankings_guilds.cache');
        if (is_array($guildCache)) {
            $multiplier = ($rankingsConfig['guild_score_formula'] ?? 1) == 1
                ? 1
                : ($rankingsConfig['guild_score_multiplier'] ?? 1);
            $multiplier = is_numeric($multiplier) ? (float) $multiplier : 1.0;
            foreach (array_slice($guildCache, 1, 10) as $row) {
                $rawGuildScore  = is_numeric($row[2] ?? null) ? (float) $row[2] : 0.0;
                $scoreValue     = floor($rawGuildScore * $multiplier);
                $topGuildData[] = [
                    'name'  => ProfileRenderer::guild((string) $row[0]),
                    'logo'  => GameHelper::guildLogo((string) $row[3], 20),
                    'score' => number_format($scoreValue),
                ];
            }
        }

        $userLoggedIn   = (new SessionManager())->isWebsiteAuthenticated(BootstrapContext::configProvider()?->moduleConfig('login'));
        $usercpMenuHtml = $userLoggedIn ? new DefaultThemeLayoutBuilder()->renderUsercpMenuHtml() : '';

        $this->view->render('home', [
            'newsItems'      => $newsItems,
            'hasNews'        => ! empty($newsItems),
            'topLevelData'   => $topLevelData,
            'topGuildData'   => $topGuildData,
            'userLoggedIn'   => $userLoggedIn,
            'usercpMenuHtml' => $usercpMenuHtml,
            'baseUrl'        => __BASE_URL__,
            'usercpUrl'      => __BASE_URL__ . 'usercp/',
            'rankLevelUrl'   => __BASE_URL__ . 'rankings/level',
            'rankGuildsUrl'  => __BASE_URL__ . 'rankings/guilds',
            'sidebarBanner'  => __PATH_THEME_IMG__ . 'sidebar_banner_join.jpg',
        ]);
    }
}
