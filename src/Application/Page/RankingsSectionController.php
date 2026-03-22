<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\Rankings\RankingCache;
use Darkheim\Application\Rankings\RankingRepository;
use Darkheim\Application\Rankings\RankingsService;
use Darkheim\Infrastructure\Cache\CacheRepository;
use Darkheim\Infrastructure\View\ViewRenderer;

final class RankingsSectionController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        try {
            $subpage = (string) ($_REQUEST['subpage'] ?? '');
            $config = $this->subpageConfig($subpage);
            if ($config === null) {
                throw new \Exception(lang('error_58', true));
            }

            if (!mconfig('active') || !mconfig($config['featureFlag'])) {
                throw new \Exception(lang('error_44', true));
            }

            $rankings = new RankingsService();
            $repository = new RankingRepository(new CacheRepository(__PATH_CACHE__));
            $rankCache = $repository->load($config['cacheFile']);
            if (!$rankCache instanceof RankingCache) {
                throw new \Exception(lang('error_58', true));
            }

            $showCountry = $config['supportsCountry'] && (bool) mconfig('show_country_flags');
            $characterCountries = $showCountry ? $repository->loadCharacterCountries() : [];
            if ($characterCountries === []) {
                $showCountry = false;
            }

            $showOnlineStatus = (bool) mconfig('show_online_status');
            $onlineCharacters = $showOnlineStatus ? $repository->loadOnlineCharacters() : [];
            $showPlaceNumber = (bool) mconfig('rankings_show_place_number');
            $showLocation = $config['supportsLocation'] && (bool) mconfig('show_location');
            $showFilter = $config['supportsFilter'] && (bool) mconfig('rankings_class_filter');

            $this->view->render('ranking', [
                'pageTitle' => lang('module_titles_txt_10', true),
                'menuItems' => $rankings->menuItems($subpage),
                'filterItems' => $showFilter ? $this->buildFilterItems($rankings) : [],
                'tableHeaders' => $this->buildHeaders($config['columns'], $showPlaceNumber, $showCountry, $showLocation),
                'rows' => $this->buildRows(
                    $subpage,
                    $rankCache,
                    $showPlaceNumber,
                    $showCountry,
                    $showLocation,
                    $showOnlineStatus,
                    $characterCountries,
                    $onlineCharacters
                ),
                'updatedAtText' => (bool) mconfig('rankings_show_date')
                    ? lang('rankings_txt_20', true) . ' ' . date('m/d/Y - h:i A', $rankCache->timestamp)
                    : null,
            ]);
        } catch (\Exception $ex) {
            inline_message('error', $ex->getMessage());
        }
    }

    /**
     * @return array{featureFlag:string,cacheFile:string,columns:array<int,string>,supportsCountry:bool,supportsLocation:bool,supportsFilter:bool}|null
     */
    private function subpageConfig(string $subpage): ?array
    {
        return match ($subpage) {
            'level' => [
                'featureFlag' => 'rankings_enable_level',
                'cacheFile' => 'rankings_level.cache',
                'columns' => ['avatar', 'player', 'level'],
                'supportsCountry' => true,
                'supportsLocation' => true,
                'supportsFilter' => true,
            ],
            'resets' => [
                'featureFlag' => 'rankings_enable_resets',
                'cacheFile' => 'rankings_resets.cache',
                'columns' => ['avatar', 'player', 'level', 'resets'],
                'supportsCountry' => true,
                'supportsLocation' => true,
                'supportsFilter' => true,
            ],
            'killers' => [
                'featureFlag' => 'rankings_enable_pk',
                'cacheFile' => 'rankings_pk.cache',
                'columns' => ['avatar', 'player', 'level', 'pk_status', 'kills'],
                'supportsCountry' => true,
                'supportsLocation' => true,
                'supportsFilter' => true,
            ],
            'guilds' => [
                'featureFlag' => 'rankings_enable_guilds',
                'cacheFile' => 'rankings_guilds.cache',
                'columns' => ['guild', 'logo', 'master', 'score'],
                'supportsCountry' => false,
                'supportsLocation' => false,
                'supportsFilter' => false,
            ],
            'grandresets' => [
                'featureFlag' => 'rankings_enable_gr',
                'cacheFile' => 'rankings_gr.cache',
                'columns' => ['avatar', 'player', 'resets', 'grand_resets'],
                'supportsCountry' => true,
                'supportsLocation' => true,
                'supportsFilter' => true,
            ],
            'online' => [
                'featureFlag' => 'rankings_enable_online',
                'cacheFile' => 'rankings_online.cache',
                'columns' => ['avatar', 'player', 'hours'],
                'supportsCountry' => true,
                'supportsLocation' => true,
                'supportsFilter' => true,
            ],
            'votes' => [
                'featureFlag' => 'rankings_enable_votes',
                'cacheFile' => 'rankings_votes.cache',
                'columns' => ['avatar', 'player', 'votes'],
                'supportsCountry' => true,
                'supportsLocation' => true,
                'supportsFilter' => true,
            ],
            'gens' => [
                'featureFlag' => 'rankings_enable_gens',
                'cacheFile' => 'rankings_gens.cache',
                'columns' => ['avatar', 'gens', 'player', 'rank', 'contribution'],
                'supportsCountry' => true,
                'supportsLocation' => true,
                'supportsFilter' => true,
            ],
            'master' => [
                'featureFlag' => 'rankings_enable_master',
                'cacheFile' => 'rankings_master.cache',
                'columns' => ['avatar', 'player', 'level', 'master_level'],
                'supportsCountry' => true,
                'supportsLocation' => true,
                'supportsFilter' => true,
            ],
            default => null,
        };
    }

    /**
     * @param array<int,string> $columns
     * @return array<int,string>
     */
    private function buildHeaders(array $columns, bool $showPlaceNumber, bool $showCountry, bool $showLocation): array
    {
        $headers = [];
        if ($showPlaceNumber) {
            $headers[] = '';
        }
        if ($showCountry) {
            $headers[] = lang('rankings_txt_33');
        }

        foreach ($columns as $column) {
            $headers[] = match ($column) {
                'avatar' => lang('rankings_txt_11'),
                'player' => lang('rankings_txt_10'),
                'level' => lang('rankings_txt_12'),
                'resets' => lang('rankings_txt_13'),
                'kills' => lang('rankings_txt_14'),
                'hours' => lang('rankings_txt_15'),
                'guild' => lang('rankings_txt_17', true),
                'master' => lang('rankings_txt_18', true),
                'score' => lang('rankings_txt_19', true),
                'grand_resets' => lang('rankings_txt_21'),
                'master_level' => lang('rankings_txt_23'),
                'gens' => lang('rankings_txt_29'),
                'rank' => lang('rankings_txt_30'),
                'contribution' => lang('rankings_txt_31'),
                'votes' => lang('rankings_txt_32', true),
                'pk_status' => lang('rankings_txt_35'),
                'logo' => lang('rankings_txt_28', true),
                default => $column,
            };
        }

        if ($showLocation) {
            $headers[] = lang('rankings_txt_34');
        }

        return $headers;
    }

    /**
     * @return array<int, array{onclick:string,avatarHtml:string,label:string,linkClass:string}>
     */
    private function buildFilterItems(RankingsService $rankings): array
    {
        $items = [[
            'onclick' => 'rankingsFilterRemove()',
            'avatarHtml' => getPlayerClassAvatar(-1, true, false, 'rankings-class-filter-image'),
            'label' => lang('rankings_filter_1'),
            'linkClass' => 'rankings-class-filter-selection',
        ]];

        foreach ($rankings->filterItems() as $item) {
            $items[] = [
                'onclick' => 'rankingsFilterByClass(' . $item['classIds'] . ')',
                'avatarHtml' => getPlayerClassAvatar($item['classGroup'], true, false, 'rankings-class-filter-image'),
                'label' => $item['label'],
                'linkClass' => 'rankings-class-filter-selection rankings-class-filter-grayscale',
            ];
        }

        return $items;
    }

    /**
     * @param array<string,string> $characterCountries
     * @param array<int,string> $onlineCharacters
     * @return array<int, array<string, mixed>>
     */
    private function buildRows(
        string $subpage,
        RankingCache $rankCache,
        bool $showPlaceNumber,
        bool $showCountry,
        bool $showLocation,
        bool $showOnlineStatus,
        array $characterCountries,
        array $onlineCharacters,
    ): array {
        $rows = [];
        $position = 1;

        foreach ($rankCache->entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $row = match ($subpage) {
                'level' => $this->buildLevelRow($entry, $position, $showPlaceNumber, $showCountry, $showLocation, $showOnlineStatus, $characterCountries, $onlineCharacters),
                'resets' => $this->buildResetsRow($entry, $position, $showPlaceNumber, $showCountry, $showLocation, $showOnlineStatus, $characterCountries, $onlineCharacters),
                'killers' => $this->buildKillersRow($entry, $position, $showPlaceNumber, $showCountry, $showLocation, $showOnlineStatus, $characterCountries, $onlineCharacters),
                'guilds' => $this->buildGuildsRow($entry, $position, $showPlaceNumber, $showOnlineStatus, $onlineCharacters),
                'grandresets' => $this->buildGrandResetsRow($entry, $position, $showPlaceNumber, $showCountry, $showLocation, $showOnlineStatus, $characterCountries, $onlineCharacters),
                'online' => $this->buildOnlineRow($entry, $position, $showPlaceNumber, $showCountry, $showLocation, $showOnlineStatus, $characterCountries, $onlineCharacters),
                'votes' => $this->buildVotesRow($entry, $position, $showPlaceNumber, $showCountry, $showLocation, $showOnlineStatus, $characterCountries, $onlineCharacters),
                'gens' => $this->buildGensRow($entry, $position, $showPlaceNumber, $showCountry, $showLocation, $showOnlineStatus, $characterCountries, $onlineCharacters),
                'master' => $this->buildMasterRow($entry, $position, $showPlaceNumber, $showCountry, $showLocation, $showOnlineStatus, $characterCountries, $onlineCharacters),
                default => null,
            };

            if ($row !== null) {
                $rows[] = $row;
                $position++;
            }
        }

        return $rows;
    }

    /** @param array<int|string,mixed> $entry */
    private function buildLevelRow(array $entry, int $position, bool $showPlaceNumber, bool $showCountry, bool $showLocation, bool $showOnlineStatus, array $characterCountries, array $onlineCharacters): ?array
    {
        if (!isset($entry[0], $entry[1], $entry[2])) {
            return null;
        }

        $name = (string) $entry[0];
        $classId = (int) $entry[1];
        $cells = $this->characterCells($position, $name, $classId, $showPlaceNumber, $showCountry, $showOnlineStatus, $characterCountries, $onlineCharacters);
        $cells[] = number_format((int) $entry[2]);
        if ($showLocation) {
            $cells[] = returnMapName((int) ($entry[3] ?? 0));
        }

        return $this->rowModel($classId, $position, $cells);
    }

    /** @param array<int|string,mixed> $entry */
    private function buildResetsRow(array $entry, int $position, bool $showPlaceNumber, bool $showCountry, bool $showLocation, bool $showOnlineStatus, array $characterCountries, array $onlineCharacters): ?array
    {
        if (!isset($entry[0], $entry[1], $entry[2], $entry[3])) {
            return null;
        }

        $name = (string) $entry[0];
        $classId = (int) $entry[1];
        $cells = $this->characterCells($position, $name, $classId, $showPlaceNumber, $showCountry, $showOnlineStatus, $characterCountries, $onlineCharacters);
        $cells[] = number_format((int) $entry[3]);
        $cells[] = number_format((int) $entry[2]);
        if ($showLocation) {
            $cells[] = returnMapName((int) ($entry[4] ?? 0));
        }

        return $this->rowModel($classId, $position, $cells);
    }

    /** @param array<int|string,mixed> $entry */
    private function buildKillersRow(array $entry, int $position, bool $showPlaceNumber, bool $showCountry, bool $showLocation, bool $showOnlineStatus, array $characterCountries, array $onlineCharacters): ?array
    {
        if (!isset($entry[0], $entry[1], $entry[2], $entry[3], $entry[5])) {
            return null;
        }

        $name = (string) $entry[0];
        $classId = (int) $entry[1];
        $cells = $this->characterCells($position, $name, $classId, $showPlaceNumber, $showCountry, $showOnlineStatus, $characterCountries, $onlineCharacters);
        $cells[] = number_format((int) $entry[3]);
        $cells[] = returnPkLevel((int) $entry[5]);
        $cells[] = number_format((int) $entry[2]);
        if ($showLocation) {
            $cells[] = returnMapName((int) ($entry[4] ?? 0));
        }

        return $this->rowModel($classId, $position, $cells);
    }

    /** @param array<int|string,mixed> $entry */
    private function buildGuildsRow(array $entry, int $position, bool $showPlaceNumber, bool $showOnlineStatus, array $onlineCharacters): ?array
    {
        if (!isset($entry[0], $entry[1], $entry[2], $entry[3])) {
            return null;
        }

        $multiplier = (int) mconfig('guild_score_formula') === 1 ? 1 : (int) mconfig('guild_score_multiplier');
        $cells = [];
        if ($showPlaceNumber) {
            $cells[] = '<span class="rankings-table-place">' . $position . '</span>';
        }
        $cells[] = guildProfile((string) $entry[0]);
        $cells[] = returnGuildLogo((string) $entry[3], 40);
        $cells[] = playerProfile((string) $entry[1]) . $this->onlineStatusHtml((string) $entry[1], $showOnlineStatus, $onlineCharacters);
        $cells[] = number_format((int) floor((float) $entry[2] * $multiplier));

        return [
            'rowClass' => $this->rankClass($position),
            'dataClassId' => null,
            'cells' => $cells,
        ];
    }

    /** @param array<int|string,mixed> $entry */
    private function buildGrandResetsRow(array $entry, int $position, bool $showPlaceNumber, bool $showCountry, bool $showLocation, bool $showOnlineStatus, array $characterCountries, array $onlineCharacters): ?array
    {
        if (!isset($entry[0], $entry[1], $entry[2], $entry[3])) {
            return null;
        }

        $name = (string) $entry[0];
        $classId = (int) $entry[3];
        $cells = $this->characterCells($position, $name, $classId, $showPlaceNumber, $showCountry, $showOnlineStatus, $characterCountries, $onlineCharacters);
        $cells[] = number_format((int) $entry[2]);
        $cells[] = number_format((int) $entry[1]);
        if ($showLocation) {
            $cells[] = returnMapName((int) ($entry[4] ?? 0));
        }

        return $this->rowModel($classId, $position, $cells);
    }

    /** @param array<int|string,mixed> $entry */
    private function buildOnlineRow(array $entry, int $position, bool $showPlaceNumber, bool $showCountry, bool $showLocation, bool $showOnlineStatus, array $characterCountries, array $onlineCharacters): ?array
    {
        if (!isset($entry[0], $entry[1], $entry[2])) {
            return null;
        }

        $name = (string) $entry[0];
        $classId = (int) $entry[2];
        $cells = $this->characterCells($position, $name, $classId, $showPlaceNumber, $showCountry, $showOnlineStatus, $characterCountries, $onlineCharacters);
        $cells[] = number_format((int) round(((float) $entry[1]) / 60 / 60)) . ' ' . lang('rankings_txt_16', true);
        if ($showLocation) {
            $cells[] = returnMapName((int) ($entry[3] ?? 0));
        }

        return $this->rowModel($classId, $position, $cells);
    }

    /** @param array<int|string,mixed> $entry */
    private function buildVotesRow(array $entry, int $position, bool $showPlaceNumber, bool $showCountry, bool $showLocation, bool $showOnlineStatus, array $characterCountries, array $onlineCharacters): ?array
    {
        if (!isset($entry[0], $entry[1], $entry[2])) {
            return null;
        }

        $name = (string) $entry[0];
        $classId = (int) $entry[2];
        $cells = $this->characterCells($position, $name, $classId, $showPlaceNumber, $showCountry, $showOnlineStatus, $characterCountries, $onlineCharacters);
        $cells[] = number_format((int) $entry[1]);
        if ($showLocation) {
            $cells[] = returnMapName((int) ($entry[3] ?? 0));
        }

        return $this->rowModel($classId, $position, $cells);
    }

    /** @param array<int|string,mixed> $entry */
    private function buildGensRow(array $entry, int $position, bool $showPlaceNumber, bool $showCountry, bool $showLocation, bool $showOnlineStatus, array $characterCountries, array $onlineCharacters): ?array
    {
        if (!isset($entry[0], $entry[1], $entry[2], $entry[3], $entry[5])) {
            return null;
        }

        $name = (string) $entry[0];
        $classId = (int) $entry[5];
        $cells = $this->characterCells($position, $name, $classId, $showPlaceNumber, $showCountry, $showOnlineStatus, $characterCountries, $onlineCharacters);
        $playerCell = array_pop($cells);
        if ($playerCell === null) {
            return null;
        }
        $avatarCell = array_pop($cells);
        if ($avatarCell === null) {
            return null;
        }
        $prefixCells = $cells;
        $cells = $prefixCells;
        $cells[] = $avatarCell;
        $cells[] = $this->gensTypeHtml((int) $entry[1]);
        $cells[] = $playerCell;
        $cells[] = (string) $entry[3];
        $cells[] = number_format((int) $entry[2]);
        if ($showLocation) {
            $cells[] = returnMapName((int) ($entry[6] ?? 0));
        }

        return $this->rowModel($classId, $position, $cells);
    }

    /** @param array<int|string,mixed> $entry */
    private function buildMasterRow(array $entry, int $position, bool $showPlaceNumber, bool $showCountry, bool $showLocation, bool $showOnlineStatus, array $characterCountries, array $onlineCharacters): ?array
    {
        if (!isset($entry[0], $entry[1], $entry[2], $entry[3])) {
            return null;
        }

        $name = (string) $entry[0];
        $classId = (int) $entry[2];
        $cells = $this->characterCells($position, $name, $classId, $showPlaceNumber, $showCountry, $showOnlineStatus, $characterCountries, $onlineCharacters);
        $cells[] = number_format((int) $entry[3]);
        $cells[] = number_format((int) $entry[1]);
        if ($showLocation) {
            $cells[] = returnMapName((int) ($entry[4] ?? 0));
        }

        return $this->rowModel($classId, $position, $cells);
    }

    /**
     * @param array<string,string> $characterCountries
     * @param array<int,string> $onlineCharacters
     * @return array<int,string>
     */
    private function characterCells(int $position, string $name, int $classId, bool $showPlaceNumber, bool $showCountry, bool $showOnlineStatus, array $characterCountries, array $onlineCharacters): array
    {
        $cells = [];
        if ($showPlaceNumber) {
            $cells[] = '<span class="rankings-table-place">' . $position . '</span>';
        }
        if ($showCountry) {
            $countryCode = $characterCountries[$name] ?? 'default';
            $cells[] = '<img src="' . getCountryFlag($countryCode) . '" alt="' . htmlspecialchars($countryCode, ENT_QUOTES, 'UTF-8') . '" />';
        }
        $cells[] = getPlayerClassAvatar($classId, true, true, 'rankings-class-image');
        $cells[] = playerProfile($name) . $this->onlineStatusHtml($name, $showOnlineStatus, $onlineCharacters);

        return $cells;
    }

    /** @param array<int,string> $onlineCharacters */
    private function onlineStatusHtml(string $characterName, bool $showOnlineStatus, array $onlineCharacters): string
    {
        if (!$showOnlineStatus) {
            return '';
        }

        $icon = in_array($characterName, $onlineCharacters, true) ? __PATH_ONLINE_STATUS__ : __PATH_OFFLINE_STATUS__;
        return '<img src="' . $icon . '" class="online-status-indicator"/>';
    }

    private function gensTypeHtml(int $influence): string
    {
        $duprian = htmlspecialchars(strip_tags((string) lang('rankings_txt_26', true)), ENT_QUOTES, 'UTF-8');
        $vantarion = htmlspecialchars(strip_tags((string) lang('rankings_txt_27', true)), ENT_QUOTES, 'UTF-8');

        if ($influence === 1) {
            return '<img class="rankings-gens-img" src="' . __PATH_THEME_IMG__ . 'gens_1.png" title="' . $duprian . '" alt="' . $duprian . '"/>';
        }

        return '<img class="rankings-gens-img" src="' . __PATH_THEME_IMG__ . 'gens_2.png" title="' . $vantarion . '" alt="' . $vantarion . '"/>';
    }

    /** @param array<int,string> $cells */
    private function rowModel(int $classId, int $position, array $cells): array
    {
        return [
            'rowClass' => $this->rankClass($position),
            'dataClassId' => $classId,
            'cells' => $cells,
        ];
    }

    private function rankClass(int $position): string
    {
        return $position <= 3 ? 'rankings-row rank-' . $position : 'rankings-row';
    }
}
