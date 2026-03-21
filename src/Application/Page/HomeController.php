<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

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
        $language = (config('language_switch_active', true) && isset($_SESSION['language_display']))
            ? $_SESSION['language_display']
            : '';

        // News feed (from cache)
        $newsItems = [];
        $newsList  = loadCache('news.cache');
        if (is_array($newsList)) {
            foreach (array_slice($newsList, 0, 7, true) as $article) {
                $title = (string)($article['news_title'] ?? '');
                if ($language !== '' && isset($article['translations'][$language])) {
                    $decoded = base64_decode($article['translations'][$language], true);
                    if ($decoded !== false && $decoded !== '' && mb_check_encoding($decoded, 'UTF-8')) {
                        $title = $decoded;
                    }
                }
                if (!mb_check_encoding($title, 'UTF-8')) {
                    $conv = @iconv('Windows-1252', 'UTF-8//IGNORE', $title);
                    if ($conv !== false && mb_check_encoding($conv, 'UTF-8')) $title = $conv;
                }
                $newsTimestamp = is_numeric($article['news_date'] ?? null) ? (int) $article['news_date'] : 0;
                $newsItems[] = [
                    'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
                    'url'   => __BASE_URL__ . 'news/' . $article['news_id'] . '/',
                    'date'  => $newsTimestamp > 0 ? date('Y/m/d', $newsTimestamp) : '-',
                ];
            }
        }

        // Top Level ranking
        $rankingsConfig = loadConfigurations('rankings');
        $topLevelData   = [];
        $levelCache     = LoadCacheData('rankings_level.cache');
        if (is_array($levelCache)) {
            foreach (array_slice($levelCache, 1, 10) as $row) {
                $levelValue = is_numeric($row[2] ?? null) ? (float) $row[2] : 0.0;
                $topLevelData[] = [
                    'name'   => playerProfile($row[0]),
                    'class'  => getPlayerClass($row[1]),
                    'level'  => number_format($levelValue),
                ];
            }
        }

        // Top Guilds ranking
        $topGuildData = [];
        $guildCache   = LoadCacheData('rankings_guilds.cache');
        if (is_array($guildCache)) {
            $multiplier = ($rankingsConfig['guild_score_formula'] ?? 1) == 1
                ? 1
                : ($rankingsConfig['guild_score_multiplier'] ?? 1);
            $multiplier = is_numeric($multiplier) ? (float) $multiplier : 1.0;
            foreach (array_slice($guildCache, 1, 10) as $row) {
                $rawGuildScore = is_numeric($row[2] ?? null) ? (float) $row[2] : 0.0;
                $scoreValue = floor($rawGuildScore * $multiplier);
                $topGuildData[] = [
                    'name'  => guildProfile($row[0]),
                    'logo'  => returnGuildLogo($row[3], 20),
                    'score' => number_format($scoreValue),
                ];
            }
        }

        $userLoggedIn = isLoggedIn() === true;
        $usercpMenuHtml = $userLoggedIn ? (new DefaultThemeLayoutBuilder())->renderUsercpMenuHtml() : '';

        $this->view->render('home', [
            'newsItems'      => $newsItems,
            'hasNews'        => !empty($newsItems),
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
