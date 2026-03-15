<?php

declare(strict_types=1);

namespace Darkheim\Application\Vote;

use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Application\Auth\Common;
use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Domain\Validator;

/**
 * Vote — user voting, cooldown checks, credit rewards, logging.
 */
class Vote
{
    private $_userid;
    private $_username;
    private $_votesideId;
    private $_ip;

    private string $_configXml    = 'usercp.vote.xml';
    private bool   $_active;
    private bool   $_saveLogs;
    private $_creditConfig;

    protected Common $common;
    protected $muonline;
    protected \SimpleXMLElement|false $xml = false;

    public function __construct()
    {
        $this->common   = new Common();
        $this->muonline = Connection::Database('MuOnline');

        $this->xml = simplexml_load_string(
            file_get_contents(__PATH_MODULE_CONFIGS__ . $this->_configXml)
        );
        if (!$this->xml) throw new \Exception(lang('error_100'));

        $xmlConfig          = convertXML($this->xml);
        $this->_active      = (bool) $xmlConfig['active'];
        $this->_saveLogs    = (bool) $xmlConfig['vote_save_logs'];
        $this->_creditConfig = $xmlConfig['credit_config'];
    }

    public function setUserid($userid): void
    {
        if (!check_value($userid)) throw new \Exception(lang('error_23', true));
        if (!Validator::UnsignedNumber($userid)) throw new \Exception(lang('error_23', true));

        $accountInfo = $this->common->accountInformation($userid);
        if (!is_array($accountInfo)) throw new \Exception(lang('error_23', true));

        $_accountInfo = $accountInfo;
        $this->_userid      = $userid;
        $this->_username    = $_accountInfo[_CLMN_USERNM_];
    }

    public function setVotesiteId($votesiteid): void
    {
        if (!check_value($votesiteid)) throw new \Exception(lang('error_23', true));
        if (!Validator::UnsignedNumber($votesiteid)) throw new \Exception(lang('error_23', true));
        if (!$this->_siteExists($votesiteid)) throw new \Exception(lang('error_23', true));
        $this->_votesideId = $votesiteid;
    }

    public function setIp($ip): void
    {
        if (!check_value($ip)) throw new \Exception(lang('error_101'));
        if (!Validator::Ip($ip)) throw new \Exception(lang('error_101'));
        $this->_ip = $ip;
    }

    public function vote(): void
    {
        if (!check_value($this->_userid))      throw new \Exception(lang('error_23', true));
        if (!check_value($this->_ip))          throw new \Exception(lang('error_23', true));
        if (!check_value($this->_votesideId))  throw new \Exception(lang('error_23', true));
        if (!$this->_active)                   throw new \Exception(lang('error_47', true));
        if ($this->_creditConfig == 0)         throw new \Exception(lang('error_102'));
        if (!$this->_canUserVote())            throw new \Exception(lang('error_15', true));
        if (!$this->_canIPVote())              throw new \Exception(lang('error_16', true));

        $voteSite = $this->retrieveVotesite($this->_votesideId);
        if (!is_array($voteSite)) throw new \Exception(lang('error_23', true));

        $creditsReward = $voteSite['votesite_reward'];

        $creditSystem = new CreditSystem();
        $creditSystem->setConfigId($this->_creditConfig);
        $configSettings = $creditSystem->showConfigs(true);

        switch ($configSettings['config_user_col_id']) {
            case 'userid':   $creditSystem->setIdentifier($this->_userid);   break;
            case 'username': $creditSystem->setIdentifier($this->_username); break;
            default:         throw new \Exception(lang('error_73'));
        }

        $creditSystem->addCredits($creditsReward);
        $this->_addRecord();
        if ($this->_saveLogs) $this->_logVote();

        redirect(3, $voteSite['votesite_link']);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function _canUserVote(): bool
    {
        if (!check_value($this->_userid))     throw new \Exception(lang('error_23', true));
        if (!check_value($this->_votesideId)) throw new \Exception(lang('error_23', true));

        $check = $this->muonline->query_fetch_single(
            "SELECT * FROM " . Votes . " WHERE user_id = ? AND vote_site_id = ?",
            [$this->_userid, $this->_votesideId]
        );

        if (!is_array($check)) return true;
        if ($this->_timePassed($check['timestamp'])
            && $this->_removeRecord(
                $check['id']
            )
        ) return true;
        return false;
    }

    private function _canIPVote(): bool
    {
        if (!check_value($this->_ip))         throw new \Exception(lang('error_23', true));
        if (!check_value($this->_votesideId)) throw new \Exception(lang('error_23', true));

        $check = $this->muonline->query_fetch_single(
            "SELECT * FROM " . Votes . " WHERE user_ip = ? AND vote_site_id = ?",
            [$this->_ip, $this->_votesideId]
        );

        if (!is_array($check)) return true;
        if ($this->_timePassed($check['timestamp'])
            && $this->_removeRecord(
                $check['id']
            )
        ) return true;
        return false;
    }

    private function _addRecord(): void
    {
        if (!check_value($this->_userid))     throw new \Exception(lang('error_23', true));
        if (!check_value($this->_ip))         throw new \Exception(lang('error_23', true));
        if (!check_value($this->_votesideId)) throw new \Exception(lang('error_23', true));

        $voteSiteInfo = $this->retrieveVotesite($this->_votesideId);
        if (!is_array($voteSiteInfo)) throw new \Exception(lang('error_23', true));

        $timestamp = time() + $voteSiteInfo['votesite_time'] * 60 * 60;
        $add = $this->muonline->query(
            "INSERT INTO " . Votes . " (user_id, user_ip, vote_site_id, timestamp) VALUES (?, ?, ?, ?)",
            [$this->_userid, $this->_ip, $this->_votesideId, $timestamp]
        );
        if (!$add) throw new \Exception(lang('error_23', true));
    }

    private function _removeRecord($id): bool
    {
        return $this->muonline->query("DELETE FROM " . Votes . " WHERE id = ?", [$id]);
    }

    private function _timePassed($timestamp): bool
    {
        return time() > $timestamp;
    }

    private function _siteExists($id): bool
    {
        if (!check_value($id)) return false;
        $check = $this->muonline->query_fetch_single("SELECT * FROM " . Vote_Sites . " WHERE votesite_id = ?", [$id]);
        return is_array($check);
    }

    private function _logVote(): void
    {
        if (!check_value($this->_userid))     throw new \Exception(lang('error_23', true));
        if (!check_value($this->_votesideId)) throw new \Exception(lang('error_23', true));

        $this->muonline->query(
            "INSERT INTO " . Vote_Logs . " (user_id, votesite_id, timestamp) VALUES (?, ?, ?)",
            [$this->_userid, $this->_votesideId, time()]
        );
    }

    /** Retrieve a single votesite row by ID — used internally. */
    private function retrieveVotesite($id): ?array
    {
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . Vote_Sites . " WHERE votesite_id = ?", [$id]);
        return is_array($result) ? $result : null;
    }
}

