<?php

declare(strict_types=1);

namespace Darkheim\Application\Auth;

use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\Runtime\ServerContext;

/**
 * Login — credential validation, brute-force throttle, session init.
 */
class Login
{
    private $_config;
    private ?SessionManager $sessionManager = null;
    private ?ServerContext $serverContext   = null;

    protected $common;
    protected $muonline;

    public function __construct(?SessionManager $sessionManager = null, ?ServerContext $serverContext = null)
    {
        $this->common         = new Common();
        $this->muonline       = Connection::Database('MuOnline');
        $this->sessionManager = $sessionManager ?? new SessionManager();
        $this->serverContext  = $serverContext  ?? new ServerContext();
        $loginConfigs         = BootstrapContext::configProvider()?->moduleConfig('login');
        if (! is_array($loginConfigs)) {
            throw new \Exception(\Darkheim\Application\Language\Translator::phrase('error_98'));
        }
        $this->_config = $loginConfigs;
    }

    public function validateLogin($username, $password): void
    {
        $ipAddress = $this->server()->remoteAddress() ?? '';

        if (! Validator::hasValue($username)) {
            throw new \Exception(\Darkheim\Application\Language\Translator::phrase('error_4', true));
        }
        if (! Validator::hasValue($password)) {
            throw new \Exception(\Darkheim\Application\Language\Translator::phrase('error_4', true));
        }
        if (! $this->canLogin($ipAddress)) {
            throw new \Exception(\Darkheim\Application\Language\Translator::phrase('error_3', true));
        }
        if (! $this->common->userExists($username)) {
            throw new \Exception(\Darkheim\Application\Language\Translator::phrase('error_2', true));
        }

        if ($this->common->validateUser($username, $password)) {
            $userId = $this->common->retrieveUserID($username);
            if (! Validator::hasValue($userId)) {
                throw new \Exception(\Darkheim\Application\Language\Translator::phrase('error_12', true));
            }

            $accountData = $this->common->accountInformation($userId);
            if (! is_array($accountData)) {
                throw new \Exception(\Darkheim\Application\Language\Translator::phrase('error_12', true));
            }

            $this->removeFailedLogins($ipAddress);
            session_regenerate_id();
            $this->session()->startAuthenticatedSession($userId, (string) $accountData[_CLMN_USERNM_]);

            \Darkheim\Infrastructure\Http\Redirector::go(1, 'usercp/');
        } else {
            $this->addFailedLogin($username, $ipAddress);
            \Darkheim\Application\View\MessageRenderer::toast('error', \Darkheim\Application\Language\Translator::phrase('error_1', true));
            \Darkheim\Application\View\MessageRenderer::toast('warning', \Darkheim\Application\Language\Translator::phraseFmt('login_txt_5', [$this->checkFailedLogins($ipAddress), \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('max_login_attempts'), \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('max_login_attempts')]));
        }
    }

    public function canLogin($ipaddress)
    {
        if (! Validator::Ip($ipaddress)) {
            return;
        }
        $failedLogins = $this->checkFailedLogins($ipaddress);
        if ($failedLogins < $this->_config['max_login_attempts']) {
            return true;
        }

        $result = $this->muonline->query_fetch_single("SELECT * FROM " . FLA . " WHERE ip_address = ? ORDER BY id DESC", [$ipaddress]);
        if (! is_array($result)) {
            return true;
        }
        if (time() < $result['unlock_timestamp']) {
            return;
        }

        $this->removeFailedLogins($ipaddress);
        return true;
    }

    public function checkFailedLogins($ipaddress)
    {
        if (! Validator::Ip($ipaddress)) {
            return;
        }
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . FLA . " WHERE ip_address = ? ORDER BY id DESC", [$ipaddress]);
        if (! is_array($result)) {
            return;
        }
        return $result['failed_attempts'];
    }

    public function addFailedLogin($username, $ipaddress): void
    {
        if (! Validator::UsernameLength($username)) {
            return;
        }
        if (! Validator::AlphaNumeric($username)) {
            return;
        }
        if (! Validator::Ip($ipaddress)) {
            return;
        }
        if (! $this->common->userExists($username)) {
            return;
        }

        $failedLogins = $this->checkFailedLogins($ipaddress);
        $timeout      = time() + $this->_config['failed_login_timeout'] * 60;

        if ($failedLogins >= 1) {
            if (($failedLogins + 1) >= $this->_config['max_login_attempts']) {
                $this->muonline->query("UPDATE " . FLA . " SET username = ?, ip_address = ?, failed_attempts = failed_attempts + 1, unlock_timestamp = ?, timestamp = ? WHERE ip_address = ?", [$username, $ipaddress, $timeout, time(), $ipaddress]);
            } else {
                $this->muonline->query("UPDATE " . FLA . " SET username = ?, ip_address = ?, failed_attempts = failed_attempts + 1, timestamp = ? WHERE ip_address = ?", [$username, $ipaddress, time(), $ipaddress]);
            }
        } else {
            $this->muonline->query("INSERT INTO " . FLA . " (username, ip_address, unlock_timestamp, failed_attempts, timestamp) VALUES (?, ?, ?, ?, ?)", [$username, $ipaddress, 0, 1, time()]);
        }
    }

    public function removeFailedLogins($ipaddress): void
    {
        if (! Validator::Ip($ipaddress)) {
            return;
        }
        $this->muonline->query("DELETE FROM " . FLA . " WHERE ip_address = ?", [$ipaddress]);
    }

    public function logout(): void
    {
        $this->session()->clearSession();
        \Darkheim\Infrastructure\Http\Redirector::go();
    }

    private function session(): SessionManager
    {
        if (! $this->sessionManager instanceof SessionManager) {
            $this->sessionManager = new SessionManager();
        }

        return $this->sessionManager;
    }

    private function server(): ServerContext
    {
        if (! $this->serverContext instanceof ServerContext) {
            $this->serverContext = new ServerContext();
        }

        return $this->serverContext;
    }
}
