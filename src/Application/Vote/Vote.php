<?php

declare(strict_types=1);

namespace Darkheim\Application\Vote;

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Application\Language\Translator;
use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Database\Connection;

/**
 * Vote — user voting, cooldown checks, credit rewards, logging.
 */
class Vote
{
    private $_userid;
    private $_username;
    private $_votesideId;
    private $_ip;

    private string $_configXml = 'vote.xml';
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
            file_get_contents(__PATH_MODULE_CONFIGS_USERCP__ . $this->_configXml),
        );
        if (! $this->xml) {
            throw new \Exception(Translator::phrase('error_100'));
        }

        $xmlConfig           = self::xmlToArray($this->xml);
        $this->_active       = (bool) $xmlConfig['active'];
        $this->_saveLogs     = (bool) $xmlConfig['vote_save_logs'];
        $this->_creditConfig = $xmlConfig['credit_config'];
    }

    public function setUserid($userid): void
    {
        if (! Validator::hasValue($userid)) {
            throw new \Exception(Translator::phrase('error_23'));
        }
        if (! Validator::UnsignedNumber($userid)) {
            throw new \Exception(Translator::phrase('error_23'));
        }

        $accountInfo = $this->common->accountInformation($userid);
        if (! is_array($accountInfo)) {
            throw new \Exception(Translator::phrase('error_23'));
        }

        $_accountInfo    = $accountInfo;
        $this->_userid   = $userid;
        $this->_username = $_accountInfo[_CLMN_USERNM_];
    }

    public function setVotesiteId($votesiteid): void
    {
        if (! Validator::hasValue($votesiteid)) {
            throw new \Exception(Translator::phrase('error_23'));
        }
        if (! Validator::UnsignedNumber($votesiteid)) {
            throw new \Exception(Translator::phrase('error_23'));
        }
        if (! $this->_siteExists($votesiteid)) {
            throw new \Exception(Translator::phrase('error_23'));
        }
        $this->_votesideId = $votesiteid;
    }

    public function setIp($ip): void
    {
        if (! Validator::hasValue($ip)) {
            throw new \Exception(Translator::phrase('error_101'));
        }
        if (! Validator::Ip($ip)) {
            throw new \Exception(Translator::phrase('error_101'));
        }
        $this->_ip = $ip;
    }

    public function vote(): void
    {
        if (! Validator::hasValue($this->_userid)) {
            throw new \Exception(Translator::phrase('error_23'));
        }
        if (! Validator::hasValue($this->_ip)) {
            throw new \Exception(Translator::phrase('error_23'));
        }
        if (! Validator::hasValue($this->_votesideId)) {
            throw new \Exception(Translator::phrase('error_23'));
        }
        if (! $this->_active) {
            throw new \Exception(Translator::phrase('error_47'));
        }
        if ($this->_creditConfig == 0) {
            throw new \Exception(Translator::phrase('error_102'));
        }
        if (! $this->_canUserVote()) {
            throw new \Exception(Translator::phrase('error_15'));
        }
        if (! $this->_canIPVote()) {
            throw new \Exception(Translator::phrase('error_16'));
        }

        $voteSite = $this->retrieveVotesite($this->_votesideId);
        if (! is_array($voteSite)) {
            throw new \Exception(Translator::phrase('error_23'));
        }

        $creditsReward = $voteSite['votesite_reward'];

        $creditSystem = new CreditSystem();
        $creditSystem->setConfigId($this->_creditConfig);
        $configSettings = $creditSystem->showConfigs(true);

        switch ($configSettings['config_user_col_id']) {
            case 'userid':   $creditSystem->setIdentifier($this->_userid);
                break;
            case 'username': $creditSystem->setIdentifier($this->_username);
                break;
            default:         throw new \Exception(Translator::phrase('error_73'));
        }

        $creditSystem->addCredits($creditsReward);
        $this->_addRecord();
        if ($this->_saveLogs) {
            $this->_logVote();
        }

        \Darkheim\Infrastructure\Http\Redirector::go(3, $voteSite['votesite_link']);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function _canUserVote(): bool
    {
        if (! Validator::hasValue($this->_userid)) {
            throw new \Exception(Translator::phrase('error_23'));
        }
        if (! Validator::hasValue($this->_votesideId)) {
            throw new \Exception(Translator::phrase('error_23'));
        }

        $check = $this->muonline->query_fetch_single(
            "SELECT * FROM " . Votes . " WHERE user_id = ? AND vote_site_id = ?",
            [$this->_userid, $this->_votesideId],
        );

        if (! is_array($check)) {
            return true;
        }
        return (bool) ($this->_timePassed($check['timestamp'])
            && $this->_removeRecord(
                $check['id'],
            )
        )
        ;
    }

    private function _canIPVote(): bool
    {
        if (! Validator::hasValue($this->_ip)) {
            throw new \Exception(Translator::phrase('error_23'));
        }
        if (! Validator::hasValue($this->_votesideId)) {
            throw new \Exception(Translator::phrase('error_23'));
        }

        $check = $this->muonline->query_fetch_single(
            "SELECT * FROM " . Votes . " WHERE user_ip = ? AND vote_site_id = ?",
            [$this->_ip, $this->_votesideId],
        );

        if (! is_array($check)) {
            return true;
        }
        return (bool) ($this->_timePassed($check['timestamp'])
            && $this->_removeRecord(
                $check['id'],
            )
        )
        ;
    }

    private function _addRecord(): void
    {
        if (! Validator::hasValue($this->_userid)) {
            throw new \Exception(Translator::phrase('error_23'));
        }
        if (! Validator::hasValue($this->_ip)) {
            throw new \Exception(Translator::phrase('error_23'));
        }
        if (! Validator::hasValue($this->_votesideId)) {
            throw new \Exception(Translator::phrase('error_23'));
        }

        $voteSiteInfo = $this->retrieveVotesite($this->_votesideId);
        if (! is_array($voteSiteInfo)) {
            throw new \Exception(Translator::phrase('error_23'));
        }

        $timestamp = time() + $voteSiteInfo['votesite_time'] * 60 * 60;
        $add       = $this->muonline->query(
            "INSERT INTO " . Votes . " (user_id, user_ip, vote_site_id, timestamp) VALUES (?, ?, ?, ?)",
            [$this->_userid, $this->_ip, $this->_votesideId, $timestamp],
        );
        if (! $add) {
            throw new \Exception(Translator::phrase('error_23'));
        }
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
        if (! Validator::hasValue($id)) {
            return false;
        }
        $check = $this->muonline->query_fetch_single("SELECT * FROM " . Vote_Sites . " WHERE votesite_id = ?", [$id]);
        return is_array($check);
    }

    private function _logVote(): void
    {
        if (! Validator::hasValue($this->_userid)) {
            throw new \Exception(Translator::phrase('error_23'));
        }
        if (! Validator::hasValue($this->_votesideId)) {
            throw new \Exception(Translator::phrase('error_23'));
        }

        $this->muonline->query(
            "INSERT INTO " . Vote_Logs . " (user_id, votesite_id, timestamp) VALUES (?, ?, ?)",
            [$this->_userid, $this->_votesideId, time()],
        );
    }

    /** Retrieve a single votesite row by ID — used internally. */
    private function retrieveVotesite($id): ?array
    {
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . Vote_Sites . " WHERE votesite_id = ?", [$id]);
        return is_array($result) ? $result : null;
    }

    /** @return array<string, mixed> */
    private static function xmlToArray(\SimpleXMLElement $object): array
    {
        return json_decode(json_encode($object, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }
}
