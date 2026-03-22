<?php

declare(strict_types=1);

namespace Darkheim\Application\Account;

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Auth\Login;
use Darkheim\Application\Language\Translator;
use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Email\Email;
use Darkheim\Infrastructure\Runtime\ServerContext;

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
        if (! Validator::hasValue($username)) {
            throw new \Exception(Translator::phrase('error_4'));
        }
        if (! Validator::hasValue($password)) {
            throw new \Exception(Translator::phrase('error_4'));
        }
        if (! Validator::hasValue($cpassword)) {
            throw new \Exception(Translator::phrase('error_4'));
        }
        if (! Validator::hasValue($email)) {
            throw new \Exception(Translator::phrase('error_4'));
        }

        if (! Validator::UsernameLength($username)) {
            throw new \Exception(Translator::phrase('error_5'));
        }
        if (! Validator::AlphaNumeric($username)) {
            throw new \Exception(Translator::phrase('error_6'));
        }
        if (! Validator::PasswordLength($password)) {
            throw new \Exception(Translator::phrase('error_7'));
        }
        if ($password != $cpassword) {
            throw new \Exception(Translator::phrase('error_8'));
        }
        if (! Validator::Email($email)) {
            throw new \Exception(Translator::phrase('error_9'));
        }

        $regCfg = $this->moduleConfig('register');

        if ($this->userExists($username)) {
            throw new \Exception(Translator::phrase('error_10'));
        }
        if ($this->emailExists($email)) {
            throw new \Exception(Translator::phrase('error_11'));
        }

        if ($regCfg['verify_email'] ?? false) {
            if ($this->checkUsernameEVS($username)) {
                throw new \Exception(Translator::phrase('error_10'));
            }
            if ($this->checkEmailEVS($email)) {
                throw new \Exception(Translator::phrase('error_11'));
            }
            $verificationKey = $this->createRegistrationVerification($username, $password, $email);
            if (! Validator::hasValue($verificationKey)) {
                throw new \Exception(Translator::phrase('error_23'));
            }
            $this->sendRegistrationVerificationEmail($username, $email, $verificationKey);
            \Darkheim\Application\View\MessageRenderer::toast('success', Translator::phrase('success_18'));
            return;
        }

        $data = ['username' => $username, 'password' => $password, 'name' => $username, 'serial' => $this->_defaultAccountSerial, 'email' => $email];
        switch ($this->_passwordEncryption) {
            case 'wzmd5':
                // noinspection SqlNoDataSourceInspection — fn_md5 is a MSSQL server-side stored function
                $query = /** @lang TSQL */ "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, [dbo].[fn_md5](:password, :username), :name, :serial, :email, 0, 0)";
                break;
            case 'phpmd5':
                $data['password'] = md5($password);
                $query            = "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, :password, :name, :serial, :email, 0, 0)";
                break;
            case 'sha256':
                $data['password'] = '0x' . hash('sha256', $password . $username . $this->_sha256salt);
                $query            = "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, CONVERT(binary(32),:password,1), :name, :serial, :email, 0, 0)";
                break;
            default:
                $query = "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, :password, :name, :serial, :email, 0, 0)";
        }

        $result = $this->muonline->query($query, $data);
        if (! $result) {
            throw new \Exception(Translator::phrase('error_22'));
        }

        if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('season_1_support')) {
            // noinspection SqlNoDataSourceInspection — VI_CURR_INFO is a Season 1 game table
            $this->muonline->query(/** @lang TSQL */ "INSERT INTO VI_CURR_INFO (ends_days, chek_code, used_time, memb___id, memb_name, memb_guid, sno__numb, Bill_Section, Bill_Value, Bill_Hour, Surplus_Point, Surplus_Minute, Increase_Days) VALUES ('2005', '1', '1234', ?, '', '1', '7', '6', '3', '6', '6', '2020-01-01 00:00:00', '0')", [$username]);
        }

        if ($regCfg['send_welcome_email'] ?? false) {
            $this->sendWelcomeEmail($username, $email);
        }

        \Darkheim\Application\View\MessageRenderer::toast('success', Translator::phrase('success_1'));

        if ($regCfg['automatic_login'] ?? false) {
            try {
                $userLogin = new Login();
                $userLogin->validateLogin($username, $password);
            } catch (\Exception $ex) {
                \Darkheim\Infrastructure\Http\Redirector::go(1, 'login/');
            }
        } else {
            \Darkheim\Infrastructure\Http\Redirector::go(2, 'login/', 5);
        }
    }

    public function changePasswordProcess($userid, $username, $password, $new_password, $confirm_new_password): void
    {
        if (! Validator::hasValue($userid)) {
            throw new \Exception(Translator::phrase('error_4'));
        }
        if (! Validator::hasValue($username)) {
            throw new \Exception(Translator::phrase('error_4'));
        }
        if (! Validator::hasValue($password)) {
            throw new \Exception(Translator::phrase('error_4'));
        }
        if (! Validator::hasValue($new_password)) {
            throw new \Exception(Translator::phrase('error_4'));
        }
        if (! Validator::hasValue($confirm_new_password)) {
            throw new \Exception(Translator::phrase('error_4'));
        }
        if (! Validator::PasswordLength($new_password)) {
            throw new \Exception(Translator::phrase('error_7'));
        }
        if ($new_password != $confirm_new_password) {
            throw new \Exception(Translator::phrase('error_8'));
        }
        if (! $this->validateUser($username, $password)) {
            throw new \Exception(Translator::phrase('error_13'));
        }
        if ($this->accountOnline($username)) {
            throw new \Exception(Translator::phrase('error_14'));
        }
        if (! $this->changePassword($userid, $username, $new_password)) {
            throw new \Exception(Translator::phrase('error_23'));
        }

        $accountData = $this->accountInformation($userid);
        try {
            $email = new Email();
            $email->setTemplate('CHANGE_PASSWORD');
            $email->addVariable('{USERNAME}', $username);
            $email->addVariable('{NEW_PASSWORD}', $new_password);
            $email->addAddress($accountData[_CLMN_EMAIL_]);
            $email->send();
        } catch (\Exception $ex) {
        }

        \Darkheim\Application\View\MessageRenderer::toast('success', Translator::phrase('success_2'));
    }

    public function changePasswordProcess_verifyEmail($userid, $username, $password, $new_password, $confirm_new_password, $ip_address): void
    {
        if (! Validator::hasValue($userid)) {
            throw new \Exception(Translator::phrase('error_4'));
        }
        if (! Validator::hasValue($username)) {
            throw new \Exception(Translator::phrase('error_4'));
        }
        if (! Validator::hasValue($password)) {
            throw new \Exception(Translator::phrase('error_4'));
        }
        if (! Validator::hasValue($new_password)) {
            throw new \Exception(Translator::phrase('error_4'));
        }
        if (! Validator::hasValue($confirm_new_password)) {
            throw new \Exception(Translator::phrase('error_4'));
        }
        if (! Validator::PasswordLength($new_password)) {
            throw new \Exception(Translator::phrase('error_7'));
        }
        if ($new_password != $confirm_new_password) {
            throw new \Exception(Translator::phrase('error_8'));
        }

        $mypassCfg = $this->moduleConfig('my-password');
        if (! $this->validateUser($username, $password)) {
            throw new \Exception(Translator::phrase('error_13'));
        }
        if ($this->accountOnline($username)) {
            throw new \Exception(Translator::phrase('error_14'));
        }
        if ($this->hasActivePasswordChangeRequest($userid)) {
            throw new \Exception(Translator::phrase('error_19'));
        }

        $accountData = $this->accountInformation($userid);
        if (! is_array($accountData)) {
            throw new \Exception(Translator::phrase('error_21'));
        }

        $auth_code = random_int(111111, 999999);
        $link      = $this->generatePasswordChangeVerificationURL($userid, $auth_code);

        if (! $this->addPasswordChangeRequest($userid, $new_password, $auth_code)) {
            throw new \Exception(Translator::phrase('error_21'));
        }

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
            \Darkheim\Application\View\MessageRenderer::toast('success', Translator::phrase('success_3'));
        } catch (\Exception $ex) {
            if ($this->_debug) {
                throw new \Exception($ex->getMessage());
            }
            throw new \Exception(Translator::phrase('error_20'));
        }
    }

    public function changePasswordVerificationProcess($user_id, $auth_code): void
    {
        if (! Validator::hasValue($user_id)) {
            throw new \Exception(Translator::phrase('error_24'));
        }
        if (! Validator::hasValue($auth_code)) {
            throw new \Exception(Translator::phrase('error_24'));
        }
        if (! Validator::UnsignedNumber($user_id)) {
            throw new \Exception(Translator::phrase('error_25'));
        }
        if (! Validator::UnsignedNumber($auth_code)) {
            throw new \Exception(Translator::phrase('error_25'));
        }

        $result = $this->muonline->query_fetch_single("SELECT * FROM " . Passchange_Request . " WHERE user_id = ?", [$user_id]);
        if (! is_array($result)) {
            throw new \Exception(Translator::phrase('error_25'));
        }

        $mypassCfg       = $this->moduleConfig('my-password');
        $request_timeout = $mypassCfg['change_password_request_timeout'] * 3600;
        $request_date    = $result['request_date'] + $request_timeout;

        if ($request_date < time()) {
            throw new \Exception(Translator::phrase('error_26'));
        }
        if ($result['auth_code'] != $auth_code) {
            throw new \Exception(Translator::phrase('error_27'));
        }

        $accountData  = $this->accountInformation($user_id);
        $username     = $accountData[_CLMN_USERNM_];
        $new_password = $result['new_password'];

        if ($this->accountOnline($username)) {
            throw new \Exception(Translator::phrase('error_14'));
        }
        if (! $this->changePassword($user_id, $username, $new_password)) {
            throw new \Exception(Translator::phrase('error_29'));
        }

        try {
            $email = new Email();
            $email->setTemplate('CHANGE_PASSWORD');
            $email->addVariable('{USERNAME}', $username);
            $email->addVariable('{NEW_PASSWORD}', $new_password);
            $email->addAddress($accountData[_CLMN_EMAIL_]);
            $email->send();
        } catch (\Exception $ex) {
            if ($this->_debug) {
                throw new \Exception($ex->getMessage());
            }
        }

        $this->removePasswordChangeRequest($user_id);
        \Darkheim\Application\View\MessageRenderer::toast('success', Translator::phrase('success_5'));
    }

    public function passwordRecoveryProcess($user_email, $ip_address): void
    {
        if (! Validator::hasValue($user_email)) {
            throw new \Exception(Translator::phrase('error_30'));
        }
        if (! Validator::hasValue($ip_address)) {
            throw new \Exception(Translator::phrase('error_30'));
        }
        if (! Validator::Email($user_email)) {
            throw new \Exception(Translator::phrase('error_30'));
        }
        if (! Validator::Ip($ip_address)) {
            throw new \Exception(Translator::phrase('error_30'));
        }
        if (! $this->emailExists($user_email)) {
            throw new \Exception(Translator::phrase('error_30'));
        }

        $user_id = $this->retrieveUserIDbyEmail($user_email);
        if (! Validator::hasValue($user_id)) {
            throw new \Exception(Translator::phrase('error_23'));
        }
        $accountData = $this->accountInformation($user_id);
        if (! is_array($accountData)) {
            throw new \Exception(Translator::phrase('error_23'));
        }

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
            \Darkheim\Application\View\MessageRenderer::toast('success', Translator::phrase('success_6'));
        } catch (\Exception $ex) {
            if ($this->_debug) {
                throw new \Exception($ex->getMessage());
            }
            throw new \Exception(Translator::phrase('error_23'));
        }
    }

    public function passwordRecoveryVerificationProcess($ui, $ue, $key): void
    {
        if (! Validator::hasValue($ui)) {
            throw new \Exception(Translator::phrase('error_31'));
        }
        if (! Validator::hasValue($ue)) {
            throw new \Exception(Translator::phrase('error_31'));
        }
        if (! Validator::hasValue($key)) {
            throw new \Exception(Translator::phrase('error_31'));
        }
        if (! Validator::UnsignedNumber($ui)) {
            throw new \Exception(Translator::phrase('error_31'));
        }
        if (! $this->emailExists($ue)) {
            throw new \Exception(Translator::phrase('error_31'));
        }

        $accountData = $this->accountInformation($ui);
        if (! is_array($accountData)) {
            throw new \Exception(Translator::phrase('error_31'));
        }

        $gen_key = $this->generateAccountRecoveryCode($ui, $accountData[_CLMN_USERNM_]);
        if ($key != $gen_key) {
            throw new \Exception(Translator::phrase('error_31'));
        }

        $new_password = random_int(11111111, 99999999);
        if (! $this->changePassword($ui, $accountData[_CLMN_USERNM_], $new_password)) {
            throw new \Exception(Translator::phrase('error_23'));
        }

        try {
            $email = new Email();
            $email->setTemplate('PASSWORD_RECOVERY_COMPLETED');
            $email->addVariable('{USERNAME}', $accountData[_CLMN_USERNM_]);
            $email->addVariable('{NEW_PASSWORD}', $new_password);
            $email->addAddress($accountData[_CLMN_EMAIL_]);
            $email->send();
            \Darkheim\Application\View\MessageRenderer::toast('success', Translator::phrase('success_7'));
        } catch (\Exception $ex) {
            if ($this->_debug) {
                throw new \Exception($ex->getMessage());
            }
            throw new \Exception(Translator::phrase('error_23'));
        }
    }

    public function changeEmailAddress($accountId, $newEmail, $ipAddress): void
    {
        if (! Validator::hasValue($accountId)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
        if (! Validator::hasValue($newEmail)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
        if (! Validator::hasValue($ipAddress)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
        if (! Validator::Ip($ipAddress)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
        if (! Validator::Email($newEmail)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
        if ($this->emailExists($newEmail)) {
            throw new \Exception(Translator::phrase('error_11'));
        }

        $accountInfo = $this->accountInformation($accountId);
        if (! is_array($accountInfo)) {
            throw new \Exception(Translator::phrase('error_21'));
        }

        $myemailCfg = $this->moduleConfig('my-email');
        if ($myemailCfg['require_verification']) {
            $userName         = $accountInfo[_CLMN_USERNM_];
            $userEmail        = $accountInfo[_CLMN_EMAIL_];
            $requestDate      = (int) strtotime(date('m/d/Y 23:59'));
            $key              = md5(md5($userName) . md5($userEmail) . md5((string) $requestDate) . md5($newEmail));
            $verificationLink = __BASE_URL__ . 'verifyemail/?op=3&uid=' . $accountId . '&email=' . $newEmail . '&key=' . $key;
            if (! $this->_changeEmailVerificationMail($userName, $userEmail, $newEmail, $verificationLink, $ipAddress)) {
                throw new \Exception(Translator::phrase('error_21'));
            }
        } elseif (! $this->updateEmail($accountId, $newEmail)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
    }

    public function changeEmailVerificationProcess($encodedId, $newEmail, $encryptedKey): void
    {
        if (! Validator::UnsignedNumber($encodedId)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
        if (! Validator::Email($newEmail)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
        if ($this->emailExists($newEmail)) {
            throw new \Exception(Translator::phrase('error_11'));
        }

        $accountInfo = $this->accountInformation($encodedId);
        if (! is_array($accountInfo)) {
            throw new \Exception(Translator::phrase('error_21'));
        }

        $requestDate = (int) strtotime(date('m/d/Y 23:59'));
        $key         = md5(md5($accountInfo[_CLMN_USERNM_]) . md5($accountInfo[_CLMN_EMAIL_]) . md5((string) $requestDate) . md5($newEmail));
        if ($key != $encryptedKey) {
            throw new \Exception(Translator::phrase('error_21'));
        }
        if (! $this->updateEmail($encodedId, $newEmail)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
    }

    public function verifyRegistrationProcess($username, $key): void
    {
        $verifyKey = $this->muonline->query_fetch_single("SELECT * FROM " . Register_Account . " WHERE registration_account = ? AND registration_key = ?", [$username, $key]);
        if (! is_array($verifyKey)) {
            throw new \Exception(Translator::phrase('error_25'));
        }

        $regCfg = $this->moduleConfig('register');
        $data   = ['username' => $verifyKey['registration_account'], 'password' => $verifyKey['registration_password'], 'name' => $verifyKey['registration_account'], 'serial' => $this->_defaultAccountSerial, 'email' => $verifyKey['registration_email']];

        switch ($this->_passwordEncryption) {
            case 'wzmd5':
                // noinspection SqlNoDataSourceInspection — fn_md5 is a MSSQL server-side stored function
                $query = /** @lang TSQL */ "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, [dbo].[fn_md5](:password, :username), :name, :serial, :email, 0, 0)";
                break;
            case 'phpmd5':
                $data['password'] = md5($verifyKey['registration_password']);
                $query            = "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, :password, :name, :serial, :email, 0, 0)";
                break;
            case 'sha256':
                $data['password'] = '0x' . hash('sha256', $verifyKey['registration_password'] . $verifyKey['registration_account'] . $this->_sha256salt);
                $query            = "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, CONVERT(binary(32),:password,1), :name, :serial, :email, 0, 0)";
                break;
            default:
                $query = "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, :password, :name, :serial, :email, 0, 0)";
        }

        $result = $this->muonline->query($query, $data);
        if (! $result) {
            throw new \Exception(Translator::phrase('error_22'));
        }

        $this->_deleteRegistrationVerification($username);

        if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('season_1_support')) {
            // noinspection SqlNoDataSourceInspection — VI_CURR_INFO is a Season 1 game table
            $this->muonline->query(/** @lang TSQL */ "INSERT INTO VI_CURR_INFO (ends_days, chek_code, used_time, memb___id, memb_name, memb_guid, sno__numb, Bill_Section, Bill_Value, Bill_Hour, Surplus_Point, Surplus_Minute, Increase_Days) VALUES ('2005', '1', '1234', ?, '', '1', '7', '6', '3', '6', '6', '2020-01-01 00:00:00', '0')", [$verifyKey['registration_account']]);
        }

        if ($regCfg['send_welcome_email']) {
            $this->sendWelcomeEmail($verifyKey['registration_account'], $verifyKey['registration_email']);
        }

        \Darkheim\Application\View\MessageRenderer::toast('success', Translator::phrase('success_1'));
        \Darkheim\Infrastructure\Http\Redirector::go(2, 'login/', 5);
    }

    public function insertAccountCountry()
    {
        if (! Validator::hasValue($this->_account)) {
            return;
        }
        if (! Validator::hasValue($this->_country)) {
            return;
        }
        $result = $this->muonline->query("INSERT INTO " . Account_Country . " (account, country) VALUES (?, ?)", [$this->_account, $this->_country]);
        if (! $result) {
            return;
        }
        return true;
    }

    public function getServerList()
    {
        $result = $this->muonline->query_fetch(
            "SELECT DISTINCT(" . _CLMN_MS_GS_ . ") FROM " . _TBL_MS_,
        );
        if (! is_array($result)) {
            return;
        }
        foreach ($result as $row) {
            $servers[] = $row[_CLMN_MS_GS_];
        }
        return $servers ?? null;
    }

    public function getOnlineAccountCount($server = null): int
    {
        if (Validator::hasValue($server)) {
            $result = $this->muonline->query_fetch_single("SELECT COUNT(*) as online FROM " . _TBL_MS_ . " WHERE " . _CLMN_CONNSTAT_ . " = 1 AND " . _CLMN_MS_GS_ . " = ?", [$server]);
        } else {
            $result = $this->muonline->query_fetch_single("SELECT COUNT(*) as online FROM " . _TBL_MS_ . " WHERE " . _CLMN_CONNSTAT_ . " = 1");
        }
        return is_array($result) ? (int) $result['online'] : 0;
    }

    public function getOnlineAccountList($server = null): false|array|null
    {
        if (Validator::hasValue($server)) {
            return $this->muonline->query_fetch("SELECT " . _CLMN_MS_MEMBID_ . ", " . _CLMN_MS_GS_ . ", " . _CLMN_MS_IP_ . " FROM " . _TBL_MS_ . " WHERE " . _CLMN_CONNSTAT_ . " = 1 AND " . _CLMN_MS_GS_ . " = ?", [$server]);
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
            if ($this->_debug) {
                throw new \Exception($ex->getMessage());
            }
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
            if ($this->_debug) {
                throw new \Exception($ex->getMessage());
            }
        }
    }

    private function createRegistrationVerification($username, $password, $email)
    {
        if (! Validator::hasValue($username) || ! Validator::hasValue($password) || ! Validator::hasValue($email)) {
            return;
        }
        $key    = uniqid('', true);
        $result = $this->muonline->query("INSERT INTO " . Register_Account . " (registration_account,registration_password,registration_email,registration_date,registration_ip,registration_key) VALUES (?,?,?,?,?,?)", [$username, $password, $email, time(), $this->server()->remoteAddress() ?? '0.0.0.0', $key]);
        if (! $result) {
            return;
        }
        return $key;
    }

    private function server(): ServerContext
    {
        if (! $this->serverContext instanceof ServerContext) {
            $this->serverContext = new ServerContext();
        }

        return $this->serverContext;
    }

    private function _deleteRegistrationVerification($username): void
    {
        if (! Validator::hasValue($username)) {
            return;
        }
        $this->muonline->query("DELETE FROM " . Register_Account . " WHERE registration_account = ?", [$username]);
    }

    private function checkUsernameEVS($username)
    {
        if (! Validator::hasValue($username)) {
            return;
        }
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . Register_Account . " WHERE registration_account = ?", [$username]);
        if (! is_array($result)) {
            return;
        }
        $configs   = $this->moduleConfig('register');
        $timelimit = $result['registration_date'] + $configs['verification_timelimit'] * 60 * 60;
        if ($timelimit > time()) {
            return true;
        }
        $this->_deleteRegistrationVerification($username);
        return false;
    }

    private function checkEmailEVS($email)
    {
        if (! Validator::hasValue($email)) {
            return;
        }
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . Register_Account . " WHERE registration_email = ?", [$email]);
        if (! is_array($result)) {
            return;
        }
        $configs   = $this->moduleConfig('register');
        $timelimit = $result['registration_date'] + $configs['verification_timelimit'] * 60 * 60;
        if ($timelimit > time()) {
            return true;
        }
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
            if ($this->_debug) {
                throw new \Exception($ex->getMessage());
            }
            return false;
        }
    }

    private function _generateAccountRecoveryLink($userid, $email, $recovery_code): string
    {
        return __BASE_URL__ . 'forgotpassword/?ui=' . $userid . '&ue=' . $email . '&key=' . $recovery_code;
    }

    private function moduleConfig(string $name): array
    {
        $config = BootstrapContext::configProvider()?->moduleConfig($name);

        return is_array($config) ? $config : [];
    }
}
