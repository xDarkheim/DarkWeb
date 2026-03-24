<?php

declare(strict_types=1);

namespace Darkheim\Application\Credits;

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Character\Character;
use Darkheim\Application\Shared\Language\Translator;
use Darkheim\Domain\Validation\Validator;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\Database\dB;
use Darkheim\Infrastructure\Runtime\Contracts\QueryStore;
use Darkheim\Infrastructure\Runtime\Native\NativeQueryStore;
use Darkheim\Infrastructure\Runtime\Support\ServerContext;

/**
 * CreditSystem — add/subtract credits, manage configurations, log transactions.
 */
class CreditSystem
{
    private int $_configId      = 0;
    private string $_identifier = '';

    private string $_configTitle      = '';
    private string $_configDatabase   = '';
    private string $_configTable      = '';
    private string $_configCreditsCol = '';
    private string $_configUserCol    = '';
    private string $_configUserColId  = '';
    public int $_configCheckOnline    = 1 {
        set(bool|int $value) {
            $this->_configCheckOnline = $value ? 1 : 0;
        }
    }
    public int $_configDisplay = 0 {
        set(bool|int $value) {
            $this->_configDisplay = $value ? 1 : 0;
        }
    }

    /**
     * @var array<string>
     */
    private array $_allowedUserColId = ['userid', 'username', 'email', 'character'];

    /**
     * @var dB|null
     */
    protected ?dB $muonline = null;
    protected Common $common;
    protected Character $character;
    private ?QueryStore $query            = null;
    private ?ServerContext $serverContext = null;

    public function __construct(?QueryStore $query = null, ?ServerContext $serverContext = null)
    {
        $this->muonline      = Connection::Database('MuOnline');
        $this->common        = new Common();
        $this->character     = new Character();
        $this->query         = $query         ?? new NativeQueryStore();
        $this->serverContext = $serverContext ?? new ServerContext();
    }

    // ─── Identifier setters ───────────────────────────────────────────────────

    public function setIdentifier(string|int $input): void
    {
        if (! $this->_configId) {
            throw new \Exception(Translator::phrase('error_66'));
        }
        $config = $this->showConfigs(true);
        if (! is_array($config) || ! isset($config['config_user_col_id'])) {
            throw new \Exception('invalid config structure');
        }
        switch ($config['config_user_col_id']) {
            case 'userid':    $this->_setUserid($input);
                break;
            case 'username':  $this->_setUsername($input);
                break;
            case 'email':     $this->_setEmail($input);
                break;
            case 'character': $this->_setCharacter($input);
                break;
            default:          throw new \Exception('invalid identifier.');
        }
    }

    private function _setUserid(int|string $input): void
    {
        if (! Validator::UnsignedNumber($input)) {
            throw new \Exception(Translator::phrase('error_67'));
        }
        $this->_identifier = (string) $input;
    }

    private function _setUsername(string $input): void
    {
        if (! Validator::AlphaNumeric($input)) {
            throw new \Exception(Translator::phrase('error_68'));
        }
        if (! Validator::UsernameLength($input)) {
            throw new \Exception(Translator::phrase('error_69'));
        }
        $this->_identifier = $input;
    }

    private function _setEmail(string $input): void
    {
        if (! Validator::Email($input)) {
            throw new \Exception(Translator::phrase('error_70'));
        }
        $this->_identifier = $input;
    }

    private function _setCharacter(string $input): void
    {
        if (! Validator::AlphaNumeric($input)) {
            throw new \Exception(Translator::phrase('error_71'));
        }
        $this->_identifier = $input;
    }

    // ─── Credit operations ────────────────────────────────────────────────────

    public function addCredits(int $input): void
    {
        if (! Validator::UnsignedNumber($input)) {
            throw new \Exception(Translator::phrase('error_72'));
        }
        if (! $this->_configId) {
            throw new \Exception(Translator::phrase('error_66'));
        }
        if (! $this->_identifier) {
            throw new \Exception(Translator::phrase('error_73'));
        }
        $config = $this->showConfigs(true);
        if (! is_array($config) || ! isset($config['config_table'], $config['config_credits_col'], $config['config_user_col'])) {
            throw new \Exception('invalid config structure');
        }
        if ($config['config_checkonline'] && $this->_isOnline($config['config_user_col_id'])) {
            throw new \Exception(Translator::phrase('error_14'));
        }
        $newCredits = $input + $this->getCredits();
        $data       = ['credits' => $newCredits, 'identifier' => $this->_identifier];
        $query      = str_replace(
            ['{TABLE}', '{COLUMN}', '{USER_COLUMN}'],
            [
                is_string($config['config_table']) ? $config['config_table'] : '',
                is_string($config['config_credits_col']) ? $config['config_credits_col'] : '',
                is_string($config['config_user_col']) ? $config['config_user_col'] : '',
            ],
            /** @lang text */
            "UPDATE {TABLE} SET {COLUMN} = :credits WHERE {USER_COLUMN} = :identifier",
        );
        $database = $this->muonline;
        if ($database === null || ! $database->query($query, $data)) {
            throw new \Exception(Translator::phrase('error_74'));
        }
        $this->_addLog((string) $config['config_title'], $input, 'add');
    }

    public function subtractCredits(int $input): void
    {
        if (! Validator::UnsignedNumber($input)) {
            throw new \Exception(Translator::phrase('error_75'));
        }
        if (! $this->_configId) {
            throw new \Exception(Translator::phrase('error_66'));
        }
        if (! $this->_identifier) {
            throw new \Exception(Translator::phrase('error_73'));
        }

        $config = $this->showConfigs(true);
        if (! is_array($config) || ! isset($config['config_checkonline'], $config['config_user_col_id'], $config['config_table'], $config['config_credits_col'], $config['config_user_col'], $config['config_title'])) {
            throw new \Exception('invalid config structure');
        }
        if ($config['config_checkonline'] && $this->_isOnline($config['config_user_col_id'])) {
            throw new \Exception(Translator::phrase('error_14'));
        }
        if ($this->getCredits() < $input) {
            throw new \Exception(Translator::phrase('error_40'));
        }

        $data  = ['credits' => $input, 'identifier' => $this->_identifier];
        $query = str_replace(
            ['{TABLE}', '{COLUMN}', '{USER_COLUMN}'],
            [
                is_string($config['config_table']) ? $config['config_table'] : '',
                is_string($config['config_credits_col']) ? $config['config_credits_col'] : '',
                is_string($config['config_user_col']) ? $config['config_user_col'] : '',
            ],
            /** @lang text */
            "UPDATE {TABLE} SET {COLUMN} = {COLUMN} - :credits WHERE {USER_COLUMN} = :identifier",
        );

        $database = $this->muonline;
        if ($database === null || ! $database->query($query, $data)) {
            throw new \Exception(Translator::phrase('error_76'));
        }

        $this->_addLog((string) $config['config_title'], $input, 'subtract');
    }

    public function getCredits(): int
    {
        if (! $this->_configId) {
            throw new \Exception(Translator::phrase('error_66'));
        }
        if (! $this->_identifier) {
            throw new \Exception(Translator::phrase('error_66'));
        }
        $config   = $this->showConfigs(true);
        $database = $this->muonline;
        $data     = ['identifier' => $this->_identifier];
        if (! is_array($config) || ! isset($config['config_table'], $config['config_credits_col'], $config['config_user_col'])) {
            throw new \Exception('invalid config structure');
        }
        $query = str_replace(
            ['{TABLE}', '{COLUMN}', '{USER_COLUMN}'],
            [
                is_string($config['config_table']) ? $config['config_table'] : '',
                is_string($config['config_credits_col']) ? $config['config_credits_col'] : '',
                is_string($config['config_user_col']) ? $config['config_user_col'] : '',
            ],
            /** @lang text */
            "SELECT {COLUMN} FROM {TABLE} WHERE {USER_COLUMN} = :identifier",
        );
        if ($database === null) {
            throw new \Exception('Database connection not available');
        }
        $result = $database->query_fetch_single($query, $data);
        if (! is_array($result) || ! isset($result[$config['config_credits_col']]) || ! is_numeric($result[$config['config_credits_col']])) {
            throw new \Exception(Translator::phrase('error_89'));
        }
        return (int) $result[$config['config_credits_col']];
    }

    // ─── Config management ────────────────────────────────────────────────────

    public function setConfigId(int $input): void
    {
        if (! Validator::UnsignedNumber($input)) {
            throw new \Exception(Translator::phrase('error_77'));
        }
        if (! $this->_configurationExists($input)) {
            throw new \Exception(Translator::phrase('error_77'));
        }
        $this->_configId = $input;
    }

    public function setConfigTitle(string $input): void
    {
        if (! Validator::Chars($input, ['a-z', 'A-Z', '0-9', ' '])) {
            throw new \Exception(Translator::phrase('error_78'));
        }
        $this->_configTitle = $input;
    }

    public function setConfigDatabase(string $input): void
    {
        if (! Validator::Chars($input, ['a-z', 'A-Z', '0-9', '_'])) {
            throw new \Exception(Translator::phrase('error_79'));
        }
        $this->_configDatabase = $input;
    }

    public function setConfigTable(string $input): void
    {
        if (! Validator::Chars($input, ['a-z', 'A-Z', '0-9', '_'])) {
            throw new \Exception(Translator::phrase('error_80'));
        }
        $this->_configTable = $input;
    }

    public function setConfigCreditsColumn(string $input): void
    {
        if (! Validator::Chars($input, ['a-z', 'A-Z', '0-9', '_'])) {
            throw new \Exception(Translator::phrase('error_81'));
        }
        $this->_configCreditsCol = $input;
    }

    public function setConfigUserColumn(string $input): void
    {
        if (! Validator::Chars($input, ['a-z', 'A-Z', '0-9', '_'])) {
            throw new \Exception(Translator::phrase('error_82'));
        }
        $this->_configUserCol = $input;
    }

    public function setConfigUserColumnId(string $input): void
    {
        if (! Validator::AlphaNumeric($input)) {
            throw new \Exception(Translator::phrase('error_83'));
        }
        if (! in_array($input, $this->_allowedUserColId, true)) {
            throw new \Exception(Translator::phrase('error_83'));
        }
        $this->_configUserColId = $input;
    }

    public function saveConfig(): void
    {
        if (! $this->_configTitle || ! $this->_configDatabase || ! $this->_configTable || ! $this->_configCreditsCol || ! $this->_configUserCol || ! $this->_configUserColId) {
            throw new \Exception(Translator::phrase('error_84'));
        }

        $data  = ['title' => $this->_configTitle, 'database' => $this->_configDatabase, 'table' => $this->_configTable, 'creditscol' => $this->_configCreditsCol, 'usercol' => $this->_configUserCol, 'usercolid' => $this->_configUserColId, 'checkonline' => $this->_configCheckOnline, 'display' => $this->_configDisplay];
        $query = "INSERT INTO " . Credits_Config . " (config_title, config_database, config_table, config_credits_col, config_user_col, config_user_col_id, config_checkonline, config_display) VALUES (:title, :database, :table, :creditscol, :usercol, :usercolid, :checkonline, :display)";

        if (! $this->muonline->query($query, $data)) {
            throw new \Exception(Translator::phrase('error_85'));
        }
    }

    public function editConfig(): void
    {
        if (! $this->_configId || ! $this->_configTitle || ! $this->_configDatabase || ! $this->_configTable || ! $this->_configCreditsCol || ! $this->_configUserCol || ! $this->_configUserColId) {
            throw new \Exception(Translator::phrase('error_84'));
        }

        $data  = ['id' => $this->_configId, 'title' => $this->_configTitle, 'database' => $this->_configDatabase, 'table' => $this->_configTable, 'creditscol' => $this->_configCreditsCol, 'usercol' => $this->_configUserCol, 'usercolid' => $this->_configUserColId, 'checkonline' => $this->_configCheckOnline, 'display' => $this->_configDisplay];
        $query = "UPDATE " . Credits_Config . " SET config_title = :title, config_database = :database, config_table = :table, config_credits_col = :creditscol, config_user_col= :usercol, config_user_col_id = :usercolid, config_checkonline = :checkonline, config_display = :display WHERE config_id = :id";

        if (! $this->muonline->query($query, $data)) {
            throw new \Exception(Translator::phrase('error_86'));
        }
    }

    public function deleteConfig(): void
    {
        if (! $this->_configId) {
            throw new \Exception(Translator::phrase('error_66'));
        }
        if ($this->muonline === null || ! $this->muonline->query("DELETE FROM " . Credits_Config . " WHERE config_id = ?", [$this->_configId])) {
            throw new \Exception(Translator::phrase('error_87'));
        }
    }

    public function showConfigs(bool $singleConfig = false): false|array|null
    {
        /**
         * @return false|array<string, mixed>|array<int, array<string, mixed>>|null
         */
        if ($singleConfig) {
            if (! $this->_configId) {
                throw new \Exception(Translator::phrase('error_66'));
            }
            if ($this->muonline === null) {
                throw new \Exception('Database connection not available');
            }
            $single = $this->muonline->query_fetch_single("SELECT * FROM " . Credits_Config . " WHERE config_id = ?", [$this->_configId]);
            return is_array($single) ? $single : false;
        }
        if ($this->muonline === null) {
            throw new \Exception('Database connection not available');
        }
        $result = $this->muonline->query_fetch("SELECT * FROM " . Credits_Config . " ORDER BY config_id");
        if (! is_array($result)) {
            return false;
        }
        // Ensure each config is an array<string, mixed>
        foreach ($result as &$config) {
            if (! is_array($config)) {
                $config = [];
            }
        }
        return $result;
    }

    public function buildSelectInput(string $name = 'creditsconfig', int $default = 1, string $class = ''): string
    {
        $selectName     = Validator::Chars($name, ['a-z', 'A-Z', '0-9', '_']) ? $name : 'creditsconfig';
        $selectedOption = Validator::UnsignedNumber($default) ? $default : 1;
        $configs        = $this->showConfigs();

        $return = $class
            ? '<select name="' . $selectName . '" class="' . $class . '">'
            : '<select name="' . $selectName . '">';

        if (is_array($configs)) {
            $return .= ($default === 0)
                ? '<option value="0" selected>none</option>'
                : '<option value="0">none</option>';
            foreach ($configs as $config) {
                if (! is_array($config) || ! isset($config['config_id'], $config['config_title'])) {
                    continue;
                }
                $selected = ($selectedOption === (is_int($config['config_id']) ? $config['config_id'] : 0)) ? ' selected' : '';
                $title    = is_string($config['config_title']) ? $config['config_title'] : '';
                $id       = is_int($config['config_id']) ? $config['config_id'] : 0;
                $return .= '<option value="' . $id . '"' . $selected . '>' . $title . '</option>';
            }
        } else {
            $return .= '<option value="0" selected>none</option>';
        }
        $return .= '</select>';
        return $return;
    }

    public function getLogs(int $limit = 50): ?array
    {
        $query = str_replace('{LIMIT}', (string) $limit, "SELECT TOP {LIMIT} * FROM " . Credits_Logs . " ORDER BY log_id DESC");
        /**
         * @return array<int, array<string, mixed>>|null
         */
        if ($this->muonline === null) {
            return null;
        }
        $result = $this->muonline->query_fetch($query);
        if (! is_array($result)) {
            return null;
        }
        foreach ($result as &$row) {
            if (! is_array($row)) {
                $row = [];
            }
        }
        return $result;
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function _configurationExists(int $input): bool
    {
        $check = $this->muonline->query_fetch_single("SELECT * FROM " . Credits_Config . " WHERE config_id = ?", [$input]);
        return (bool) $check;
    }

    private function _isOnline(string $input): bool
    {
        if (! $this->_identifier) {
            throw new \Exception(Translator::phrase('error_88'));
        }

        switch ($input) {
            case 'userid':
                $accountInfo = $this->common->accountInformation($this->_identifier);
                if (! is_array($accountInfo) || ! isset($accountInfo[_CLMN_USERNM_])) {
                    throw new \Exception(Translator::phrase('error_12'));
                }
                return (bool) $this->common->accountOnline($accountInfo[_CLMN_USERNM_]);

            case 'username':
                return (bool) $this->common->accountOnline($this->_identifier);

            case 'email':
                $userId = $this->common->retrieveUserIDbyEmail($this->_identifier);
                if (! is_string($userId)) {
                    throw new \Exception(Translator::phrase('error_12'));
                }
                $accountInfo = $this->common->accountInformation($userId);
                if (! is_array($accountInfo) || ! isset($accountInfo[_CLMN_USERNM_])) {
                    throw new \Exception(Translator::phrase('error_12'));
                }
                return (bool) $this->common->accountOnline($accountInfo[_CLMN_USERNM_]);

            case 'character':
                $characterData = $this->character->CharacterData($this->_identifier);
                if (! is_array($characterData) || ! isset($characterData[_CLMN_CHR_ACCID_])) {
                    throw new \Exception(Translator::phrase('error_12'));
                }
                return (bool) $this->common->accountOnline($characterData[_CLMN_CHR_ACCID_]);

            default:
                throw new \Exception(Translator::phrase('error_88'));
        }
    }

    private function _addLog(string $configTitle = 'unknown', int $credits = 0, string $transaction = 'unknown'): void
    {
        $inadmincp = (defined('access') && access === 'admincp') ? 1 : 0;
        $module    = $inadmincp === 1
            ? (string) $this->query()->get('module', 'unknown')
            : ($this->query()->get('page', '') . '/' . $this->query()->get('subpage', ''));
        $ip = ($this->server()->remoteAddress() ?? '0.0.0.0');

        $data = [
            'config'      => $configTitle,
            'identifier'  => $this->_identifier,
            'credits'     => $credits,
            'transaction' => $transaction,
            'timestamp'   => time(),
            'inadmincp'   => $inadmincp,
            'module'      => $module,
            'ip'          => $ip,
        ];
        $query = "INSERT INTO " . Credits_Logs . " (log_config, log_identifier, log_credits, log_transaction, log_date, log_inadmincp, log_module, log_ip) VALUES (:config, :identifier, :credits, :transaction, :timestamp, :inadmincp, :module, :ip)";

        $this->muonline->query($query, $data);
    }

    private function query(): QueryStore
    {
        if (! $this->query instanceof QueryStore) {
            $this->query = new NativeQueryStore();
        }

        return $this->query;
    }

    private function server(): ServerContext
    {
        if (! $this->serverContext instanceof ServerContext) {
            $this->serverContext = new ServerContext();
        }

        return $this->serverContext;
    }
}
