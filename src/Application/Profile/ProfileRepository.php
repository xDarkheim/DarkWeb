<?php

declare(strict_types=1);

namespace Darkheim\Application\Profile;

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Character\Character;
use Darkheim\Application\Shared\Language\Translator;
use Darkheim\Application\Shared\Support\Encoder;
use Darkheim\Domain\Validation\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Database\Connection;

/**
 * ProfileRepository — player and guild profile caching and data retrieval.
 * Mirrors the legacy `weProfiles` class with PSR-4 namespace.
 */
class ProfileRepository
{
    private $_request;
    private ?string $_type = null;
    private int $_reqMaxLen;
    private string $_guildsCachePath;
    private string $_playersCachePath;
    private int $_cacheUpdateTime;
    private $_fileData;

    protected Common $common;
    protected $dB;
    private array $cfg;

    public function __construct()
    {
        $this->common = new Common();
        $this->dB     = Connection::Database('MuOnline');

        $this->_guildsCachePath  = __PATH_CACHE__ . 'profiles/guilds/';
        $this->_playersCachePath = __PATH_CACHE__ . 'profiles/players/';
        $this->_cacheUpdateTime  = 300;

        $this->_checkCacheDir($this->_guildsCachePath);
        $this->_checkCacheDir($this->_playersCachePath);

        $profileConfig = BootstrapContext::configProvider()?->moduleConfig('profiles');
        if (! is_array($profileConfig)) {
            throw new \Exception(Translator::phrase('error_25', true));
        }
        $this->cfg = $profileConfig;
    }

    public function setType(string $input): void
    {
        if ($input === 'guild') {
            $this->_type      = 'guild';
            $this->_reqMaxLen = 8;
        } else {
            $this->_type      = 'player';
            $this->_reqMaxLen = 10;
        }
    }

    public function setRequest($input): void
    {
        if (array_key_exists('encode', $this->cfg) && $this->cfg['encode'] == 1) {
            if (! Validator::Chars($input, ['a-z', 'A-Z', '0-9', '_', '-'])) {
                throw new \Exception(Translator::phrase('error_25', true));
            }
            $decodedReq = Encoder::base64urlDecode((string) $input);
            if (! $decodedReq) {
                throw new \Exception(Translator::phrase('error_25', true));
            }
            $this->_request = $decodedReq;
            return;
        }

        if (! Validator::AlphaNumeric($input)) {
            throw new \Exception(Translator::phrase('error_25', true));
        }
        if (strlen($input) > $this->_reqMaxLen) {
            throw new \Exception(Translator::phrase('error_25', true));
        }
        if (strlen($input) < 4) {
            throw new \Exception(Translator::phrase('error_25', true));
        }
        $this->_request = $input;
    }

    public function data(): array
    {
        if (! Validator::hasValue($this->_type)) {
            throw new \Exception(Translator::phrase('error_21', true));
        }
        if (! Validator::hasValue($this->_request)) {
            throw new \Exception(Translator::phrase('error_21', true));
        }
        $this->_checkCache();
        return explode('|', $this->_fileData);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function _checkCacheDir(string $path): void
    {
        if (! Validator::hasValue($path)) {
            return;
        }
        if (! file_exists($path) || ! is_dir($path)) {
            $msg = BootstrapContext::cmsValue('error_reporting', true)
                ? "Invalid cache directory ($path)"
                : Translator::phrase('error_21', true);
            throw new \Exception($msg);
        }
        if (! is_writable($path)) {
            $msg = BootstrapContext::cmsValue('error_reporting', true)
                ? "The cache directory is not writable ($path)"
                : Translator::phrase('error_21', true);
            throw new \Exception($msg);
        }
    }

    private function _checkCache(): void
    {
        if ($this->_type === 'guild') {
            $reqFile = $this->_guildsCachePath . strtolower($this->_request) . '.cache';
            if (! file_exists($reqFile)) {
                $this->_cacheGuildData();
            }
            $fileData = explode('|', (string) file_get_contents($reqFile));
            if (time() > ((int) $fileData[0] + $this->_cacheUpdateTime)) {
                $this->_cacheGuildData();
            }
        } else {
            $reqFile = $this->_playersCachePath . strtolower($this->_request) . '.cache';
            if (! file_exists($reqFile)) {
                $this->_cachePlayerData();
            }
            $fileData = explode('|', (string) file_get_contents($reqFile));
            if (time() > ((int) $fileData[0] + $this->_cacheUpdateTime)) {
                $this->_cachePlayerData();
            }
        }
        $this->_fileData = file_get_contents($reqFile);
    }

    private function _cacheGuildData(): void
    {
        $guildData = $this->dB->query_fetch_single(
            "SELECT *, CONVERT(varchar(max), " . _CLMN_GUILD_LOGO_ . ", 2) as " . _CLMN_GUILD_LOGO_ . " FROM " . _TBL_GUILD_ . " WHERE " . _CLMN_GUILD_NAME_ . " = ?",
            [$this->_request],
        );
        if (! $guildData) {
            throw new \Exception(Translator::phrase('error_25', true));
        }

        $guildMembers = $this->dB->query_fetch(
            "SELECT * FROM " . _TBL_GUILDMEMB_ . " WHERE " . _CLMN_GUILDMEMB_NAME_ . " = ?",
            [$this->_request],
        );
        if (! $guildMembers) {
            throw new \Exception(Translator::phrase('error_25', true));
        }

        $members = [];
        foreach ($guildMembers as $gmember) {
            $members[] = $gmember[_CLMN_GUILDMEMB_CHAR_];
        }

        $data = [
            time(),
            $guildData[_CLMN_GUILD_NAME_],
            $guildData[_CLMN_GUILD_LOGO_],
            $guildData[_CLMN_GUILD_SCORE_],
            $guildData[_CLMN_GUILD_MASTER_],
            implode(',', $members),
        ];

        $reqFile = $this->_guildsCachePath . strtolower($this->_request) . '.cache';
        $fp      = fopen($reqFile, 'w+b');
        fwrite($fp, implode('|', $data));
        fclose($fp);
    }

    private function _cachePlayerData(): void
    {
        $character  = new Character();
        $playerData = $character->CharacterData($this->_request);
        if (! $playerData) {
            throw new \Exception(Translator::phrase('error_25', true));
        }

        /** @phpstan-ignore equal.alwaysFalse */
        if (_TBL_MASTERLVL_ == _TBL_CHR_) {
            $playerMasterLevel = $playerData[_CLMN_ML_LVL_] ?? 0;
        } else {
            $masterLevelInfo   = $character->getMasterLevelInfo($this->_request);
            $playerMasterLevel = is_array($masterLevelInfo) ? $masterLevelInfo[_CLMN_ML_LVL_] : 0;
        }

        $guild     = '';
        $guildData = $this->dB->query_fetch_single(
            "SELECT * FROM " . _TBL_GUILDMEMB_ . " WHERE " . _CLMN_GUILDMEMB_CHAR_ . " = ?",
            [$this->_request],
        );
        if ($guildData) {
            $guild = $guildData[_CLMN_GUILDMEMB_NAME_];
        }

        $data = [
            time(),
            $playerData[_CLMN_CHR_NAME_],
            $playerData[_CLMN_CHR_CLASS_],
            $playerData[_CLMN_CHR_LVL_],
            $playerData[_CLMN_CHR_RSTS_],
            $playerData[_CLMN_CHR_STAT_STR_],
            $playerData[_CLMN_CHR_STAT_AGI_],
            $playerData[_CLMN_CHR_STAT_VIT_],
            $playerData[_CLMN_CHR_STAT_ENE_],
            $playerData[_CLMN_CHR_STAT_CMD_] ?? 0,
            $playerData[_CLMN_CHR_PK_KILLS_],
            Validator::hasValue($playerData[_CLMN_CHR_GRSTS_]) ? $playerData[_CLMN_CHR_GRSTS_] : 0,
            $guild,
            0,
            Validator::hasValue($playerMasterLevel) ? $playerMasterLevel : 0,
        ];

        $reqFile = $this->_playersCachePath . strtolower($this->_request) . '.cache';
        $fp      = fopen($reqFile, 'w+b');
        fwrite($fp, implode('|', $data));
        fclose($fp);
    }
}
