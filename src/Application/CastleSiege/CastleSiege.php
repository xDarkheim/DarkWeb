<?php

declare(strict_types=1);

namespace Darkheim\Application\CastleSiege;

use Darkheim\Infrastructure\Database\Connection;

/**
 * Castle Siege — schedule generation, stage management, live/cached siege data.
 */
class CastleSiege
{
    protected string $_database           = 'MuOnline';
    protected string $_configFileName     = 'castlesiege';
    protected string $_cacheFileName      = 'castle_siege.cache';
    protected string $_dateFormat         = 'Y/m/d H:i';
    protected string $_friendlyDateFormat = 'l, F jS g:i A';
    protected string $_defaultGuildLogo   = '1111111111111111111111111114411111144111111111111111111111111111' {
        get {
            return $this->_defaultGuildLogo;
        }
    }

    protected bool $_active                  = true;
    protected bool $_hideIdle                = false;
    protected bool $_liveData                = false;
    protected bool $_showCastleOwner         = true;
    protected bool $_showCastleOwnerAlliance = true;
    protected bool $_showBattleCountdown     = true;
    protected bool $_showCastleInformation   = true;
    protected bool $_showCurrentStage        = true;
    protected bool $_showNextStage           = true;
    protected bool $_showBattleDuration      = true;
    protected bool $_showRegisteredGuilds    = true;
    protected bool $_showSchedule            = true;
    protected bool $_showWidget              = true;

    protected $_stages;
    protected array $_schedule = [] {
        get {
            return $this->_schedule;
        }
    }
    protected ?int $_currentStage = null;
    protected ?int $_nextStage    = null;
    protected ?int $_warfareStage = null;

    protected $_cacheSiegeData;
    protected $db;

    public function __construct()
    {
        $cfg = loadConfig($this->_configFileName);
        if (!$cfg) throw new \Exception('Could not load castle siege configuration file.');

        $this->_active                  = (bool) $cfg['active'];
        $this->_hideIdle                = (bool) $cfg['hide_idle'];
        $this->_liveData                = (bool) $cfg['live_data'];
        $this->_showCastleOwner         = (bool) $cfg['show_castle_owner'];
        $this->_showCastleOwnerAlliance = (bool) $cfg['show_castle_owner_alliance'];
        $this->_showBattleCountdown     = (bool) $cfg['show_battle_countdown'];
        $this->_showCastleInformation   = (bool) $cfg['show_castle_information'];
        $this->_showCurrentStage        = (bool) $cfg['show_current_stage'];
        $this->_showNextStage           = (bool) $cfg['show_next_stage'];
        $this->_showBattleDuration      = (bool) $cfg['show_battle_duration'];
        $this->_showRegisteredGuilds    = (bool) $cfg['show_registered_guilds'];
        $this->_showSchedule            = (bool) $cfg['show_schedule'];
        $this->_showWidget              = (bool) $cfg['show_widget'];
        $this->_friendlyDateFormat      = $cfg['schedule_date_format'];
        $this->_stages                  = $cfg['stages'];

        $this->_generateSchedule();
        $this->_determineCurrentStage();
        $this->_determineNextStage();
        $this->_determineWarfareStage();
        $this->_loadCacheSiegeData();
    }

    public function siegeData(): ?array
    {
        if ($this->_liveData) {
            $this->_initDatabase();
        } elseif (!is_array($this->_cacheSiegeData)) return null;

        return [
            'current_stage'           => $this->getCurrentStage(),
            'next_stage'              => $this->getNextStage(),
            'warfare_stage'           => $this->getWarfareStage(),
            'castle_data'             => $this->getCastleData(),
            'castle_owner_alliance'   => $this->getCastleOwnerAlliance(),
            'registered_guilds'       => $this->getRegisteredGuildsAndAlliances(),
            'schedule'                => $this->_schedule,
            'next_stage_timeleft'     => $this->_getSecondsForNextStage(),
            'next_stage_countdown'    => $this->getNextStageCountdown(),
            'warfare_stage_timeleft'  => $this->_getSecondsForWarfareStage(),
            'warfare_stage_countdown' => $this->getWarfareStageCountdown(),
            'warfare_duration'        => $this->getWarfareDuration(),
        ];
    }

    public function getCurrentStage(): ?array
    {
        return $this->_currentStage !== null ? ($this->_schedule[$this->_currentStage] ?? null) : null;
    }

    public function getNextStage(): ?array
    {
        if ($this->_nextStage === 0) return $this->_getNextScheduleStartingStage();
        return $this->_nextStage !== null ? ($this->_schedule[$this->_nextStage] ?? null) : null;
    }

    public function getWarfareStage(): ?array
    {
        return $this->_warfareStage !== null ? ($this->_schedule[$this->_warfareStage] ?? null) : null;
    }

    public function getNextStageCountdown(): string    { return $this->_formatCountdownTime($this->_getSecondsForNextStage()); }
    public function getWarfareStageCountdown(): string { return $this->_formatCountdownTime($this->_getSecondsForWarfareStage()); }

    public function getCastleData()
    {
        if ($this->_liveData) return $this->_returnCastleData();
        return $this->_cacheSiegeData['castle_data'] ?? null;
    }

    public function getCastleOwnerAlliance()
    {
        if ($this->_liveData) return $this->_returnCastleOwnerAlliance();
        return $this->_cacheSiegeData['castle_owner_alliance'] ?? null;
    }

    public function getRegisteredGuildsAndAlliances()
    {
        if ($this->_liveData) return $this->_returnRegisteredGuildsAndAlliances();
        return $this->_cacheSiegeData['registered_guilds'] ?? null;
    }

    public function friendlyDateFormat(int $ts): string { return date($this->_friendlyDateFormat, $ts); }
    public function showCastleOwner(): bool           { return $this->_showCastleOwner; }
    public function showCastleOwnerAlliance(): bool   { return $this->_showCastleOwnerAlliance; }
    public function showBattleCountdown(): bool       { return $this->_showBattleCountdown; }
    public function showCastleInformation(): bool     { return $this->_showCastleInformation; }
    public function showCurrentStage(): bool          { return $this->_showCurrentStage; }
    public function showNextStage(): bool             { return $this->_showNextStage; }
    public function showRegisteredGuilds(): bool      { return $this->_showRegisteredGuilds; }
    public function showBattleDuration(): bool        { return $this->_showBattleDuration; }
    public function showSchedule(): bool              { return $this->_showSchedule; }

    public function moduleEnabled(): bool             { return $this->_active; }

    public function getWarfareDuration(): string
    {
        $warfareStage = $this->getWarfareStage();
        $warfareDurationSeconds = $warfareStage['end_timestamp'] - $warfareStage['start_timestamp'];
        $warfareDuration = sec_to_hms($warfareDurationSeconds);
        return langf('castlesiege_battle_duration', [$warfareDuration[0], $warfareDuration[1]]);
    }

    public function updateSiegeCache(): void
    {
        $this->_initDatabase();
        $this->_liveData = true;
        $this->_cacheSiegeData();
    }

    protected function _generateSchedule(): void
    {
        if (!is_array($this->_stages)) {
            throw new \Exception('The castle siege schedule could not be generated, missing stages data.');
        }

        $schedule            = $this->_stages;
        $currentDay          = date('l');
        $csScheduleStartDay  = $schedule[0]['start_day'];
        $csScheduleStartTime = $schedule[0]['start_time'];

        if (strtolower($currentDay) != strtolower($csScheduleStartDay)) {
            $scheduleStartingDay = strtotime('last ' . $csScheduleStartDay . ' ' . $csScheduleStartTime);
        } else {
            $scheduleStartingDay = strtotime('today ' . $csScheduleStartTime);
        }

        foreach ($schedule as $key => $stage) {
            if ($this->_hideIdle && $stage['is_idle']) {
                unset($schedule[$key]);
                continue;
            }

            if ($key == 0) {
                $start_timestamp = (strtolower($currentDay) == strtolower($stage['start_day']))
                    ? strtotime('today ' . $stage['start_time'])
                    : strtotime('last ' . $stage['start_day'] . ' ' . $stage['start_time']);
                $end_timestamp = (strtolower($currentDay) == strtolower($stage['end_day']))
                    ? strtotime('today ' . $stage['end_time'])
                    : strtotime('next ' . $stage['end_day'] . ' ' . $stage['end_time'], $scheduleStartingDay);
            } else {
                $start_timestamp = strtotime('next ' . $stage['start_day'] . ' ' . $stage['start_time'], $scheduleStartingDay);
                $end_timestamp   = strtotime('next ' . $stage['end_day'] . ' ' . $stage['end_time'], $scheduleStartingDay);
            }

            $schedule[$key]['title']           = lang($stage['title']);
            $schedule[$key]['start_timestamp'] = $start_timestamp;
            $schedule[$key]['end_timestamp']   = $end_timestamp;
            $schedule[$key]['start_date']      = date($this->_dateFormat, $start_timestamp);
            $schedule[$key]['end_date']        = date($this->_dateFormat, $end_timestamp);
        }

        $this->_schedule = array_values($schedule);
    }

    protected function _determineCurrentStage(): void
    {
        foreach ($this->_schedule as $key => $row) {
            if (time() < $row['start_timestamp']) continue;
            if (time() > $row['end_timestamp'])   continue;
            $this->_currentStage = $key;
            return;
        }
    }

    protected function _determineNextStage(): void
    {
        $next = ($this->_currentStage ?? -1) + 1;
        if (array_key_exists($next, $this->_schedule)) {
            $this->_nextStage = $next;
            return;
        }
        $this->_nextStage = 0;
    }

    protected function _determineWarfareStage(): void
    {
        foreach ($this->_schedule as $key => $row) {
            if ($row['is_battle']) {
                $this->_warfareStage = $key;
                return;
            }
        }
    }

    protected function _getNextScheduleStartingStage(): array
    {
        $stage          = $this->_schedule[0];
        $startTimestamp = strtotime('next ' . $stage['start_day'] . ' ' . $stage['start_time'], $stage['start_timestamp']);
        $endTimestamp   = strtotime('next ' . $stage['end_day'] . ' ' . $stage['end_time'], $stage['end_timestamp']);
        $stage['start_timestamp'] = $startTimestamp;
        $stage['end_timestamp']   = $endTimestamp;
        $stage['start_date']      = date($this->_dateFormat, $startTimestamp);
        $stage['end_date']        = date($this->_dateFormat, $endTimestamp);
        return $stage;
    }

    protected function _getSecondsForNextStage(): int
    {
        $nextStage = $this->getNextStage();
        return ($nextStage['start_timestamp'] - time());
    }

    protected function _getSecondsForWarfareStage(): int
    {
        $warfareStage = $this->getWarfareStage();
        return ($warfareStage['start_timestamp'] - time());
    }

    protected function _formatCountdownTime(int $seconds): string
    {
        $timeleft = sec_to_dhms($seconds);
        if ($timeleft[0] > 0) return langf('castlesiege_time_1', [$timeleft[0], $timeleft[1]]);
        if ($timeleft[1] > 0) return langf('castlesiege_time_2', [$timeleft[1], $timeleft[2]]);
        if ($timeleft[2] > 0) return langf('castlesiege_time_3', [$timeleft[2]]);
        return lang('castlesiege_time_4');
    }

    protected function _returnCastleData(): ?array
    {
        $result = $this->db->query_fetch_single(
            "SELECT * FROM " . _TBL_MUCASTLE_DATA_
        );
        return is_array($result) ? $result : null;
    }

    protected function _returnCastleOwnerAlliance(): ?array
    {
        $castleData      = $this->getCastleData();
        $castleOwnerData = $this->_getGuildData($castleData[_CLMN_MCD_GUILD_OWNER_]);
        if (!is_array($castleOwnerData)) return null;

        $castleOwnerData['member_count'] = $this->_getGuildMemberCount($castleData[_CLMN_MCD_GUILD_OWNER_]);
        $result[] = $castleOwnerData;

        $alliedGuilds = $this->_getAlliedGuilds($castleData[_CLMN_MCD_GUILD_OWNER_]);
        if (is_array($alliedGuilds)) {
            foreach ($alliedGuilds as $alliedGuild) {
                $result[] = $alliedGuild;
            }
        }
        return $result;
    }

    protected function _getGuildMemberCount(string $guild): int
    {
        $guildMembers = $this->db->query_fetch_single(
            "SELECT COUNT(*) AS result FROM " . _TBL_GUILDMEMB_ . " WHERE " . _CLMN_GUILDMEMB_NAME_ . " = ?",
            [$guild]
        );
        return is_array($guildMembers) ? (int) $guildMembers['result'] : 1;
    }

    protected function _getAlliedGuilds(string $guild): ?array
    {
        $alliedGuilds = $this->db->query_fetch(
            "SELECT * FROM " . _TBL_MUCASTLE_SGL_ . " WHERE " . _CLMN_MCSGL_GID_ . " = (SELECT " . _CLMN_MCSGL_GID_ . " FROM " . _TBL_MUCASTLE_SGL_ . " WHERE " . _CLMN_MCSGL_GNAME_ . " = :guild) AND " . _CLMN_MCSGL_GNAME_ . " != :guild",
            ['guild' => $guild]
        );
        if (!is_array($alliedGuilds)) return null;

        $result = [];
        foreach ($alliedGuilds as $alliedGuild) {
            $alliedGuildData = $this->_getGuildData($alliedGuild[_CLMN_MCSGL_GNAME_]);
            if (!is_array($alliedGuildData)) continue;
            $alliedGuildData['member_count'] = $this->_getGuildMemberCount($alliedGuild[_CLMN_MCSGL_GNAME_]);
            $result[] = $alliedGuildData;
        }
        return count($result) > 0 ? $result : null;
    }

    protected function _getGuildData(string $guild): ?array
    {
        $result = $this->db->query_fetch_single(
            "SELECT *, CONVERT(varchar(max), " . _CLMN_GUILD_LOGO_ . ", 2) as " . _CLMN_GUILD_LOGO_ . " FROM " . _TBL_GUILD_ . " WHERE " . _CLMN_GUILD_NAME_ . " = ?",
            [$guild]
        );
        return is_array($result) ? $result : null;
    }

    protected function _returnRegisteredGuildsAndAlliances(): ?array
    {
        $registeredGuilds = $this->db->query_fetch(
            "SELECT * FROM " . _TBL_MUCASTLE_RS_ . " ORDER BY " . _CLMN_MCRS_SEQNUM_
        );
        if (!is_array($registeredGuilds)) return null;

        $result = [];
        foreach ($registeredGuilds as $registeredGuild) {
            $guildData = $this->_getGuildData($registeredGuild[_CLMN_MCRS_GUILD_]);
            if (is_array($guildData)) {
                $guildData['member_count'] = $this->_getGuildMemberCount($registeredGuild[_CLMN_MCRS_GUILD_]);
                $result[] = $guildData;
            }
            $alliedGuilds = $this->_getAlliedGuilds($registeredGuild[_CLMN_MCRS_GUILD_]);
            if (is_array($alliedGuilds)) {
                foreach ($alliedGuilds as $alliedGuild) {
                    $result[] = $alliedGuild;
                }
            }
        }
        return count($result) > 0 ? $result : null;
    }

    protected function _cacheSiegeData(): bool
    {
        $data = [
            'castle_data'           => $this->getCastleData(),
            'castle_owner_alliance' => $this->getCastleOwnerAlliance(),
            'registered_guilds'     => $this->getRegisteredGuildsAndAlliances(),
        ];
        $encoded = encodeCache($data, true);
        return (bool) updateCacheFile($this->_cacheFileName, $encoded);
    }

    protected function _loadCacheSiegeData(): void
    {
        $data = loadCache($this->_cacheFileName);
        if (is_array($data)) {
            $this->_cacheSiegeData = $data;
        }
    }

    protected function _initDatabase(): void
    {
        if (!empty($this->db)) return;
        $this->db = Connection::Database($this->_database);
    }
}

