<?php

declare(strict_types=1);

namespace Darkheim\Application\Account;

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Auth\Login;
use Darkheim\Infrastructure\Email\Email;
use Darkheim\Infrastructure\Runtime\ServerContext;
use Darkheim\Domain\Validator;

/**
 * Account — registration, verification, password recovery, email change.
 */
class Account extends Common
{
    private string $_defaultAccountSerial = '1111111111111';
    private ?ServerContext $serverContext = null;

    public $_account {
        set {
            $this->_account = $value;
        }
    }
    public $_country {
        set {
            $this->_country = $value;
        }
    }

    public function __construct(?ServerContext $serverContext = null)
    {
        parent::__construct();
        $this->serverContext = $serverContext ?? new ServerContext();
    }

    public function registerAccount($username, $password, $cpassword, $email): void
    {
        if (!check_value($username)) throw new \Exception(lang('error_4', true));
        if (!check_value($password)) throw new \Exception(lang('error_4', true));
        if (!check_value($cpassword)) throw new \Exception(lang('error_4', true));
        if (!check_value($email)) throw new \Exception(lang('error_4', true));

        if (!Validator::UsernameLength($username)) throw new \Exception(lang('error_5', true));
        if (!Validator::AlphaNumeric($username)) throw new \Exception(lang('error_6', true));
        if (!Validator::PasswordLength($password)) throw new \Exception(lang('error_7', true));
        if ($password != $cpassword) throw new \Exception(lang('error_8', true));
        if (!Validator::Email($email)) throw new \Exception(lang('error_9', true));

        $regCfg = loadConfigurations('register');

        if ($this->userExists($username)) throw new \Exception(lang('error_10', true));
        if ($this->emailExists($email)) throw new \Exception(lang('error_11', true));

        if ($regCfg['verify_email'] ?? false) {
            if ($this->checkUsernameEVS($username)) throw new \Exception(lang('error_10', true));
            if ($this->checkEmailEVS($email)) throw new \Exception(lang('error_11', true));
            $verificationKey = $this->createRegistrationVerification($username, $password, $email);
            if (!check_value($verificationKey)) throw new \Exception(lang('error_23', true));
            $this->sendRegistrationVerificationEmail($username, $email, $verificationKey);
            message('success', lang('success_18', true));
            return;
        }

        $data  = ['username' => $username, 'password' => $password, 'name' => $username, 'serial' => $this->_defaultAccountSerial, 'email' => $email];
        switch ($this->_passwordEncryption) {
            case 'wzmd5':
                // noinspection SqlNoDataSourceInspection — fn_md5 is a MSSQL server-side stored function
                $query = /** @lang TSQL */ "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, [dbo].[fn_md5](:password, :username), :name, :serial, :email, 0, 0)";
                break;
            case 'phpmd5':
                $data['password'] = md5($password);
                $query = "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, :password, :name, :serial, :email, 0, 0)";
                break;
            case 'sha256':
                $data['password'] = '0x' . hash('sha256', $password . $username . $this->_sha256salt);
                $query = "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, CONVERT(binary(32),:password,1), :name, :serial, :email, 0, 0)";
                break;
            default:
                $query = "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, :password, :name, :serial, :email, 0, 0)";
        }

        $result = $this->muonline->query($query, $data);
        if (!$result) throw new \Exception(lang('error_22', true));

        if (config('season_1_support')) {
            // noinspection SqlNoDataSourceInspection — VI_CURR_INFO is a Season 1 game table
            $this->muonline->query(/** @lang TSQL */ "INSERT INTO VI_CURR_INFO (ends_days, chek_code, used_time, memb___id, memb_name, memb_guid, sno__numb, Bill_Section, Bill_Value, Bill_Hour, Surplus_Point, Surplus_Minute, Increase_Days) VALUES ('2005', '1', '1234', ?, '', '1', '7', '6', '3', '6', '6', '2020-01-01 00:00:00', '0')", array($username));
        }

        if ($regCfg['send_welcome_email'] ?? false) {
            $this->sendWelcomeEmail($username, $email);
        }

        message('success', lang('success_1', true));

        if ($regCfg['automatic_login'] ?? false) {
            try {
                $userLogin = new Login();
                $userLogin->validateLogin($username, $password);
            } catch (\Exception $ex) {
                redirect(1, 'login/');
            }
        } else {
            redirect(2, 'login/', 5);
        }
    }

    public function changePasswordProcess($userid, $username, $password, $new_password, $confirm_new_password): void
    {
        if (!check_value($userid)) throw new \Exception(lang('error_4', true));
        if (!check_value($username)) throw new \Exception(lang('error_4', true));
        if (!check_value($password)) throw new \Exception(lang('error_4', true));
        if (!check_value($new_password)) throw new \Exception(lang('error_4', true));
        if (!check_value($confirm_new_password)) throw new \Exception(lang('error_4', true));
        if (!Validator::PasswordLength($new_password)) throw new \Exception(lang('error_7', true));
        if ($new_password != $confirm_new_password) throw new \Exception(lang('error_8', true));
        if (!$this->validateUser($username, $password)) throw new \Exception(lang('error_13', true));
        if ($this->accountOnline($username)) throw new \Exception(lang('error_14', true));
        if (!$this->changePassword($userid, $username, $new_password)) throw new \Exception(lang('error_23', true));

        $accountData = $this->accountInformation($userid);
        try {
            $email = new Email();
            $email->setTemplate('CHANGE_PASSWORD');
            $email->addVariable('{USERNAME}', $username);
            $email->addVariable('{NEW_PASSWORD}', $new_password);
            $email->addAddress($accountData[_CLMN_EMAIL_]);
            $email->send();
        } catch (\Exception $ex) {}

        message('success', lang('success_2', true));
    }

    public function changePasswordProcess_verifyEmail($userid, $username, $password, $new_password, $confirm_new_password, $ip_address): void
    {
        if (!check_value($userid)) throw new \Exception(lang('error_4', true));
        if (!check_value($username)) throw new \Exception(lang('error_4', true));
        if (!check_value($password)) throw new \Exception(lang('error_4', true));
        if (!check_value($new_password)) throw new \Exception(lang('error_4', true));
        if (!check_value($confirm_new_password)) throw new \Exception(lang('error_4', true));
        if (!Validator::PasswordLength($new_password)) throw new \Exception(lang('error_7', true));
        if ($new_password != $confirm_new_password) throw new \Exception(lang('error_8', true));

        $mypassCfg = loadConfigurations('my-password');
        if (!$this->validateUser($username, $password)) throw new \Exception(lang('error_13', true));
        if ($this->accountOnline($username)) throw new \Exception(lang('error_14', true));
        if ($this->hasActivePasswordChangeRequest($userid)) throw new \Exception(lang('error_19', true));

        $accountData = $this->accountInformation($userid);
        if (!is_array($accountData)) throw new \Exception(lang('error_21', true));

        $auth_code = random_int(111111, 999999);
        $link      = $this->generatePasswordChangeVerificationURL($userid, $auth_code);

        if (!$this->addPasswordChangeRequest($userid, $new_password, $auth_code)) throw new \Exception(lang('error_21', true));

        try {
            $email = new Email();
            $email->setTemplate('CHANGE_PASSWORD_EMAIL_VERIFICATION');
            $email->addVariable('{USERNAME}', $username);
            $email->addVariable('{DATE}', date("m/d/Y @ h:i a"));
            $email->addVariable('{IP_ADDRESS}', $ip_address);
            $email->addVariable('{LINK}', $link);
            $email->addVariable('{EXPIRATION_TIME}', $mypassCfg['change_password_request_timeout']);
            $email->addAddress($accountData[_CLMN_EMAIL_]);
            $email->send();
            message('success', lang('success_3', true));
        } catch (\Exception $ex) {
            if ($this->_debug) throw new \Exception($ex->getMessage());
            throw new \Exception(lang('error_20', true));
        }
    }

    public function changePasswordVerificationProcess($user_id, $auth_code): void
    {
        if (!check_value($user_id)) throw new \Exception(lang('error_24', true));
        if (!check_value($auth_code)) throw new \Exception(lang('error_24', true));
        if (!Validator::UnsignedNumber($user_id)) throw new \Exception(lang('error_25', true));
        if (!Validator::UnsignedNumber($auth_code)) throw new \Exception(lang('error_25', true));

        $result = $this->muonline->query_fetch_single("SELECT * FROM " . Passchange_Request . " WHERE user_id = ?", array($user_id));
        if (!is_array($result)) throw new \Exception(lang('error_25', true));

        $mypassCfg       = loadConfigurations('my-password');
        $request_timeout = $mypassCfg['change_password_request_timeout'] * 3600;
        $request_date    = $result['request_date'] + $request_timeout;

        if ($request_date < time()) throw new \Exception(lang('error_26', true));
        if ($result['auth_code'] != $auth_code) throw new \Exception(lang('error_27', true));

        $accountData  = $this->accountInformation($user_id);
        $username     = $accountData[_CLMN_USERNM_];
        $new_password = $result['new_password'];

        if ($this->accountOnline($username)) throw new \Exception(lang('error_14', true));
        if (!$this->changePassword($user_id, $username, $new_password)) throw new \Exception(lang('error_29', true));

        try {
            $email = new Email();
            $email->setTemplate('CHANGE_PASSWORD');
            $email->addVariable('{USERNAME}', $username);
            $email->addVariable('{NEW_PASSWORD}', $new_password);
            $email->addAddress($accountData[_CLMN_EMAIL_]);
            $email->send();
        } catch (\Exception $ex) {
            if ($this->_debug) throw new \Exception($ex->getMessage());
        }

        $this->removePasswordChangeRequest($user_id);
        message('success', lang('success_5', true));
    }

    public function passwordRecoveryProcess($user_email, $ip_address): void
    {
        if (!check_value($user_email)) throw new \Exception(lang('error_30', true));
        if (!check_value($ip_address)) throw new \Exception(lang('error_30', true));
        if (!Validator::Email($user_email)) throw new \Exception(lang('error_30', true));
        if (!Validator::Ip($ip_address)) throw new \Exception(lang('error_30', true));
        if (!$this->emailExists($user_email)) throw new \Exception(lang('error_30', true));

        $user_id     = $this->retrieveUserIDbyEmail($user_email);
        if (!check_value($user_id)) throw new \Exception(lang('error_23', true));
        $accountData = $this->accountInformation($user_id);
        if (!is_array($accountData)) throw new \Exception(lang('error_23', true));

        $arc = $this->generateAccountRecoveryCode($accountData[_CLMN_MEMBID_], $accountData[_CLMN_USERNM_]);
        $aru = $this->_generateAccountRecoveryLink($accountData[_CLMN_MEMBID_], $accountData[_CLMN_EMAIL_], $arc);

        try {
            $email = new Email();
            $email->setTemplate('PASSWORD_RECOVERY_REQUEST');
            $email->addVariable('{USERNAME}', $accountData[_CLMN_USERNM_]);
            $email->addVariable('{DATE}', date("Y-m-d @ h:i a"));
            $email->addVariable('{IP_ADDRESS}', $ip_address);
            $email->addVariable('{LINK}', $aru);
            $email->addAddress($accountData[_CLMN_EMAIL_]);
            $email->send();
            message('success', lang('success_6', true));
        } catch (\Exception $ex) {
            if ($this->_debug) throw new \Exception($ex->getMessage());
            throw new \Exception(lang('error_23', true));
        }
    }

    public function passwordRecoveryVerificationProcess($ui, $ue, $key): void
    {
        if (!check_value($ui)) throw new \Exception(lang('error_31', true));
        if (!check_value($ue)) throw new \Exception(lang('error_31', true));
        if (!check_value($key)) throw new \Exception(lang('error_31', true));
        if (!Validator::UnsignedNumber($ui)) throw new \Exception(lang('error_31', true));
        if (!$this->emailExists($ue)) throw new \Exception(lang('error_31', true));

        $accountData = $this->accountInformation($ui);
        if (!is_array($accountData)) throw new \Exception(lang('error_31', true));

        $gen_key = $this->generateAccountRecoveryCode($ui, $accountData[_CLMN_USERNM_]);
        if ($key != $gen_key) throw new \Exception(lang('error_31', true));

        $new_password = random_int(11111111, 99999999);
        if (!$this->changePassword($ui, $accountData[_CLMN_USERNM_], $new_password)) throw new \Exception(lang('error_23', true));

        try {
            $email = new Email();
            $email->setTemplate('PASSWORD_RECOVERY_COMPLETED');
            $email->addVariable('{USERNAME}', $accountData[_CLMN_USERNM_]);
            $email->addVariable('{NEW_PASSWORD}', $new_password);
            $email->addAddress($accountData[_CLMN_EMAIL_]);
            $email->send();
            message('success', lang('success_7', true));
        } catch (\Exception $ex) {
            if ($this->_debug) throw new \Exception($ex->getMessage());
            throw new \Exception(lang('error_23', true));
        }
    }

    public function changeEmailAddress($accountId, $newEmail, $ipAddress): void
    {
        if (!check_value($accountId)) throw new \Exception(lang('error_21', true));
        if (!check_value($newEmail)) throw new \Exception(lang('error_21', true));
        if (!check_value($ipAddress)) throw new \Exception(lang('error_21', true));
        if (!Validator::Ip($ipAddress)) throw new \Exception(lang('error_21', true));
        if (!Validator::Email($newEmail)) throw new \Exception(lang('error_21', true));
        if ($this->emailExists($newEmail)) throw new \Exception(lang('error_11', true));

        $accountInfo = $this->accountInformation($accountId);
        if (!is_array($accountInfo)) throw new \Exception(lang('error_21', true));

        $myemailCfg = loadConfigurations('my-email');
        if ($myemailCfg['require_verification']) {
            $userName         = $accountInfo[_CLMN_USERNM_];
            $userEmail        = $accountInfo[_CLMN_EMAIL_];
            $requestDate      = (int) strtotime(date('m/d/Y 23:59'));
            $key              = md5(md5($userName) . md5($userEmail) . md5((string) $requestDate) . md5($newEmail));
            $verificationLink = __BASE_URL__ . 'verifyemail/?op=3&uid=' . $accountId . '&email=' . $newEmail . '&key=' . $key;
            if (!$this->_changeEmailVerificationMail($userName, $userEmail, $newEmail, $verificationLink, $ipAddress)) throw new \Exception(lang('error_21', true));
        } elseif (!$this->updateEmail($accountId, $newEmail)) throw new \Exception(lang('error_21', true));
    }

    public function changeEmailVerificationProcess($encodedId, $newEmail, $encryptedKey): void
    {
        if (!Validator::UnsignedNumber($encodedId)) throw new \Exception(lang('error_21', true));
        if (!Validator::Email($newEmail)) throw new \Exception(lang('error_21', true));
        if ($this->emailExists($newEmail)) throw new \Exception(lang('error_11', true));

        $accountInfo = $this->accountInformation($encodedId);
        if (!is_array($accountInfo)) throw new \Exception(lang('error_21', true));

        $requestDate = (int) strtotime(date('m/d/Y 23:59'));
        $key         = md5(md5($accountInfo[_CLMN_USERNM_]) . md5($accountInfo[_CLMN_EMAIL_]) . md5((string) $requestDate) . md5($newEmail));
        if ($key != $encryptedKey) throw new \Exception(lang('error_21', true));
        if (!$this->updateEmail($encodedId, $newEmail)) throw new \Exception(lang('error_21', true));
    }

    public function verifyRegistrationProcess($username, $key): void
    {
        $verifyKey = $this->muonline->query_fetch_single("SELECT * FROM " . Register_Account . " WHERE registration_account = ? AND registration_key = ?", array($username, $key));
        if (!is_array($verifyKey)) throw new \Exception(lang('error_25', true));

        $regCfg = loadConfigurations('register');
        $data   = ['username' => $verifyKey['registration_account'], 'password' => $verifyKey['registration_password'], 'name' => $verifyKey['registration_account'], 'serial' => $this->_defaultAccountSerial, 'email' => $verifyKey['registration_email']];

        switch ($this->_passwordEncryption) {
            case 'wzmd5':
                // noinspection SqlNoDataSourceInspection — fn_md5 is a MSSQL server-side stored function
                $query = /** @lang TSQL */ "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, [dbo].[fn_md5](:password, :username), :name, :serial, :email, 0, 0)";
                break;
            case 'phpmd5':
                $data['password'] = md5($verifyKey['registration_password']);
                $query = "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, :password, :name, :serial, :email, 0, 0)";
                break;
            case 'sha256':
                $data['password'] = '0x' . hash('sha256', $verifyKey['registration_password'] . $verifyKey['registration_account'] . $this->_sha256salt);
                $query = "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, CONVERT(binary(32),:password,1), :name, :serial, :email, 0, 0)";
                break;
            default:
                $query = "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, :password, :name, :serial, :email, 0, 0)";
        }

        $result = $this->muonline->query($query, $data);
        if (!$result) throw new \Exception(lang('error_22', true));

        $this->_deleteRegistrationVerification($username);

        if (config('season_1_support')) {
            // noinspection SqlNoDataSourceInspection — VI_CURR_INFO is a Season 1 game table
            $this->muonline->query(/** @lang TSQL */ "INSERT INTO VI_CURR_INFO (ends_days, chek_code, used_time, memb___id, memb_name, memb_guid, sno__numb, Bill_Section, Bill_Value, Bill_Hour, Surplus_Point, Surplus_Minute, Increase_Days) VALUES ('2005', '1', '1234', ?, '', '1', '7', '6', '3', '6', '6', '2020-01-01 00:00:00', '0')", array($verifyKey['registration_account']));
        }

        if ($regCfg['send_welcome_email']) {
            $this->sendWelcomeEmail($verifyKey['registration_account'], $verifyKey['registration_email']);
        }

        message('success', lang('success_1', true));
        redirect(2, 'login/', 5);
    }

    public function insertAccountCountry()
    {
        if (!check_value($this->_account)) return;
        if (!check_value($this->_country)) return;
        $result = $this->muonline->query("INSERT INTO " . Account_Country . " (account, country) VALUES (?, ?)", array($this->_account, $this->_country));
        if (!$result) return;
        return true;
    }

    public function getServerList()
    {
        $result = $this->muonline->query_fetch(
            "SELECT DISTINCT(" . _CLMN_MS_GS_ . ") FROM " . _TBL_MS_
        );
        if (!is_array($result)) return;
        foreach ($result as $row) { $servers[] = $row[_CLMN_MS_GS_]; }
        return $servers ?? null;
    }

    public function getOnlineAccountCount($server = null): int
    {
        if (check_value($server)) {
            $result = $this->muonline->query_fetch_single("SELECT COUNT(*) as online FROM " . _TBL_MS_ . " WHERE " . _CLMN_CONNSTAT_ . " = 1 AND " . _CLMN_MS_GS_ . " = ?", array($server));
        } else {
            $result = $this->muonline->query_fetch_single("SELECT COUNT(*) as online FROM " . _TBL_MS_ . " WHERE " . _CLMN_CONNSTAT_ . " = 1");
        }
        return is_array($result) ? (int)$result['online'] : 0;
    }

    public function getOnlineAccountList($server = null): false|array|null
    {
        if (check_value($server)) {
            return $this->muonline->query_fetch("SELECT " . _CLMN_MS_MEMBID_ . ", " . _CLMN_MS_GS_ . ", " . _CLMN_MS_IP_ . " FROM " . _TBL_MS_ . " WHERE " . _CLMN_CONNSTAT_ . " = 1 AND " . _CLMN_MS_GS_ . " = ?", array($server));
        }
        return $this->muonline->query_fetch("SELECT " . _CLMN_MS_MEMBID_ . ", " . _CLMN_MS_GS_ . ", " . _CLMN_MS_IP_ . " FROM " . _TBL_MS_ . " WHERE " . _CLMN_CONNSTAT_ . " = 1");
    }

    private function sendRegistrationVerificationEmail($username, $account_email, $key): void
    {
        try {
            $email = new Email();
            $email->setTemplate('WELCOME_EMAIL_VERIFICATION');
            $email->addVariable('{USERNAME}', $username);
            $email->addVariable('{LINK}', __BASE_URL__ . 'verifyemail/?op=2&user=' . $username . '&key=' . $key);
            $email->addAddress($account_email);
            $email->send();
        } catch (\Exception $ex) {
            if ($this->_debug) throw new \Exception($ex->getMessage());
        }
    }

    private function sendWelcomeEmail($username, $address): void
    {
        try {
            $email = new Email();
            $email->setTemplate('WELCOME_EMAIL');
            $email->addVariable('{USERNAME}', $username);
            $email->addAddress($address);
            $email->send();
        } catch (\Exception $ex) {
            if ($this->_debug) throw new \Exception($ex->getMessage());
        }
    }

    private function createRegistrationVerification($username, $password, $email)
    {
        if (!check_value($username) || !check_value($password) || !check_value($email)) return;
        $key    = uniqid('', true);
        $result = $this->muonline->query("INSERT INTO " . Register_Account . " (registration_account,registration_password,registration_email,registration_date,registration_ip,registration_key) VALUES (?,?,?,?,?,?)", [$username, $password, $email, time(), $this->server()->remoteAddress() ?? '0.0.0.0', $key]);
        if (!$result) return;
        return $key;
    }

    private function server(): ServerContext
    {
        if (!$this->serverContext instanceof ServerContext) {
            $this->serverContext = new ServerContext();
        }

        return $this->serverContext;
    }

    private function _deleteRegistrationVerification($username): void
    {
        if (!check_value($username)) return;
        $this->muonline->query("DELETE FROM " . Register_Account . " WHERE registration_account = ?", array($username));
    }

    private function checkUsernameEVS($username)
    {
        if (!check_value($username)) return;
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . Register_Account . " WHERE registration_account = ?", array($username));
        if (!is_array($result)) return;
        $configs   = loadConfigurations('register');
        $timelimit = $result['registration_date'] + $configs['verification_timelimit'] * 60 * 60;
        if ($timelimit > time()) return true;
        $this->_deleteRegistrationVerification($username);
        return false;
    }

    private function checkEmailEVS($email)
    {
        if (!check_value($email)) return;
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . Register_Account . " WHERE registration_email = ?", array($email));
        if (!is_array($result)) return;
        $configs   = loadConfigurations('register');
        $timelimit = $result['registration_date'] + $configs['verification_timelimit'] * 60 * 60;
        if ($timelimit > time()) return true;
        $this->_deleteRegistrationVerification($result['registration_account']);
        return false;
    }

    private function _changeEmailVerificationMail($userName, $emailAddress, $newEmail, $verificationLink, $ipAddress): bool
    {
        try {
            $email = new Email();
            $email->setTemplate('CHANGE_EMAIL_VERIFICATION');
            $email->addVariable('{USERNAME}', $userName);
            $email->addVariable('{IP_ADDRESS}', $ipAddress);
            $email->addVariable('{NEW_EMAIL}', $newEmail);
            $email->addVariable('{LINK}', $verificationLink);
            $email->addAddress($emailAddress);
            $email->send();
            return true;
        } catch (\Exception $ex) {
            if ($this->_debug) throw new \Exception($ex->getMessage());
            return false;
        }
    }

    private function _generateAccountRecoveryLink($userid, $email, $recovery_code): string
    {
        return __BASE_URL__ . 'forgotpassword/?ui=' . $userid . '&ue=' . $email . '&key=' . $recovery_code;
    }
}

