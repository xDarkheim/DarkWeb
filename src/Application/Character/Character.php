<?php

declare(strict_types=1);

namespace Darkheim\Application\Character;

use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Application\Auth\Common;
use Darkheim\Application\Account\Account;
use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Domain\Validator;

/**
 * Character — game character operations: reset, stats, unstick, clear PK, skill tree, add stats.
 */
class Character
{
    protected array $_classData;

    protected $_userid;
    protected $_username;
    public $_character {
        set {
            $this->_character = $value;
        }
    }

    protected int $_unstickMap    = 0;
    protected int $_unstickCoordX = 125;
    protected int $_unstickCoordY = 125;

    protected int $_clearPkLevel          = 3;
    protected int $_skilEnhanceTreeLevel  = 800;

    protected int $_strength  = 0;
    protected int $_agility   = 0;
    protected int $_vitality  = 0;
    protected int $_energy    = 0;
    protected int $_command   = 0;

    protected $muonline;
    protected Common $common;

    public function __construct()
    {
        $this->muonline = Connection::Database('MuOnline');
        $this->common   = new Common();

        $classData = custom('character_class');
        if (!is_array($classData)) throw new \Exception(lang('error_108'));
        $this->_classData = $classData;
    }

    // ─── Setters ─────────────────────────────────────────────────────────────

    public function setUserid($userid): void
    {
        if (!Validator::UnsignedNumber($userid)) throw new \Exception(lang('error_111'));
        $this->_userid = $userid;
    }

    public function setUsername($username): void
    {
        if (!Validator::UsernameLength($username)) throw new \Exception(lang('error_112'));
        $this->_username = $username;
    }

    public function setStrength($value): void
    {
        if (!Validator::UnsignedNumber($value)) throw new \Exception(lang('error_122'));
        $this->_strength = (int) $value;
    }

    public function setAgility($value): void
    {
        if (!Validator::UnsignedNumber($value)) throw new \Exception(lang('error_122'));
        $this->_agility = (int) $value;
    }

    public function setVitality($value): void
    {
        if (!Validator::UnsignedNumber($value)) throw new \Exception(lang('error_122'));
        $this->_vitality = (int) $value;
    }

    public function setEnergy($value): void
    {
        if (!Validator::UnsignedNumber($value)) throw new \Exception(lang('error_122'));
        $this->_energy = (int) $value;
    }

    public function setCommand($value): void
    {
        if (!Validator::UnsignedNumber($value)) throw new \Exception(lang('error_122'));
        $this->_command = (int) $value;
    }

    // ─── Actions ─────────────────────────────────────────────────────────────

    public function CharacterReset(): void
    {
        if (!check_value($this->_username))  throw new \Exception(lang('error_21'));
        if (!check_value($this->_character)) throw new \Exception(lang('error_21'));
        if (!check_value($this->_userid))    throw new \Exception(lang('error_21'));
        if (!$this->CharacterExists($this->_character)) throw new \Exception(lang('error_32'));
        if (!$this->CharacterBelongsToAccount($this->_character, $this->_username)) throw new \Exception(lang('error_32'));

        $account = new Account();
        if ($account->accountOnline($this->_username)) throw new \Exception(lang('error_14'));

        $characterData = $this->CharacterData($this->_character);
        $resetNumber   = $characterData[_CLMN_CHR_RSTS_] + 1;

        if ((mconfig('required_level') >= 1)
            && $characterData[_CLMN_CHR_LVL_] < mconfig('required_level')
        ) throw new \Exception(lang('error_33'));

        $maxResets = mconfig('maximum_resets');
        if ($maxResets > 0 && $resetNumber > $maxResets) throw new \Exception(lang('error_127'));

        $clearStats       = mconfig('keep_stats') != 1;
        $newLevelUpPoints = mconfig('points_reward') >= 1 ? (int) mconfig('points_reward') : 0;
        if (mconfig('multiply_points_by_resets') == 1) {
            $newLevelUpPoints *= $resetNumber;
        }
        if (!$clearStats) {
            $newLevelUpPoints += $characterData[_CLMN_CHR_LVLUP_POINT_];
        }

        $revertClass = mconfig('revert_class_evolution') == 1;
        if ($revertClass) {
            if (!array_key_exists('class_group', $this->_classData[$characterData[_CLMN_CHR_CLASS_]])) throw new \Exception(lang('error_128'));
            $classGroup = $this->_classData[$characterData[_CLMN_CHR_CLASS_]]['class_group'];
        }

        $zenRequirement = mconfig('zen_cost');
        if ($zenRequirement > 0 && $characterData[_CLMN_CHR_ZEN_] < $zenRequirement) throw new \Exception(lang('error_34'));
        $newZen = $characterData[_CLMN_CHR_ZEN_] - $zenRequirement;

        $creditConfig   = mconfig('credit_config');
        $creditCost     = mconfig('credit_cost');
        $creditSystem   = null;
        if ($creditCost > 0 && $creditConfig != 0) {
            $creditSystem   = new CreditSystem();
            $creditSystem->setConfigId($creditConfig);
            $configSettings = $creditSystem->showConfigs(true);
            $this->_setCreditIdentifier($creditSystem, $configSettings['config_user_col_id']);
            if ($creditSystem->getCredits() < $creditCost) throw new \Exception(langf('error_126', [$configSettings['config_title']]));
        }

        $base_stats     = $this->_getClassBaseStats($characterData[_CLMN_CHR_CLASS_]);
        $clearInventory = mconfig('clear_inventory') == 1;

        $data = [];
        if ($revertClass) { $data['class'] = $classGroup; }
        if ($clearStats)  { $data = array_merge($data, ['str' => $base_stats['str'], 'agi' => $base_stats['agi'], 'vit' => $base_stats['vit'], 'ene' => $base_stats['ene'], 'cmd' => $base_stats['cmd']]); }
        $data['points'] = $newLevelUpPoints;
        if ($zenRequirement > 0) $data['zen'] = $newZen;
        $data['name']   = $characterData[_CLMN_CHR_NAME_];

        $query  = "UPDATE " . _TBL_CHR_ . " SET ";
        $query .= _CLMN_CHR_LVL_ . " = 1, ";
        if ($revertClass)    { $query .= _CLMN_CHR_CLASS_ . " = :class, "; $query .= _CLMN_CHR_QUEST_ . " = NULL, "; }
        if ($clearStats)     { $query .= _CLMN_CHR_STAT_STR_ . " = :str, " . _CLMN_CHR_STAT_AGI_ . " = :agi, " . _CLMN_CHR_STAT_VIT_ . " = :vit, " . _CLMN_CHR_STAT_ENE_ . " = :ene, " . _CLMN_CHR_STAT_CMD_ . " = :cmd, "; }
        if ($zenRequirement > 0) $query .= _CLMN_CHR_ZEN_ . " = :zen, ";
        if ($clearInventory) $query .= _CLMN_CHR_INV_ . " = NULL, ";
        $query .= _CLMN_CHR_LVLUP_POINT_ . " = :points, ";
        $query .= _CLMN_CHR_RSTS_ . " = " . _CLMN_CHR_RSTS_ . "+1 ";
        $query .= "WHERE " . _CLMN_CHR_NAME_ . " = :name";

        $result = $this->muonline->query($query, $data);
        if (!$result) throw new \Exception(lang('error_23'));

        if ($creditCost > 0 && $creditConfig != 0) $creditSystem->subtractCredits($creditCost);

        $creditRewardConfig = mconfig('credit_reward_config');
        $creditReward       = mconfig('credit_reward');
        if ($creditReward > 0 && $creditRewardConfig != 0) {
            $rewardSystem = new CreditSystem();
            $rewardSystem->setConfigId($creditRewardConfig);
            $rewardSettings = $rewardSystem->showConfigs(true);
            $this->_setCreditIdentifier($rewardSystem, $rewardSettings['config_user_col_id']);
            $rewardSystem->addCredits($creditReward);
        }

        message('success', lang('success_8'));
    }

    public function CharacterResetStats(): void
    {
        if (!check_value($this->_username))  throw new \Exception(lang('error_21'));
        if (!check_value($this->_character)) throw new \Exception(lang('error_21'));
        if (!check_value($this->_userid))    throw new \Exception(lang('error_21'));
        if (!$this->CharacterExists($this->_character)) throw new \Exception(lang('error_35'));
        if (!$this->CharacterBelongsToAccount($this->_character, $this->_username)) throw new \Exception(lang('error_35'));

        $account = new Account();
        if ($account->accountOnline($this->_username)) throw new \Exception(lang('error_14'));

        $characterData  = $this->CharacterData($this->_character);
        $zenRequirement = mconfig('zen_cost');

        $creditConfig = mconfig('credit_config');
        $creditCost   = mconfig('credit_cost');
        $creditSystem = null;
        if ($creditCost > 0 && $creditConfig != 0) {
            $creditSystem   = new CreditSystem();
            $creditSystem->setConfigId($creditConfig);
            $configSettings = $creditSystem->showConfigs(true);
            $this->_setCreditIdentifier($creditSystem, $configSettings['config_user_col_id']);
            if ($creditSystem->getCredits() < $creditCost) throw new \Exception(langf('error_113', [$configSettings['config_title']]));
        }

        if ($zenRequirement > 0 && $characterData[_CLMN_CHR_ZEN_] < $zenRequirement) throw new \Exception(lang('error_34'));

        $base_stats        = $this->_getClassBaseStats($characterData[_CLMN_CHR_CLASS_]);
        $base_stats_points = array_sum($base_stats);

        $levelUpPoints = $characterData[_CLMN_CHR_STAT_STR_] + $characterData[_CLMN_CHR_STAT_AGI_] + $characterData[_CLMN_CHR_STAT_VIT_] + $characterData[_CLMN_CHR_STAT_ENE_];
        if (array_key_exists(_CLMN_CHR_STAT_CMD_, $characterData)) {
            $levelUpPoints += $characterData[_CLMN_CHR_STAT_CMD_];
        }
        if ($base_stats_points > 0) {
            $levelUpPoints -= $base_stats_points;
        }

        $data = array_merge(
            ['player' => $characterData[_CLMN_CHR_NAME_], 'points' => $levelUpPoints, 'zen' => $zenRequirement],
            $base_stats
        );

        $query  = "UPDATE " . _TBL_CHR_ . " SET " . _CLMN_CHR_STAT_STR_ . " = :str, " . _CLMN_CHR_STAT_AGI_ . " = :agi, " . _CLMN_CHR_STAT_VIT_ . " = :vit, " . _CLMN_CHR_STAT_ENE_ . " = :ene";
        if (array_key_exists(_CLMN_CHR_STAT_CMD_, $characterData)) $query .= ", " . _CLMN_CHR_STAT_CMD_ . " = :cmd";
        $query .= ", " . _CLMN_CHR_ZEN_ . " = " . _CLMN_CHR_ZEN_ . " - :zen";
        $query .= ", " . _CLMN_CHR_LVLUP_POINT_ . " = " . _CLMN_CHR_LVLUP_POINT_ . " + :points WHERE " . _CLMN_CHR_NAME_ . " = :player";

        $result = $this->muonline->query($query, $data);
        if (!$result) throw new \Exception(lang('error_21'));

        if ($creditCost > 0 && $creditConfig != 0) $creditSystem->subtractCredits($creditCost);

        message('success', lang('success_9'));
    }

    public function CharacterClearPK(): void
    {
        if (!check_value($this->_username))  throw new \Exception(lang('error_21'));
        if (!check_value($this->_character)) throw new \Exception(lang('error_21'));
        if (!check_value($this->_userid))    throw new \Exception(lang('error_21'));
        if (!$this->CharacterExists($this->_character)) throw new \Exception(lang('error_36'));
        if (!$this->CharacterBelongsToAccount($this->_character, $this->_username)) throw new \Exception(lang('error_36'));

        $account = new Account();
        if ($account->accountOnline($this->_username)) throw new \Exception(lang('error_14'));

        $characterData = $this->CharacterData($this->_character);
        if ($characterData[_CLMN_CHR_PK_LEVEL_] == $this->_clearPkLevel) throw new \Exception(lang('error_117'));

        $zenRequirement = mconfig('zen_cost');

        $creditConfig = mconfig('credit_config');
        $creditCost   = mconfig('credit_cost');
        $creditSystem = null;
        if ($creditCost > 0 && $creditConfig != 0) {
            $creditSystem   = new CreditSystem();
            $creditSystem->setConfigId($creditConfig);
            $configSettings = $creditSystem->showConfigs(true);
            $this->_setCreditIdentifier($creditSystem, $configSettings['config_user_col_id']);
            if ($creditSystem->getCredits() < $creditCost) throw new \Exception(langf('error_116', [$configSettings['config_title']]));
        }

        if ($zenRequirement > 0 && $characterData[_CLMN_CHR_ZEN_] < $zenRequirement) throw new \Exception(lang('error_34'));

        $data  = ['player' => $characterData[_CLMN_CHR_NAME_], 'pklevel' => $this->_clearPkLevel, 'zen' => $zenRequirement];
        $query = "UPDATE " . _TBL_CHR_ . " SET " . _CLMN_CHR_PK_LEVEL_ . " = :pklevel, " . _CLMN_CHR_PK_TIME_ . " = 0, " . _CLMN_CHR_ZEN_ . " = " . _CLMN_CHR_ZEN_ . " - :zen WHERE " . _CLMN_CHR_NAME_ . " = :player";

        $result = $this->muonline->query($query, $data);
        if (!$result) throw new \Exception(lang('error_21'));

        if ($creditCost > 0 && $creditConfig != 0) $creditSystem->subtractCredits($creditCost);

        message('success', lang('success_10'));
    }

    public function CharacterUnstick(): void
    {
        if (!check_value($this->_username))  throw new \Exception(lang('error_21'));
        if (!check_value($this->_character)) throw new \Exception(lang('error_21'));
        if (!check_value($this->_userid))    throw new \Exception(lang('error_21'));
        if (!$this->CharacterExists($this->_character)) throw new \Exception(lang('error_37'));
        if (!$this->CharacterBelongsToAccount($this->_character, $this->_username)) throw new \Exception(lang('error_37'));

        $account = new Account();
        if ($account->accountOnline($this->_username)) throw new \Exception(lang('error_14'));

        $characterData = $this->CharacterData($this->_character);
        if (($characterData[_CLMN_CHR_MAP_] == $this->_unstickMap)
            && $characterData[_CLMN_CHR_MAP_X_] == $this->_unstickCoordX
            && $characterData[_CLMN_CHR_MAP_Y_] == $this->_unstickCoordY
        ) {
            throw new \Exception(lang('error_115'));
        }

        $zenRequirement = mconfig('zen_cost');

        $creditConfig = mconfig('credit_config');
        $creditCost   = mconfig('credit_cost');
        $creditSystem = null;
        if ($creditCost > 0 && $creditConfig != 0) {
            $creditSystem   = new CreditSystem();
            $creditSystem->setConfigId($creditConfig);
            $configSettings = $creditSystem->showConfigs(true);
            $this->_setCreditIdentifier($creditSystem, $configSettings['config_user_col_id']);
            if ($creditSystem->getCredits() < $creditCost) throw new \Exception(langf('error_114', [$configSettings['config_title']]));
        }

        if ($zenRequirement > 0 && $characterData[_CLMN_CHR_ZEN_] < $zenRequirement) throw new \Exception(lang('error_34'));
        if ($zenRequirement > 0 && !$this->DeductZEN($this->_character, $zenRequirement)) throw new \Exception(lang('error_34'));

        $update = $this->_moveCharacter($this->_character, $this->_unstickMap, $this->_unstickCoordX, $this->_unstickCoordY);
        if (!$update) throw new \Exception(lang('error_21'));

        if ($creditCost > 0 && $creditConfig != 0) $creditSystem->subtractCredits($creditCost);

        message('success', lang('success_11'));
    }

    public function CharacterClearSkillTree(): void
    {
        if (!check_value($this->_username))  throw new \Exception(lang('error_21'));
        if (!check_value($this->_character)) throw new \Exception(lang('error_21'));
        if (!check_value($this->_userid))    throw new \Exception(lang('error_21'));
        if (!$this->CharacterExists($this->_character)) throw new \Exception(lang('error_38'));
        if (!$this->CharacterBelongsToAccount($this->_character, $this->_username)) throw new \Exception(lang('error_38'));

        $account = new Account();
        if ($account->accountOnline($this->_username)) throw new \Exception(lang('error_14'));

        $characterData = $this->CharacterData($this->_character);
        if ($characterData[_CLMN_CHR_LVL_] < mconfig('required_level')) throw new \Exception(lang('error_120'));

        /** @phpstan-ignore notEqual.alwaysTrue */
        $characterMasterLvlData = _TBL_CHR_ != _TBL_MASTERLVL_ ? $this->getMasterLevelInfo($this->_character) : $characterData;
        if (!is_array($characterMasterLvlData)) throw new \Exception(lang('error_119'));
        if ($characterMasterLvlData[_CLMN_ML_LVL_] < mconfig('required_master_level')) throw new \Exception(lang('error_121'));

        $characterLevel = $characterData[_CLMN_CHR_LVL_] + $characterMasterLvlData[_CLMN_ML_LVL_];

        $skillEnhancementTreeEnabled = false;
        $skillEnhancementPoints      = 0;
        if (defined('_CLMN_ML_I4SP_')) {
            $skillEnhancementTreeEnabled = array_key_exists(_CLMN_ML_I4SP_, $characterMasterLvlData);
        }
        if ($skillEnhancementTreeEnabled && $characterLevel > $this->_skilEnhanceTreeLevel) {
            $skillEnhancementPoints = $characterLevel - $this->_skilEnhanceTreeLevel;
        }

        $zenRequirement = mconfig('zen_cost');

        $creditConfig = mconfig('credit_config');
        $creditCost   = mconfig('credit_cost');
        $creditSystem = null;
        if ($creditCost > 0 && $creditConfig != 0) {
            $creditSystem   = new CreditSystem();
            $creditSystem->setConfigId($creditConfig);
            $configSettings = $creditSystem->showConfigs(true);
            $this->_setCreditIdentifier($creditSystem, $configSettings['config_user_col_id']);
            if ($creditSystem->getCredits() < $creditCost) throw new \Exception(langf('error_118', [$configSettings['config_title']]));
        }

        if ($zenRequirement > 0 && $characterData[_CLMN_CHR_ZEN_] < $zenRequirement) throw new \Exception(lang('error_34'));

        $data = ['player' => $this->_character, 'masterpoints' => $characterMasterLvlData[_CLMN_ML_LVL_] - $skillEnhancementPoints];
        if ($skillEnhancementTreeEnabled && $skillEnhancementPoints > 0) {
            $data['skillenhancementpoints'] = $skillEnhancementPoints;
        }

        $query = "UPDATE " . _TBL_MASTERLVL_ . " SET " . _CLMN_ML_POINT_ . " = :masterpoints";
        if (defined('_CLMN_ML_EXP_') && array_key_exists(_CLMN_ML_EXP_, $characterMasterLvlData))  $query .= ", " . _CLMN_ML_EXP_ . " = 0";
        if (defined('_CLMN_ML_NEXP_') && array_key_exists(_CLMN_ML_NEXP_, $characterMasterLvlData)) $query .= ", " . _CLMN_ML_NEXP_ . " = 0";
        if ($skillEnhancementTreeEnabled && $skillEnhancementPoints > 0) $query .= ", " . _CLMN_ML_I4SP_ . " = :skillenhancementpoints";
        $query .= " WHERE " . _CLMN_ML_NAME_ . " = :player";

        if (!$this->_resetMagicList($this->_character)) throw new \Exception(lang('error_21'));
        if (!$this->muonline->query($query, $data))     throw new \Exception(lang('error_21'));
        if ($zenRequirement > 0 && !$this->DeductZEN($this->_character, $zenRequirement)) throw new \Exception(lang('error_34'));

        if ($creditCost > 0 && $creditConfig != 0) $creditSystem->subtractCredits($creditCost);

        message('success', lang('success_12'));
    }

    public function CharacterAddStats(): void
    {
        if (!check_value($this->_username))  throw new \Exception(lang('error_21'));
        if (!check_value($this->_character)) throw new \Exception(lang('error_21'));
        if (!check_value($this->_userid))    throw new \Exception(lang('error_21'));
        if (!$this->CharacterExists($this->_character)) throw new \Exception(lang('error_64'));
        if (!$this->CharacterBelongsToAccount($this->_character, $this->_username)) throw new \Exception(lang('error_64'));

        $pointsTotal = $this->_strength + $this->_agility + $this->_vitality + $this->_energy + $this->_command;
        if ($pointsTotal < mconfig('minimum_limit')) throw new \Exception(langf('error_54', [mconfig('minimum_limit')]));

        $account = new Account();
        if ($account->accountOnline($this->_username)) throw new \Exception(lang('error_14'));

        $characterData = $this->CharacterData($this->_character);
        if ($characterData[_CLMN_CHR_LVLUP_POINT_] < $pointsTotal) throw new \Exception(lang('error_51'));

        $str = $characterData[_CLMN_CHR_STAT_STR_] + $this->_strength;
        $agi = $characterData[_CLMN_CHR_STAT_AGI_] + $this->_agility;
        $vit = $characterData[_CLMN_CHR_STAT_VIT_] + $this->_vitality;
        $ene = $characterData[_CLMN_CHR_STAT_ENE_] + $this->_energy;

        if ($str > mconfig('max_stats')) throw new \Exception(langf('error_53', [number_format(mconfig('max_stats'))]));
        if ($agi > mconfig('max_stats')) throw new \Exception(langf('error_53', [number_format(mconfig('max_stats'))]));
        if ($vit > mconfig('max_stats')) throw new \Exception(langf('error_53', [number_format(mconfig('max_stats'))]));
        if ($ene > mconfig('max_stats')) throw new \Exception(langf('error_53', [number_format(mconfig('max_stats'))]));

        $cmd = 0;
        if (array_key_exists(_CLMN_CHR_STAT_CMD_, $characterData) && $this->_command >= 1) {
            if (!in_array(
                $characterData[_CLMN_CHR_CLASS_],
                custom('character_cmd'),
                true
            )
            ) throw new \Exception(lang('error_52'));
            $cmd = $characterData[_CLMN_CHR_STAT_CMD_] + $this->_command;
            if ($cmd > mconfig('max_stats')) throw new \Exception(langf('error_53', [number_format(mconfig('max_stats'))]));
        }

        if ($characterData[_CLMN_CHR_LVL_] < mconfig('required_level')) throw new \Exception(lang('error_123'));

        if (mconfig('required_master_level') >= 1) {
            /** @phpstan-ignore notEqual.alwaysTrue */
            $characterMasterLvlData = _TBL_CHR_ != _TBL_MASTERLVL_ ? $this->getMasterLevelInfo($this->_character) : $characterData;
            if (!is_array($characterMasterLvlData)) throw new \Exception(lang('error_119'));
            if ($characterMasterLvlData[_CLMN_ML_LVL_] < mconfig('required_master_level')) throw new \Exception(lang('error_124'));
        }

        $zenRequirement = mconfig('zen_cost');
        if ($zenRequirement > 0 && $characterData[_CLMN_CHR_ZEN_] < $zenRequirement) throw new \Exception(lang('error_34'));

        $creditConfig = mconfig('credit_config');
        $creditCost   = mconfig('credit_cost');
        $creditSystem = null;
        if ($creditCost > 0 && $creditConfig != 0) {
            $creditSystem   = new CreditSystem();
            $creditSystem->setConfigId($creditConfig);
            $configSettings = $creditSystem->showConfigs(true);
            $this->_setCreditIdentifier($creditSystem, $configSettings['config_user_col_id']);
            if ($creditSystem->getCredits() < $creditCost) throw new \Exception(langf('error_125', [$configSettings['config_title']]));
        }

        if ($zenRequirement > 0 && !$this->DeductZEN($this->_character, $zenRequirement)) throw new \Exception(lang('error_34'));

        $data = ['str' => $str, 'agi' => $agi, 'vit' => $vit, 'ene' => $ene, 'total' => $pointsTotal, 'player' => $characterData[_CLMN_CHR_NAME_]];
        if ($cmd >= 1) $data['cmd'] = $cmd;

        $query  = "UPDATE " . _TBL_CHR_ . " SET " . _CLMN_CHR_LVLUP_POINT_ . " = " . _CLMN_CHR_LVLUP_POINT_ . " - :total, ";
        if ($cmd >= 1) $query .= _CLMN_CHR_STAT_CMD_ . " = :cmd, ";
        $query .= _CLMN_CHR_STAT_STR_ . " = :str, " . _CLMN_CHR_STAT_AGI_ . " = :agi, " . _CLMN_CHR_STAT_VIT_ . " = :vit, " . _CLMN_CHR_STAT_ENE_ . " = :ene";
        $query .= " WHERE " . _CLMN_CHR_NAME_ . " = :player";

        $result = $this->muonline->query($query, $data);
        if (!$result) throw new \Exception(lang('error_21'));

        if ($creditCost > 0 && $creditConfig != 0) $creditSystem->subtractCredits($creditCost);

        message('success', lang('success_17'));
    }

    // ─── Data retrieval ───────────────────────────────────────────────────────

    public function AccountCharacter($username)
    {
        if (!check_value($username)) return;
        if (!Validator::UsernameLength($username)) return;
        if (!Validator::AlphaNumeric($username)) return;

        $result = $this->muonline->query_fetch(
            "SELECT " . _CLMN_CHR_NAME_ . " FROM " . _TBL_CHR_ . " WHERE " . _CLMN_CHR_ACCID_ . " = ?",
            [$username]
        );
        if (!is_array($result)) return;

        $return = [];
        foreach ($result as $row) {
            if (!check_value($row[_CLMN_CHR_NAME_])) continue;
            $return[] = $row[_CLMN_CHR_NAME_];
        }

        return count($return) > 0 ? $return : null;
    }

    public function CharacterData($character_name)
    {
        if (!check_value($character_name)) return;
        $result = $this->muonline->query_fetch_single(
            "SELECT * FROM " . _TBL_CHR_ . " WHERE " . _CLMN_CHR_NAME_ . " = ?",
            [$character_name]
        );
        return is_array($result) ? $result : null;
    }

    public function CharacterBelongsToAccount($character_name, $username)
    {
        if (!check_value($character_name)) return;
        if (!check_value($username)) return;
        if (!Validator::UsernameLength($username)) return;
        if (!Validator::AlphaNumeric($username)) return;
        $characterData = $this->CharacterData($character_name);
        if (!is_array($characterData)) return;
        if (strtolower($characterData[_CLMN_CHR_ACCID_]) != strtolower($username)) return;
        return true;
    }

    public function CharacterExists($character_name)
    {
        if (!check_value($character_name)) return;
        $check = $this->muonline->query_fetch_single(
            "SELECT * FROM " . _TBL_CHR_ . " WHERE " . _CLMN_CHR_NAME_ . " = ?",
            [$character_name]
        );
        return is_array($check) ? true : null;
    }

    public function DeductZEN($character_name, $zen_amount)
    {
        if (!check_value($character_name)) return;
        if (!check_value($zen_amount)) return;
        if (!Validator::UnsignedNumber($zen_amount)) return;
        if ($zen_amount < 1) return;
        if (!$this->CharacterExists($character_name)) return;
        $characterData = $this->CharacterData($character_name);
        if (!is_array($characterData)) return;
        if ($characterData[_CLMN_CHR_ZEN_] < $zen_amount) return;
        $deduct = $this->muonline->query(
            "UPDATE " . _TBL_CHR_ . " SET " . _CLMN_CHR_ZEN_ . " = " . _CLMN_CHR_ZEN_ . " - ? WHERE " . _CLMN_CHR_NAME_ . " = ?",
            [$zen_amount, $character_name]
        );
        return $deduct ? true : null;
    }

    public function AccountCharacterIDC($username)
    {
        if (!check_value($username)) return;
        if (!Validator::UsernameLength($username)) return;
        if (!Validator::AlphaNumeric($username)) return;
        $data = $this->muonline->query_fetch_single(
            "SELECT * FROM " . _TBL_AC_ . " WHERE " . _CLMN_AC_ID_ . " = ?",
            [$username]
        );
        return is_array($data) ? $data[_CLMN_GAMEIDC_] : null;
    }

    /** @deprecated Use getPlayerClassAvatar() helper directly. */
    public function GenerateCharacterClassAvatar($code = 0, $alt = true, $img_tags = true): string
    {
        return getPlayerClassAvatar($code, $img_tags, $alt, 'tables-character-class-img');
    }

    public function getMasterLevelInfo($character_name)
    {
        if (!check_value($character_name)) return;
        if (!$this->CharacterExists($character_name)) return;
        $CharInfo = $this->muonline->query_fetch_single(
            "SELECT * FROM " . _TBL_MASTERLVL_ . " WHERE " . _CLMN_ML_NAME_ . " = ?",
            [$character_name]
        );
        return is_array($CharInfo) ? $CharInfo : null;
    }

    // ─── Protected helpers ────────────────────────────────────────────────────

    protected function _moveCharacter($character_name, int $map = 0, int $x = 125, int $y = 125)
    {
        if (!check_value($character_name)) return;
        $move = $this->muonline->query(
            "UPDATE " . _TBL_CHR_ . " SET " . _CLMN_CHR_MAP_ . " = ?, " . _CLMN_CHR_MAP_X_ . " = ?, " . _CLMN_CHR_MAP_Y_ . " = ? WHERE " . _CLMN_CHR_NAME_ . " = ?",
            [$map, $x, $y, $character_name]
        );
        return $move ? true : null;
    }

    protected function _resetMagicList($character): ?true
    {
        $result = $this->muonline->query(
            "UPDATE " . _TBL_CHR_ . " SET " . _CLMN_CHR_MAGIC_L_ . " = null WHERE " . _CLMN_CHR_NAME_ . " = ?",
            [$character]
        );
        return $result ? true : null;
    }

    protected function _getClassBaseStats($class): array
    {
        if (!array_key_exists($class, $this->_classData)) throw new \Exception(lang('error_109'));
        if (!array_key_exists('base_stats', $this->_classData[$class])) throw new \Exception(lang('error_110'));
        if (!is_array($this->_classData[$class]['base_stats'])) throw new \Exception(lang('error_110'));
        return $this->_classData[$class]['base_stats'];
    }

    /**
     * Sets the credit system identifier based on the config_user_col_id value.
     * Avoids repeating the switch block in every action method.
     */
    private function _setCreditIdentifier(CreditSystem $creditSystem, string $colId): void
    {
        switch ($colId) {
            case 'userid':    $creditSystem->setIdentifier($this->_userid);    break;
            case 'username':  $creditSystem->setIdentifier($this->_username);  break;
            case 'character': $creditSystem->setIdentifier($this->_character); break;
            default:          throw new \Exception('Invalid identifier (credit system).');
        }
    }
}

