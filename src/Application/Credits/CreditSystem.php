<?php

declare(strict_types=1);

namespace Darkheim\Application\Credits;

use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Application\Auth\Common;
use Darkheim\Application\Character\Character;
use Darkheim\Domain\Validator;

/**
 * CreditSystem — add/subtract credits, manage configurations, log transactions.
 */
class CreditSystem
{
    private $_configId;
    private $_identifier;

    private $_configTitle;
    private $_configDatabase;
    private $_configTable;
    private $_configCreditsCol;
    private $_configUserCol;
    private $_configUserColId;
    private int $_configCheckOnline = 1;
    private int $_configDisplay     = 0;

    private array $_allowedUserColId = ['userid', 'username', 'email', 'character'];

    protected $muonline;
    protected Common $common;
    protected Character $character;

    public function __construct()
    {
        $this->muonline  = Connection::Database('MuOnline');
        $this->common    = new Common();
        $this->character = new Character();
    }

    // ─── Identifier setters ───────────────────────────────────────────────────

    public function setIdentifier($input): void
    {
        if (!$this->_configId) throw new \Exception(lang('error_66'));
        $config = $this->showConfigs(true);

        switch ($config['config_user_col_id']) {
            case 'userid':    $this->_setUserid($input);    break;
            case 'username':  $this->_setUsername($input);  break;
            case 'email':     $this->_setEmail($input);     break;
            case 'character': $this->_setCharacter($input); break;
            default:          throw new \Exception('invalid identifier.');
        }
    }

    private function _setUserid($input): void
    {
        if (!Validator::UnsignedNumber($input)) throw new \Exception(lang('error_67'));
        $this->_identifier = $input;
    }

    private function _setUsername($input): void
    {
        if (!Validator::AlphaNumeric($input)) throw new \Exception(lang('error_68'));
        if (!Validator::UsernameLength($input)) throw new \Exception(lang('error_69'));
        $this->_identifier = $input;
    }

    private function _setEmail($input): void
    {
        if (!Validator::Email($input)) throw new \Exception(lang('error_70'));
        $this->_identifier = $input;
    }

    private function _setCharacter($input): void
    {
        if (!Validator::AlphaNumeric($input)) throw new \Exception(lang('error_71'));
        $this->_identifier = $input;
    }

    // ─── Credit operations ────────────────────────────────────────────────────

    public function addCredits($input): void
    {
        if (!Validator::UnsignedNumber($input)) throw new \Exception(lang('error_72'));
        if (!$this->_configId)   throw new \Exception(lang('error_66'));
        if (!$this->_identifier) throw new \Exception(lang('error_73'));

        $config = $this->showConfigs(true);
        if ($config['config_checkonline'] && $this->_isOnline($config['config_user_col_id'])) throw new \Exception(lang('error_14'));

        $newCredits = $input + $this->getCredits();
        $data       = ['credits' => $newCredits, 'identifier' => $this->_identifier];
        // noinspection SqlNoDataSourceInspection — {TABLE}/{COLUMN}/{USER_COLUMN} are runtime-replaced placeholders
        $query      = str_replace(
            ['{TABLE}', '{COLUMN}', '{USER_COLUMN}'],
            [$config['config_table'], $config['config_credits_col'], $config['config_user_col']],
            /** @lang text */ "UPDATE {TABLE} SET {COLUMN} = :credits WHERE {USER_COLUMN} = :identifier"
        );

        $database = $this->muonline;
        if (!$database->query($query, $data)) throw new \Exception(lang('error_74'));

        $this->_addLog($config['config_title'], $input, 'add');
    }

    public function subtractCredits($input): void
    {
        if (!Validator::UnsignedNumber($input)) throw new \Exception(lang('error_75'));
        if (!$this->_configId)   throw new \Exception(lang('error_66'));
        if (!$this->_identifier) throw new \Exception(lang('error_73'));

        $config = $this->showConfigs(true);
        if ($config['config_checkonline'] && $this->_isOnline($config['config_user_col_id'])) throw new \Exception(lang('error_14'));
        if ($this->getCredits() < $input) throw new \Exception(lang('error_40', true));

        $data  = ['credits' => $input, 'identifier' => $this->_identifier];
        // noinspection SqlNoDataSourceInspection — {TABLE}/{COLUMN}/{USER_COLUMN} are runtime-replaced placeholders
        $query = str_replace(
            ['{TABLE}', '{COLUMN}', '{USER_COLUMN}'],
            [$config['config_table'], $config['config_credits_col'], $config['config_user_col']],
            /** @lang text */ "UPDATE {TABLE} SET {COLUMN} = {COLUMN} - :credits WHERE {USER_COLUMN} = :identifier"
        );

        $database = $this->muonline;
        if (!$database->query($query, $data)) throw new \Exception(lang('error_76'));

        $this->_addLog($config['config_title'], $input, 'subtract');
    }

    public function getCredits()
    {
        if (!$this->_configId)   throw new \Exception(lang('error_66'));
        if (!$this->_identifier) throw new \Exception(lang('error_66'));

        $config   = $this->showConfigs(true);
        $database = $this->muonline;
        $data     = ['identifier' => $this->_identifier];
        // noinspection SqlNoDataSourceInspection — {TABLE}/{COLUMN}/{USER_COLUMN} are runtime-replaced placeholders
        $query    = str_replace(
            ['{TABLE}', '{COLUMN}', '{USER_COLUMN}'],
            [$config['config_table'], $config['config_credits_col'], $config['config_user_col']],
            /** @lang text */ "SELECT {COLUMN} FROM {TABLE} WHERE {USER_COLUMN} = :identifier"
        );

        $result = $database->query_fetch_single($query, $data);
        if (!$result) throw new \Exception(lang('error_89'));

        return $result[$config['config_credits_col']];
    }

    // ─── Config management ────────────────────────────────────────────────────

    public function setConfigId($input): void
    {
        if (!Validator::UnsignedNumber($input)) throw new \Exception(lang('error_77'));
        if (!$this->_configurationExists($input)) throw new \Exception(lang('error_77'));
        $this->_configId = $input;
    }

    public function setConfigTitle($input): void
    {
        if (!Validator::Chars($input, ['a-z', 'A-Z', '0-9', ' '])) throw new \Exception(lang('error_78'));
        $this->_configTitle = $input;
    }

    public function setConfigDatabase($input): void
    {
        if (!Validator::Chars($input, ['a-z', 'A-Z', '0-9', '_'])) throw new \Exception(lang('error_79'));
        $this->_configDatabase = $input;
    }

    public function setConfigTable($input): void
    {
        if (!Validator::Chars($input, ['a-z', 'A-Z', '0-9', '_'])) throw new \Exception(lang('error_80'));
        $this->_configTable = $input;
    }

    public function setConfigCreditsColumn($input): void
    {
        if (!Validator::Chars($input, ['a-z', 'A-Z', '0-9', '_'])) throw new \Exception(lang('error_81'));
        $this->_configCreditsCol = $input;
    }

    public function setConfigUserColumn($input): void
    {
        if (!Validator::Chars($input, ['a-z', 'A-Z', '0-9', '_'])) throw new \Exception(lang('error_82'));
        $this->_configUserCol = $input;
    }

    public function setConfigUserColumnId($input): void
    {
        if (!Validator::AlphaNumeric($input)) throw new \Exception(lang('error_83'));
        if (!in_array($input, $this->_allowedUserColId, true)) throw new \Exception(lang('error_83'));
        $this->_configUserColId = $input;
    }

    public function setConfigCheckOnline($input): void  { $this->_configCheckOnline = $input ? 1 : 0; }
    public function setConfigDisplay($input): void      { $this->_configDisplay = $input ? 1 : 0; }

    public function saveConfig(): void
    {
        if (!$this->_configTitle || !$this->_configDatabase || !$this->_configTable || !$this->_configCreditsCol || !$this->_configUserCol || !$this->_configUserColId) {
            throw new \Exception(lang('error_84'));
        }

        $data  = ['title' => $this->_configTitle, 'database' => $this->_configDatabase, 'table' => $this->_configTable, 'creditscol' => $this->_configCreditsCol, 'usercol' => $this->_configUserCol, 'usercolid' => $this->_configUserColId, 'checkonline' => $this->_configCheckOnline, 'display' => $this->_configDisplay];
        $query = "INSERT INTO " . Credits_Config . " (config_title, config_database, config_table, config_credits_col, config_user_col, config_user_col_id, config_checkonline, config_display) VALUES (:title, :database, :table, :creditscol, :usercol, :usercolid, :checkonline, :display)";

        if (!$this->muonline->query($query, $data)) throw new \Exception(lang('error_85'));
    }

    public function editConfig(): void
    {
        if (!$this->_configId || !$this->_configTitle || !$this->_configDatabase || !$this->_configTable || !$this->_configCreditsCol || !$this->_configUserCol || !$this->_configUserColId) {
            throw new \Exception(lang('error_84'));
        }

        $data  = ['id' => $this->_configId, 'title' => $this->_configTitle, 'database' => $this->_configDatabase, 'table' => $this->_configTable, 'creditscol' => $this->_configCreditsCol, 'usercol' => $this->_configUserCol, 'usercolid' => $this->_configUserColId, 'checkonline' => $this->_configCheckOnline, 'display' => $this->_configDisplay];
        $query = "UPDATE " . Credits_Config . " SET config_title = :title, config_database = :database, config_table = :table, config_credits_col = :creditscol, config_user_col= :usercol, config_user_col_id = :usercolid, config_checkonline = :checkonline, config_display = :display WHERE config_id = :id";

        if (!$this->muonline->query($query, $data)) throw new \Exception(lang('error_86'));
    }

    public function deleteConfig(): void
    {
        if (!$this->_configId) throw new \Exception(lang('error_66'));
        if (!$this->muonline->query("DELETE FROM " . Credits_Config . " WHERE config_id = ?", [$this->_configId])) {
            throw new \Exception(lang('error_87'));
        }
    }

    public function showConfigs($singleConfig = false): false|array|null
    {
        if ($singleConfig) {
            if (!$this->_configId) throw new \Exception(lang('error_66'));
            return $this->muonline->query_fetch_single("SELECT * FROM " . Credits_Config . " WHERE config_id = ?", [$this->_configId]);
        }
        $result = $this->muonline->query_fetch("SELECT * FROM " . Credits_Config . " ORDER BY config_id");
        return $result ?: false;
    }

    public function buildSelectInput(string $name = 'creditsconfig', $default = 1, string $class = ''): string
    {
        $selectName     = Validator::Chars($name, ['a-z', 'A-Z', '0-9', '_']) ? $name : 'creditsconfig';
        $selectedOption = Validator::UnsignedNumber($default) ? $default : 1;
        $configs        = $this->showConfigs();

        $return = $class
            ? '<select name="' . $selectName . '" class="' . $class . '">'
            : '<select name="' . $selectName . '">';

        if (is_array($configs)) {
            $return .= ($default == 0)
                ? '<option value="0" selected>none</option>'
                : '<option value="0">none</option>';
            foreach ($configs as $config) {
                $selected = ($selectedOption == $config['config_id']) ? ' selected' : '';
                $return  .= '<option value="' . $config['config_id'] . '"' . $selected . '>' . $config['config_title'] . '</option>';
            }
        } else {
            $return .= '<option value="0" selected>none</option>';
        }
        $return .= '</select>';
        return $return;
    }

    public function getLogs(int $limit = 50): ?array
    {
        $query  = str_replace('{LIMIT}', (string) $limit, "SELECT TOP {LIMIT} * FROM " . Credits_Logs . " ORDER BY log_id DESC");
        $result = $this->muonline->query_fetch($query);
        return is_array($result) ? $result : null;
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function _configurationExists($input): bool
    {
        $check = $this->muonline->query_fetch_single("SELECT * FROM " . Credits_Config . " WHERE config_id = ?", [$input]);
        return (bool) $check;
    }

    private function _isOnline(string $input): bool
    {
        if (!$this->_identifier) throw new \Exception(lang('error_88'));

        switch ($input) {
            case 'userid':
                $accountInfo = $this->common->accountInformation($this->_identifier);
                if (!$accountInfo) throw new \Exception(lang('error_12'));
                return (bool) $this->common->accountOnline($accountInfo[_CLMN_USERNM_]);

            case 'username':
                return (bool) $this->common->accountOnline($this->_identifier);

            case 'email':
                $userId = $this->common->retrieveUserIDbyEmail($this->_identifier);
                if (!$userId) throw new \Exception(lang('error_12'));
                $accountInfo = $this->common->accountInformation($userId);
                if (!$accountInfo) throw new \Exception(lang('error_12'));
                return (bool) $this->common->accountOnline($accountInfo[_CLMN_USERNM_]);

            case 'character':
                $characterData = $this->character->CharacterData($this->_identifier);
                if (!$characterData) throw new \Exception(lang('error_12'));
                return (bool) $this->common->accountOnline($characterData[_CLMN_CHR_ACCID_]);

            default:
                throw new \Exception(lang('error_88'));
        }
    }

    private function _addLog(string $configTitle = 'unknown', $credits = 0, string $transaction = 'unknown'): void
    {
        $inadmincp = (defined('access') && access == 'admincp') ? 1 : 0;
        $module    = $inadmincp == 1
            ? ($_GET['module'] ?? 'unknown')
            : (($_GET['page'] ?? '') . '/' . ($_GET['subpage'] ?? ''));
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $data  = ['config' => $configTitle, 'identifier' => $this->_identifier, 'credits' => $credits, 'transaction' => $transaction, 'timestamp' => time(), 'inadmincp' => $inadmincp, 'module' => $module, 'ip' => $ip];
        $query = "INSERT INTO " . Credits_Logs . " (log_config, log_identifier, log_credits, log_transaction, log_date, log_inadmincp, log_module, log_ip) VALUES (:config, :identifier, :credits, :transaction, :timestamp, :inadmincp, :module, :ip)";

        $this->muonline->query($query, $data);
    }
}

