<?php

declare(strict_types=1);

namespace Darkheim\Application\Rankings;

use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Application\Auth\Common;
use Darkheim\Application\Character\Character;

/**
 * RankingsService — cache-building and menu rendering for all ranking types.
 * Complete PSR-4 port of the legacy `Rankings` classmap class.
 */
class RankingsService
{
    private int $_results;
    private array $_excludedCharacters = [''];
    private array $_excludedGuilds     = [''];
    private array $_rankingsMenu;

    protected $config;
    protected $mu;
    protected $me;

    public function __construct()
    {
        $this->config = cmsConfigs();

        loadModuleConfigs('rankings');
        $this->_results = check_value(mconfig('rankings_results')) ? (int) mconfig('rankings_results') : 25;

        if (check_value(mconfig('rankings_excluded_characters'))) {
            $this->_excludedCharacters = explode(',', mconfig('rankings_excluded_characters'));
        }
        if (check_value(mconfig('rankings_excluded_guilds'))) {
            $this->_excludedGuilds = explode(',', mconfig('rankings_excluded_guilds'));
        }

        $this->_rankingsMenu = [
            [lang('rankings_txt_1',  true), 'level',        mconfig('rankings_enable_level')],
            [lang('rankings_txt_2',  true), 'resets',       mconfig('rankings_enable_resets')],
            [lang('rankings_txt_3',  true), 'killers',      mconfig('rankings_enable_pk')],
            [lang('rankings_txt_4',  true), 'guilds',       mconfig('rankings_enable_guilds')],
            [lang('rankings_txt_5',  true), 'grandresets',  mconfig('rankings_enable_gr')],
            [lang('rankings_txt_6',  true), 'online',       mconfig('rankings_enable_online')],
            [lang('rankings_txt_7',  true), 'votes',        mconfig('rankings_enable_votes')],
            [lang('rankings_txt_8',  true), 'gens',         mconfig('rankings_enable_gens')],
            [lang('rankings_txt_22', true), 'master',       mconfig('rankings_enable_master')],
        ];

        $extraMenuLinks = getRankingMenuLinks();
        if (is_array($extraMenuLinks)) {
            foreach ($extraMenuLinks as $menuLink) {
                $this->_rankingsMenu[] = [$menuLink[0], $menuLink[1], true];
            }
        }
    }

    // ─── Public API ───────────────────────────────────────────────────────────

    public function UpdateRankingCache(string $type): void
    {
        switch ($type) {
            case 'level':        $this->_levelsRanking();       break;
            case 'resets':       $this->_resetsRanking();       break;
            case 'killers':      $this->_killersRanking();      break;
            case 'grandresets':  $this->_grandresetsRanking();  break;
            case 'online':       $this->_onlineRanking();       break;
            case 'votes':        $this->_votesRanking();        break;
            case 'guilds':       $this->_guildsRanking();       break;
            case 'master':       $this->_masterlevelRanking();  break;
            case 'gens':         $this->_gensRanking();         break;
        }
    }

    public function rankingsMenu(): void
    {
        echo '<div class="rankings_menu" id="rankings-anchor">';
        foreach ($this->_rankingsMenu as $item) {
            if (!$item[2]) continue;
            $active = ($_REQUEST['subpage'] ?? '') == $item[1] ? ' active' : '';
            echo '<a href="' . __PATH_MODULES_RANKINGS__ . $item[1] . '/" class="' . $active . ' rankings-nav-link">' . $item[0] . '</a>';
        }
        echo '</div>';
    }

    public function rankingsFilterMenu(): void
    {
        $filterData = $this->_getRankingsFilterData();
        if (!is_array($filterData)) return;

        echo '<div class="text-center">';
        echo '<ul class="rankings-class-filter">';
        echo '<li><a onclick="rankingsFilterRemove()" class="rankings-class-filter-selection">'
            . getPlayerClassAvatar(-1, true, false, 'rankings-class-filter-image')
            . '<br />' . lang('rankings_filter_1') . '</a></li>';
        foreach ($filterData as $row) {
            echo '<li><a onclick="rankingsFilterByClass(' . $row[1] . ')" class="rankings-class-filter-selection rankings-class-filter-grayscale">'
                . getPlayerClassAvatar($row[0], true, false, 'rankings-class-filter-image')
                . '<br />' . $row[2] . '</a></li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    // ─── Private cache builders ───────────────────────────────────────────────

    private function _levelsRanking(): void
    {
        $result = $this->_getLevelRankingData((bool) mconfig('combine_level_masterlevel'));
        if (!is_array($result)) return;
        UpdateCache('rankings_level.cache', BuildCacheData($result));
    }

    private function _resetsRanking(): void
    {
        $result = $this->_getResetRankingData((bool) mconfig('combine_level_masterlevel'));
        if (!is_array($result)) return;
        UpdateCache('rankings_resets.cache', BuildCacheData($result));
    }

    private function _killersRanking(): void
    {
        $result = $this->_getKillersRankingData((bool) mconfig('combine_level_masterlevel'));
        if (!is_array($result)) return;
        UpdateCache('rankings_pk.cache', BuildCacheData($result));
    }

    private function _grandresetsRanking(): void
    {
        $this->mu = Connection::Database('MuOnline');
        $result = $this->mu->query_fetch(
            "SELECT TOP " . $this->_results . " " . _CLMN_CHR_NAME_ . ", " . _CLMN_CHR_GRSTS_ . ", " . _CLMN_CHR_RSTS_ . ", " . _CLMN_CHR_CLASS_ . ", " . _CLMN_CHR_MAP_ . " FROM " . _TBL_CHR_ . " WHERE " . _CLMN_CHR_GRSTS_ . " >= 1 AND " . _CLMN_CHR_NAME_ . " NOT IN(" . $this->_rankingsExcludeChars() . ") ORDER BY " . _CLMN_CHR_GRSTS_ . " DESC, " . _CLMN_CHR_RSTS_ . " DESC"
        );
        if (!is_array($result)) return;
        UpdateCache('rankings_gr.cache', BuildCacheData($result));
    }

    private function _guildsRanking(): void
    {
        $this->mu = Connection::Database('MuOnline');
        $result = match (mconfig('guild_score_formula')) {
            2 => $this->mu->query_fetch(
                "SELECT "._TBL_GUILDMEMB_."."._CLMN_GUILDMEMB_NAME_.", (SELECT "
                ._CLMN_GUILD_MASTER_." FROM "._TBL_GUILD_." WHERE "
                ._CLMN_GUILD_NAME_." = "._TBL_GUILDMEMB_."."
                ._CLMN_GUILDMEMB_NAME_.") as "._CLMN_GUILD_MASTER_.", SUM("
                ._TBL_CHR_."."._CLMN_CHR_STAT_STR_."+"._TBL_CHR_."."
                ._CLMN_CHR_STAT_AGI_."+"._TBL_CHR_."."._CLMN_CHR_STAT_VIT_."+"
                ._TBL_CHR_."."._CLMN_CHR_STAT_ENE_."+"._TBL_CHR_."."
                ._CLMN_CHR_STAT_CMD_.") as "._CLMN_GUILD_SCORE_
                .", (SELECT CONVERT(varchar(max), "._CLMN_GUILD_LOGO_
                .", 2) FROM "._TBL_GUILD_." WHERE "._CLMN_GUILD_NAME_." = "
                ._TBL_GUILDMEMB_."."._CLMN_GUILDMEMB_NAME_.") as "
                ._CLMN_GUILD_LOGO_." FROM "._TBL_GUILDMEMB_." INNER JOIN "
                ._TBL_CHR_." ON "._TBL_CHR_."."._CLMN_CHR_NAME_." = "
                ._TBL_GUILDMEMB_."."._CLMN_GUILDMEMB_CHAR_." INNER JOIN "
                ._TBL_GUILD_." ON "._TBL_GUILD_."."._CLMN_GUILD_NAME_." = "
                ._TBL_GUILDMEMB_."."._CLMN_GUILDMEMB_NAME_." WHERE "
                ._TBL_GUILDMEMB_."."._CLMN_GUILDMEMB_NAME_." NOT IN("
                .$this->_rankingsExcludeGuilds().") GROUP BY "._TBL_GUILDMEMB_
                ."."._CLMN_GUILDMEMB_NAME_." ORDER BY "._CLMN_GUILD_SCORE_
                ." DESC"
            ),
            3 => $this->mu->query_fetch(
                "SELECT "._TBL_GUILDMEMB_."."._CLMN_GUILDMEMB_NAME_.", (SELECT "
                ._CLMN_GUILD_MASTER_." FROM "._TBL_GUILD_." WHERE "
                ._CLMN_GUILD_NAME_." = "._TBL_GUILDMEMB_."."
                ._CLMN_GUILDMEMB_NAME_.") as "._CLMN_GUILD_MASTER_.", SUM("
                ._TBL_CHR_."."._CLMN_CHR_STAT_STR_."+"._TBL_CHR_."."
                ._CLMN_CHR_STAT_AGI_."+"._TBL_CHR_."."._CLMN_CHR_STAT_VIT_."+"
                ._TBL_CHR_."."._CLMN_CHR_STAT_ENE_.") as "._CLMN_GUILD_SCORE_
                .", (SELECT CONVERT(varchar(max), "._CLMN_GUILD_LOGO_
                .", 2) FROM "._TBL_GUILD_." WHERE "._CLMN_GUILD_NAME_." = "
                ._TBL_GUILDMEMB_."."._CLMN_GUILDMEMB_NAME_.") as "
                ._CLMN_GUILD_LOGO_." FROM "._TBL_GUILDMEMB_." INNER JOIN "
                ._TBL_CHR_." ON "._TBL_CHR_."."._CLMN_CHR_NAME_." = "
                ._TBL_GUILDMEMB_."."._CLMN_GUILDMEMB_CHAR_." INNER JOIN "
                ._TBL_GUILD_." ON "._TBL_GUILD_."."._CLMN_GUILD_NAME_." = "
                ._TBL_GUILDMEMB_."."._CLMN_GUILDMEMB_NAME_." WHERE "
                ._TBL_GUILDMEMB_."."._CLMN_GUILDMEMB_NAME_." NOT IN("
                .$this->_rankingsExcludeGuilds().") GROUP BY "._TBL_GUILDMEMB_
                ."."._CLMN_GUILDMEMB_NAME_." ORDER BY "._CLMN_GUILD_SCORE_
                ." DESC"
            ),
            default => $this->mu->query_fetch(
                "SELECT TOP ".$this->_results." "._CLMN_GUILD_NAME_.","
                ._CLMN_GUILD_MASTER_.","._CLMN_GUILD_SCORE_
                .",CONVERT(varchar(max), "._CLMN_GUILD_LOGO_.", 2) as "
                ._CLMN_GUILD_LOGO_." FROM "._TBL_GUILD_." WHERE G_Name NOT IN("
                .$this->_rankingsExcludeGuilds().") ORDER BY "
                ._CLMN_GUILD_SCORE_." DESC"
            ),
        };
        if (!is_array($result)) return;
        UpdateCache('rankings_guilds.cache', BuildCacheData($result));
    }

    private function _masterlevelRanking(): void
    {
        $this->mu = Connection::Database('MuOnline');
        /** @phpstan-ignore equal.alwaysFalse */
        if (_TBL_CHR_ == _TBL_MASTERLVL_) {
            $result = $this->mu->query_fetch("SELECT TOP " . $this->_results . " " . _CLMN_CHR_NAME_ . ", " . _CLMN_ML_LVL_ . ", " . _CLMN_CHR_CLASS_ . ", " . _CLMN_CHR_LVL_ . ", " . _CLMN_CHR_MAP_ . " FROM " . _TBL_CHR_ . " WHERE " . _CLMN_CHR_NAME_ . " NOT IN(" . $this->_rankingsExcludeChars() . ") AND " . _CLMN_ML_LVL_ . " > 0 ORDER BY " . _CLMN_ML_LVL_ . " DESC");
        } else {
            $result = $this->mu->query_fetch("SELECT TOP " . $this->_results . " t1." . _CLMN_ML_NAME_ . ", t1." . _CLMN_ML_LVL_ . ", t2." . _CLMN_CHR_CLASS_ . ", t2." . _CLMN_CHR_LVL_ . ", t2." . _CLMN_CHR_MAP_ . " FROM " . _TBL_MASTERLVL_ . " AS t1 INNER JOIN " . _TBL_CHR_ . " AS t2 ON t1." . _CLMN_ML_NAME_ . " = t2." . _CLMN_CHR_NAME_ . " WHERE t1." . _CLMN_ML_NAME_ . " NOT IN(" . $this->_rankingsExcludeChars() . ") AND t1." . _CLMN_ML_LVL_ . " > 0 ORDER BY t1." . _CLMN_ML_LVL_ . " DESC, t2." . _CLMN_CHR_LVL_ . " DESC");
        }
        if (!is_array($result)) return;
        UpdateCache('rankings_master.cache', BuildCacheData($result));
    }

    private function _gensRanking(): void
    {
        $duprianData = $this->_generateGensRankingData(1) ?? [];
        $vanertData  = $this->_generateGensRankingData(2) ?? [];

        $rankingData = array_merge($duprianData, $vanertData);
        usort($rankingData, static fn($a, $b) => $b['contribution'] - $a['contribution']);
        $result = array_slice($rankingData, 0, $this->_results);
        if (empty($result)) return;
        UpdateCache('rankings_gens.cache', BuildCacheData($result));
    }

    private function _votesRanking(): void
    {
        $this->me         = Connection::Database('MuOnline');
        $voteMonthTs      = strtotime(date('m/01/Y 00:00'));
        $accounts         = $this->me->query_fetch(
            "SELECT TOP " . $this->_results . " user_id, COUNT(*) as count FROM " . \Vote_Logs . " WHERE timestamp >= ? GROUP BY user_id ORDER BY count DESC",
            [$voteMonthTs]
        );
        if (!is_array($accounts)) return;

        $result    = [];
        $commonObj = new Common();
        $character = new Character();

        foreach ($accounts as $data) {
            $accountInfo = $commonObj->accountInformation($data['user_id']);
            if (!is_array($accountInfo)) continue;
            $characterName = $character->AccountCharacterIDC($accountInfo[_CLMN_USERNM_]);
            if (!check_value($characterName)) continue;
            $characterData = $character->CharacterData($characterName);
            if (!is_array($characterData)) continue;
            if (in_array($characterName, $this->_excludedCharacters, true)) continue;
            $result[] = [$characterName, $data['count'], $characterData[_CLMN_CHR_CLASS_], $characterData[_CLMN_CHR_MAP_]];
        }
        if (empty($result)) return;
        UpdateCache('rankings_votes.cache', BuildCacheData($result));
    }

    private function _onlineRanking(): void
    {
        $result = $this->_getOnlineRankingDataMembStatHours();
        if (!is_array($result)) return;
        UpdateCache('rankings_online.cache', BuildCacheData($result));
    }

    // ─── Private data helpers ─────────────────────────────────────────────────

    private function _rankingsExcludeChars(): string
    {
        return implode(',', array_map(static fn($n) => "'" . $n . "'", $this->_excludedCharacters));
    }

    private function _rankingsExcludeGuilds(): string
    {
        return implode(',', array_map(static fn($n) => "'" . $n . "'", $this->_excludedGuilds));
    }

    private function _generateGensRankingData(int $influence = 1): ?array
    {
        $this->mu = Connection::Database('MuOnline');
        $result = $this->mu->query_fetch(
            "SELECT t1." . _CLMN_GENS_NAME_ . ", t1." . _CLMN_GENS_TYPE_ . ", t1." . _CLMN_GENS_POINT_ . ", t2." . _CLMN_CHR_LVL_ . ", t2." . _CLMN_CHR_CLASS_ . ", t2." . _CLMN_CHR_MAP_ . " FROM " . _TBL_GENS_ . " as t1 INNER JOIN " . _TBL_CHR_ . " as t2 ON t1." . _CLMN_GENS_NAME_ . " = t2." . _CLMN_CHR_NAME_ . " WHERE t1." . _CLMN_GENS_TYPE_ . " = ? AND t1." . _CLMN_GENS_NAME_ . " NOT IN(" . $this->_rankingsExcludeChars() . ") ORDER BY t1." . _CLMN_GENS_POINT_ . " DESC",
            [$influence]
        );
        if (!is_array($result)) return null;

        $rankingData = [];
        foreach ($result as $rankPos => $row) {
            $gensRank = getGensRank($row[_CLMN_GENS_POINT_]);
            if ($row[_CLMN_GENS_POINT_] >= 10000) $gensRank = getGensLeadershipRank($rankPos);
            $rankingData[] = [
                'name'         => $row[_CLMN_GENS_NAME_],
                'influence'    => $row[_CLMN_GENS_TYPE_],
                'contribution' => $row[_CLMN_GENS_POINT_],
                'rank'         => $gensRank,
                'level'        => $row[_CLMN_CHR_LVL_],
                'class'        => $row[_CLMN_CHR_CLASS_],
                'map'          => $row[_CLMN_CHR_MAP_],
            ];
        }
        return count($rankingData) > 0 ? $rankingData : null;
    }

    private function _getLevelRankingData(bool $combineMasterLevel = false): ?array
    {
        $this->mu = Connection::Database('MuOnline');

        if (!$combineMasterLevel) {
            $result = $this->mu->query_fetch("SELECT TOP " . $this->_results . " " . _CLMN_CHR_NAME_ . "," . _CLMN_CHR_CLASS_ . "," . _CLMN_CHR_LVL_ . "," . _CLMN_CHR_MAP_ . " FROM " . _TBL_CHR_ . " WHERE " . _CLMN_CHR_NAME_ . " NOT IN(" . $this->_rankingsExcludeChars() . ") ORDER BY " . _CLMN_CHR_LVL_ . " DESC");
            return is_array($result) ? $result : null;
        }

        /** @phpstan-ignore equal.alwaysFalse */
        if (_TBL_CHR_ == _TBL_MASTERLVL_) {
            $result = $this->mu->query_fetch("SELECT TOP " . $this->_results . " " . _CLMN_CHR_NAME_ . "," . _CLMN_CHR_CLASS_ . ",(" . _CLMN_CHR_LVL_ . "+" . _CLMN_ML_LVL_ . ") as " . _CLMN_CHR_LVL_ . "," . _CLMN_CHR_MAP_ . " FROM " . _TBL_CHR_ . " WHERE " . _CLMN_CHR_NAME_ . " NOT IN(" . $this->_rankingsExcludeChars() . ") ORDER BY " . _CLMN_CHR_LVL_ . " DESC");
            return is_array($result) ? $result : null;
        }
        $character  = new Character();
        $characters = $this->mu->query_fetch("SELECT " . _CLMN_CHR_NAME_ . "," . _CLMN_CHR_CLASS_ . "," . _CLMN_CHR_LVL_ . "," . _CLMN_CHR_MAP_ . " FROM " . _TBL_CHR_ . " WHERE " . _CLMN_CHR_NAME_ . " NOT IN(" . $this->_rankingsExcludeChars() . ") ORDER BY " . _CLMN_CHR_LVL_ . " DESC");
        if (!is_array($characters)) return null;

        $rankingData = [];
        foreach ($characters as $row) {
            $masterLevelInfo = $character->getMasterLevelInfo($row[_CLMN_CHR_NAME_]);
            $masterLevel     = (is_array($masterLevelInfo) && isset($masterLevelInfo[_CLMN_ML_LVL_])) ? $masterLevelInfo[_CLMN_ML_LVL_] : 0;
            $rankingData[]   = [_CLMN_CHR_NAME_ => $row[_CLMN_CHR_NAME_], _CLMN_CHR_CLASS_ => $row[_CLMN_CHR_CLASS_], _CLMN_CHR_LVL_ => $row[_CLMN_CHR_LVL_] + $masterLevel, _CLMN_CHR_MAP_ => $row[_CLMN_CHR_MAP_]];
        }
        usort($rankingData, static fn($a, $b) => $b[_CLMN_CHR_LVL_] - $a[_CLMN_CHR_LVL_]);
        $result = array_slice($rankingData, 0, $this->_results);
        return count($result) > 0 ? $result : null;
    }

    private function _getResetRankingData(bool $combineMasterLevel = false): ?array
    {
        $this->mu = Connection::Database('MuOnline');

        if (!$combineMasterLevel) {
            $result = $this->mu->query_fetch("SELECT TOP " . $this->_results . " " . _CLMN_CHR_NAME_ . "," . _CLMN_CHR_CLASS_ . "," . _CLMN_CHR_RSTS_ . "," . _CLMN_CHR_LVL_ . "," . _CLMN_CHR_MAP_ . " FROM " . _TBL_CHR_ . " WHERE " . _CLMN_CHR_NAME_ . " NOT IN(" . $this->_rankingsExcludeChars() . ") AND " . _CLMN_CHR_RSTS_ . " > 0 ORDER BY " . _CLMN_CHR_RSTS_ . " DESC, " . _CLMN_CHR_LVL_ . " DESC");
            return is_array($result) ? $result : null;
        }

        /** @phpstan-ignore equal.alwaysFalse */
        if (_TBL_CHR_ == _TBL_MASTERLVL_) {
            $result = $this->mu->query_fetch("SELECT TOP " . $this->_results . " " . _CLMN_CHR_NAME_ . "," . _CLMN_CHR_CLASS_ . "," . _CLMN_CHR_RSTS_ . ",(" . _CLMN_CHR_LVL_ . "+" . _CLMN_ML_LVL_ . ") as " . _CLMN_CHR_LVL_ . "," . _CLMN_CHR_MAP_ . " FROM " . _TBL_CHR_ . " WHERE " . _CLMN_CHR_NAME_ . " NOT IN(" . $this->_rankingsExcludeChars() . ") AND " . _CLMN_CHR_RSTS_ . " > 0 ORDER BY " . _CLMN_CHR_RSTS_ . " DESC, " . _CLMN_CHR_LVL_ . " DESC");
            return is_array($result) ? $result : null;
        }

        $result = $this->mu->query_fetch("SELECT TOP " . $this->_results . " " . _TBL_CHR_ . "." . _CLMN_CHR_NAME_ . ", " . _TBL_CHR_ . "." . _CLMN_CHR_CLASS_ . ", " . _TBL_CHR_ . "." . _CLMN_CHR_RSTS_ . ", (" . _TBL_CHR_ . "." . _CLMN_CHR_LVL_ . " + " . _TBL_MASTERLVL_ . "." . _CLMN_ML_LVL_ . ") as " . _CLMN_CHR_LVL_ . ", " . _TBL_CHR_ . "." . _CLMN_CHR_MAP_ . " FROM " . _TBL_CHR_ . " INNER JOIN " . _TBL_MASTERLVL_ . " ON " . _TBL_CHR_ . "." . _CLMN_CHR_NAME_ . " = " . _TBL_MASTERLVL_ . "." . _CLMN_ML_NAME_ . " WHERE " . _TBL_CHR_ . "." . _CLMN_CHR_NAME_ . " NOT IN (" . $this->_rankingsExcludeChars() . ") AND " . _TBL_CHR_ . "." . _CLMN_CHR_RSTS_ . " > 0 ORDER BY " . _TBL_CHR_ . "." . _CLMN_CHR_RSTS_ . " DESC, " . _CLMN_CHR_LVL_ . " DESC");
        return is_array($result) ? $result : null;
    }

    private function _getKillersRankingData(bool $combineMasterLevel = false): ?array
    {
        $this->mu = Connection::Database('MuOnline');

        if (!$combineMasterLevel) {
            $result = $this->mu->query_fetch("SELECT TOP " . $this->_results . " " . _CLMN_CHR_NAME_ . "," . _CLMN_CHR_CLASS_ . "," . _CLMN_CHR_PK_KILLS_ . "," . _CLMN_CHR_LVL_ . "," . _CLMN_CHR_MAP_ . "," . _CLMN_CHR_PK_LEVEL_ . " FROM " . _TBL_CHR_ . " WHERE " . _CLMN_CHR_NAME_ . " NOT IN(" . $this->_rankingsExcludeChars() . ") AND " . _CLMN_CHR_PK_KILLS_ . " > 0 ORDER BY " . _CLMN_CHR_PK_KILLS_ . " DESC");
            return is_array($result) ? $result : null;
        }

        /** @phpstan-ignore equal.alwaysFalse */
        if (_TBL_CHR_ == _TBL_MASTERLVL_) {
            $result = $this->mu->query_fetch("SELECT TOP " . $this->_results . " " . _CLMN_CHR_NAME_ . "," . _CLMN_CHR_CLASS_ . "," . _CLMN_CHR_PK_KILLS_ . ",(" . _CLMN_CHR_LVL_ . "+" . _CLMN_ML_LVL_ . ") as " . _CLMN_CHR_LVL_ . "," . _CLMN_CHR_MAP_ . "," . _CLMN_CHR_PK_LEVEL_ . " FROM " . _TBL_CHR_ . " WHERE " . _CLMN_CHR_NAME_ . " NOT IN(" . $this->_rankingsExcludeChars() . ") AND " . _CLMN_CHR_PK_KILLS_ . " > 0 ORDER BY " . _CLMN_CHR_PK_KILLS_ . " DESC");
            return is_array($result) ? $result : null;
        }

        $character = new Character();
        $result = $this->mu->query_fetch("SELECT TOP " . $this->_results . " " . _CLMN_CHR_NAME_ . "," . _CLMN_CHR_CLASS_ . "," . _CLMN_CHR_PK_KILLS_ . "," . _CLMN_CHR_LVL_ . "," . _CLMN_CHR_MAP_ . "," . _CLMN_CHR_PK_LEVEL_ . " FROM " . _TBL_CHR_ . " WHERE " . _CLMN_CHR_NAME_ . " NOT IN(" . $this->_rankingsExcludeChars() . ") AND " . _CLMN_CHR_PK_KILLS_ . " > 0 ORDER BY " . _CLMN_CHR_PK_KILLS_ . " DESC");
        if (!is_array($result)) return null;
        foreach ($result as $key => $row) {
            $masterLevelInfo = $character->getMasterLevelInfo($row[_CLMN_CHR_NAME_]);
            if (!is_array($masterLevelInfo)) continue;
            $result[$key][_CLMN_CHR_LVL_] = $row[_CLMN_CHR_LVL_] + $masterLevelInfo[_CLMN_ML_LVL_];
        }
        return $result;
    }

    private function _getOnlineRankingDataMembStatHours(): ?array
    {
        $this->mu = Connection::Database('MuOnline');
        $accounts = $this->mu->query_fetch(
            "SELECT TOP " . $this->_results . " " . _CLMN_MS_MEMBID_ . ", " . _CLMN_MS_ONLINEHRS_ . " FROM " . _TBL_MS_ . " WHERE " . _CLMN_MS_ONLINEHRS_ . " > 0 ORDER BY " . _CLMN_MS_ONLINEHRS_ . " DESC"
        );
        if (!is_array($accounts)) return null;

        $character = new Character();
        $result    = [];
        foreach ($accounts as $row) {
            $playerIDC = $character->AccountCharacterIDC($row[_CLMN_MS_MEMBID_]);
            if (!check_value($playerIDC)) continue;
            $playerData = $character->CharacterData($playerIDC);
            if (!is_array($playerData)) continue;
            $result[] = [$playerIDC, $row[_CLMN_MS_ONLINEHRS_] * 3600, $playerData[_CLMN_CHR_CLASS_], $playerData[_CLMN_CHR_MAP_]];
        }
        return count($result) > 0 ? $result : null;
    }

    private function _getRankingsFilterData(): ?array
    {
        $classesData    = custom('character_class');
        $rankingsFilter = custom('rankings_classgroup_filter');
        if (!is_array($rankingsFilter)) return null;

        $filterList = [];
        foreach ($rankingsFilter as $class => $phrase) {
            if (!array_key_exists($class, $classesData)) continue;
            $filterName     = lang($phrase) == 'ERROR' ? $phrase : lang($phrase);
            $classGroupList = [];
            foreach ($classesData as $key => $row) {
                if ($row['class_group'] == $class) $classGroupList[] = $key;
            }
            $filterList[] = [$class, implode(',', $classGroupList), $filterName];
        }
        return count($filterList) > 0 ? $filterList : null;
    }
}

