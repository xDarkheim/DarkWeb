<?php

declare(strict_types=1);

namespace Darkheim\Application\Account;

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Auth\Login;
use Darkheim\Application\Shared\Language\Translator;
use Darkheim\Application\Shared\UI\MessageRenderer;
use Darkheim\Domain\Validation\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Email\Email;
use Darkheim\Infrastructure\Http\Redirector;
use Darkheim\Infrastructure\Runtime\Support\ServerContext;

/**
 * Account — registration, verification, password recovery, email change.
 */
class Account extends Common
{
    private const string REGISTRATION_REQUEST_BUCKET = 'registration-verifications';
    private const string PASSWORD_RECOVERY_BUCKET    = 'password-recovery';
    private const string EMAIL_CHANGE_BUCKET         = 'email-change-verifications';

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
            MessageRenderer::toast('success', Translator::phrase('success_18'));
            return;
        }

        $encodedPassword = $this->encodePasswordForStorage($username, $password);
        $result          = $this->insertAccountWithEncodedPassword($username, $encodedPassword, $email);
        if (! $result) {
            throw new \Exception(Translator::phrase('error_22'));
        }

        if (BootstrapContext::cmsValue('season_1_support')) {
            // noinspection SqlNoDataSourceInspection — VI_CURR_INFO is a Season 1 game table
            $this->muonline->query(/** @lang TSQL */ "INSERT INTO VI_CURR_INFO (ends_days, chek_code, used_time, memb___id, memb_name, memb_guid, sno__numb, Bill_Section, Bill_Value, Bill_Hour, Surplus_Point, Surplus_Minute, Increase_Days) VALUES ('2005', '1', '1234', ?, '', '1', '7', '6', '3', '6', '6', '2020-01-01 00:00:00', '0')", [$username]);
        }

        if ($regCfg['send_welcome_email'] ?? false) {
            $this->sendWelcomeEmail($username, $email);
        }

        MessageRenderer::toast('success', Translator::phrase('success_1'));

        if ($regCfg['automatic_login'] ?? false) {
            try {
                $userLogin = new Login();
                $userLogin->validateLogin($username, $password);
            } catch (\Exception $ex) {
                Redirector::go(1, 'login/');
            }
        } else {
            Redirector::go(2, 'login/', 5);
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
            $email->addAddress($accountData[_CLMN_EMAIL_]);
            $email->send();
        } catch (\Exception $ex) {
        }

        MessageRenderer::toast('success', Translator::phrase('success_2'));
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

        $auth_code = $this->generateOpaqueToken();
        $link      = $this->generatePasswordChangeVerificationURL($userid, $auth_code);

        if (! $this->addPasswordChangeRequest($userid, $new_password, $auth_code, $username)) {
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
            MessageRenderer::toast('success', Translator::phrase('success_3'));
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
        $storedRequest = $this->loadPasswordChangeRequest((string) $user_id);
        if (is_array($storedRequest)) {
            if (! $this->isPasswordChangeRequestActive($storedRequest)) {
                $this->removePasswordChangeRequest($user_id);
                throw new \Exception(Translator::phrase('error_26'));
            }
            if (! $this->opaqueTokenMatches((string) ($storedRequest['token_hash'] ?? ''), (string) $auth_code)) {
                throw new \Exception(Translator::phrase('error_27'));
            }

            $accountData = $this->accountInformation($user_id);
            if (! is_array($accountData)) {
                throw new \Exception(Translator::phrase('error_25'));
            }

            $username = (string) ($storedRequest['username'] ?? $accountData[_CLMN_USERNM_]);
            if ($this->accountOnline($username)) {
                throw new \Exception(Translator::phrase('error_14'));
            }

            $encodedPassword = (string) ($storedRequest['encoded_password'] ?? '');
            $passwordMode    = (string) ($storedRequest['password_mode'] ?? $this->passwordEncryptionMode());
            if ($encodedPassword === '' || ! $this->updatePasswordWithEncodedValue($user_id, $encodedPassword, $passwordMode)) {
                throw new \Exception(Translator::phrase('error_29'));
            }

            try {
                $email = new Email();
                $email->setTemplate('CHANGE_PASSWORD');
                $email->addVariable('{USERNAME}', $username);
                $email->addAddress($accountData[_CLMN_EMAIL_]);
                $email->send();
            } catch (\Exception $ex) {
                if ($this->_debug) {
                    throw new \Exception($ex->getMessage());
                }
            }

            $this->removePasswordChangeRequest($user_id);
            MessageRenderer::toast('success', Translator::phrase('success_5'));
            return;
        }

        if (! Validator::UnsignedNumber($auth_code)) {
            throw new \Exception(Translator::phrase('error_25'));
        }

        $result = $this->muonline->query_fetch_single("SELECT * FROM " . Passchange_Request . " WHERE user_id = ?", [$user_id]);
        if (! is_array($result)) {
            throw new \Exception(Translator::phrase('error_25'));
        }

        $request_date = (int) ($result['request_date'] ?? 0) + $this->passwordChangeRequestTimeout();

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
            $email->addAddress($accountData[_CLMN_EMAIL_]);
            $email->send();
        } catch (\Exception $ex) {
            if ($this->_debug) {
                throw new \Exception($ex->getMessage());
            }
        }

        $this->removePasswordChangeRequest($user_id);
        MessageRenderer::toast('success', Translator::phrase('success_5'));
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

        $arc = $this->generateAccountRecoveryCode();
        if (! $this->createPasswordRecoveryRequest((string) $accountData[_CLMN_MEMBID_], (string) $accountData[_CLMN_EMAIL_], $arc)) {
            throw new \Exception(Translator::phrase('error_23'));
        }
        $aru = $this->_generateAccountRecoveryLink((string) $accountData[_CLMN_MEMBID_], $arc);

        try {
            $email = new Email();
            $email->setTemplate('PASSWORD_RECOVERY_REQUEST');
            $email->addVariable('{USERNAME}', $accountData[_CLMN_USERNM_]);
            $email->addVariable('{DATE}', date("Y-m-d @ h:i a"));
            $email->addVariable('{IP_ADDRESS}', $ip_address);
            $email->addVariable('{LINK}', $aru);
            $email->addAddress($accountData[_CLMN_EMAIL_]);
            $email->send();
            MessageRenderer::toast('success', Translator::phrase('success_6'));
        } catch (\Exception $ex) {
            if ($this->_debug) {
                throw new \Exception($ex->getMessage());
            }
            throw new \Exception(Translator::phrase('error_23'));
        }
    }

    public function passwordRecoveryVerificationProcess($ui, $key, $ue = null): void
    {
        $this->validatePasswordRecoveryRequest($ui, $key, $ue);
    }

    public function passwordRecoveryResetProcess($ui, $key, $new_password, $confirm_new_password, $ue = null): void
    {
        if (! Validator::hasValue($new_password) || ! Validator::hasValue($confirm_new_password)) {
            throw new \Exception(Translator::phrase('error_31'));
        }
        if (! Validator::PasswordLength((string) $new_password)) {
            throw new \Exception(Translator::phrase('error_7'));
        }
        if ($new_password != $confirm_new_password) {
            throw new \Exception(Translator::phrase('error_8'));
        }

        $accountData = $this->validatePasswordRecoveryRequest($ui, $key, $ue);
        $userId      = (string) $accountData[_CLMN_MEMBID_];
        $username    = (string) $accountData[_CLMN_USERNM_];

        if ($this->accountOnline($username)) {
            throw new \Exception(Translator::phrase('error_14'));
        }
        if (! $this->changePassword($userId, $username, $new_password)) {
            throw new \Exception(Translator::phrase('error_23'));
        }

        try {
            $email = new Email();
            $email->setTemplate('PASSWORD_RECOVERY_COMPLETED');
            $email->addVariable('{USERNAME}', $username);
            $email->addAddress($accountData[_CLMN_EMAIL_]);
            $email->send();
        } catch (\Exception $ex) {
            if ($this->_debug) {
                throw new \Exception($ex->getMessage());
            }
            throw new \Exception(Translator::phrase('error_23'));
        }

        $this->actionStore()->delete(self::PASSWORD_RECOVERY_BUCKET, $userId);
        MessageRenderer::toast('success', Translator::phrase('success_7'));
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
            $token            = $this->generateOpaqueToken();
            $verificationLink = __BASE_URL__ . 'verifyemail/?op=3&uid=' . $accountId . '&email=' . rawurlencode((string) $newEmail) . '&key=' . rawurlencode($token);
            if (! $this->storeEmailChangeRequest((string) $accountId, (string) $newEmail, $token) || ! $this->_changeEmailVerificationMail((string) $accountInfo[_CLMN_USERNM_], (string) $accountInfo[_CLMN_EMAIL_], $newEmail, $verificationLink, $ipAddress)) {
                throw new \Exception(Translator::phrase('error_21'));
            }
        } elseif (! $this->updateEmail($accountId, $newEmail)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
    }

    public function changeEmailVerificationProcess($encodedId, $newEmail, $encryptedKey): void
    {
        if (! Validator::hasValue($encodedId)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
        if (! Validator::hasValue($newEmail)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
        if (! Validator::hasValue($encryptedKey)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
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

        $request = $this->loadActionRequest(self::EMAIL_CHANGE_BUCKET, (string) $encodedId);
        if (! is_array($request) || ! $this->isEmailChangeRequestActive($request)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
        if (! hash_equals((string) ($request['new_email'] ?? ''), $newEmail)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
        if (! $this->opaqueTokenMatches((string) ($request['token_hash'] ?? ''), (string) $encryptedKey)) {
            throw new \Exception(Translator::phrase('error_21'));
        }
        if (! $this->updateEmail($encodedId, $newEmail)) {
            throw new \Exception(Translator::phrase('error_21'));
        }

        $this->actionStore()->delete(self::EMAIL_CHANGE_BUCKET, (string) $encodedId);
    }

    public function verifyRegistrationProcess($username, $key): void
    {
        $registrationRequest = $this->loadActionRequest(self::REGISTRATION_REQUEST_BUCKET, (string) $username);
        if (is_array($registrationRequest)) {
            if (! $this->isRegistrationRequestActive($registrationRequest)) {
                $this->_deleteRegistrationVerification($username);
                throw new \Exception(Translator::phrase('error_25'));
            }
            if (! $this->opaqueTokenMatches((string) ($registrationRequest['token_hash'] ?? ''), (string) $key)) {
                throw new \Exception(Translator::phrase('error_25'));
            }
            if (! $this->insertAccountWithEncodedPassword(
                (string) ($registrationRequest['registration_account'] ?? ''),
                (string) ($registrationRequest['registration_password'] ?? ''),
                (string) ($registrationRequest['registration_email'] ?? ''),
                (string) ($registrationRequest['password_mode'] ?? $this->passwordEncryptionMode()),
            )) {
                throw new \Exception(Translator::phrase('error_22'));
            }

            $this->_deleteRegistrationVerification($username);

            if (BootstrapContext::cmsValue('season_1_support')) {
                $this->muonline->query(/** @lang TSQL */ "INSERT INTO VI_CURR_INFO (ends_days, chek_code, used_time, memb___id, memb_name, memb_guid, sno__numb, Bill_Section, Bill_Value, Bill_Hour, Surplus_Point, Surplus_Minute, Increase_Days) VALUES ('2005', '1', '1234', ?, '', '1', '7', '6', '3', '6', '6', '2020-01-01 00:00:00', '0')", [(string) ($registrationRequest['registration_account'] ?? '')]);
            }

            $regCfg = $this->moduleConfig('register');
            if ($regCfg['send_welcome_email']) {
                $this->sendWelcomeEmail((string) ($registrationRequest['registration_account'] ?? ''), (string) ($registrationRequest['registration_email'] ?? ''));
            }

            MessageRenderer::toast('success', Translator::phrase('success_1'));
            Redirector::go(2, 'login/', 5);
            return;
        }

        $verifyKey = $this->muonline->query_fetch_single("SELECT * FROM " . Register_Account . " WHERE registration_account = ?", [$username]);
        if (! is_array($verifyKey)) {
            throw new \Exception(Translator::phrase('error_25'));
        }

        $configs   = $this->moduleConfig('register');
        $timelimit = (int) ($verifyKey['registration_date'] ?? 0) + ($configs['verification_timelimit'] ?? 24) * 3600;
        if ($timelimit < time()) {
            $this->_deleteRegistrationVerification($username);
            throw new \Exception(Translator::phrase('error_25'));
        }

        if (! hash_equals((string) $verifyKey['registration_key'], (string) $key)) {
            throw new \Exception(Translator::phrase('error_25'));
        }

        $encodedPassword = $this->encodePasswordForStorage((string) $verifyKey['registration_account'], (string) $verifyKey['registration_password']);
        if (! $this->insertAccountWithEncodedPassword((string) $verifyKey['registration_account'], $encodedPassword, (string) $verifyKey['registration_email'])) {
            throw new \Exception(Translator::phrase('error_22'));
        }

        $this->_deleteRegistrationVerification($username);

        if (BootstrapContext::cmsValue('season_1_support')) {
            $this->muonline->query(/** @lang TSQL */ "INSERT INTO VI_CURR_INFO (ends_days, chek_code, used_time, memb___id, memb_name, memb_guid, sno__numb, Bill_Section, Bill_Value, Bill_Hour, Surplus_Point, Surplus_Minute, Increase_Days) VALUES ('2005', '1', '1234', ?, '', '1', '7', '6', '3', '6', '6', '2020-01-01 00:00:00', '0')", [(string) $verifyKey['registration_account']]);
        }

        if ($configs['send_welcome_email'] ?? false) {
            $this->sendWelcomeEmail((string) $verifyKey['registration_account'], (string) $verifyKey['registration_email']);
        }

        MessageRenderer::toast('success', Translator::phrase('success_1'));
        Redirector::go(2, 'login/', 5);
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
            $email->addVariable('{LINK}', __BASE_URL__ . 'verifyemail/?op=2&user=' . rawurlencode((string) $username) . '&key=' . rawurlencode((string) $key));
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
        $key             = $this->generateOpaqueToken();
        $encodedPassword = $this->encodePasswordForStorage((string) $username, (string) $password);

        if (! $this->saveActionRequest(self::REGISTRATION_REQUEST_BUCKET, (string) $username, [
            'registration_account'  => (string) $username,
            'registration_password' => $encodedPassword,
            'registration_email'    => (string) $email,
            'registration_date'     => time(),
            'registration_ip'       => $this->server()->remoteAddress() ?? '0.0.0.0',
            'password_mode'         => $this->passwordEncryptionMode(),
            'token_hash'            => $this->hashOpaqueToken($key),
        ])) {
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
        $this->actionStore()->delete(self::REGISTRATION_REQUEST_BUCKET, (string) $username);
        $this->muonline->query("DELETE FROM " . Register_Account . " WHERE registration_account = ?", [$username]);
    }

    private function checkUsernameEVS($username)
    {
        if (! Validator::hasValue($username)) {
            return;
        }
        $request = $this->loadActionRequest(self::REGISTRATION_REQUEST_BUCKET, (string) $username);
        if (is_array($request)) {
            if ($this->isRegistrationRequestActive($request)) {
                return true;
            }

            $this->_deleteRegistrationVerification($username);
            return false;
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
        foreach ($this->loadAllActionRequests(self::REGISTRATION_REQUEST_BUCKET) as $request) {
            if (! hash_equals((string) ($request['registration_email'] ?? ''), (string) $email)) {
                continue;
            }

            if ($this->isRegistrationRequestActive($request)) {
                return true;
            }

            $this->_deleteRegistrationVerification((string) ($request['registration_account'] ?? ''));
            return false;
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

    private function _generateAccountRecoveryLink($userid, $recovery_code): string
    {
        return __BASE_URL__ . 'forgotpassword/?ui=' . rawurlencode((string) $userid) . '&key=' . rawurlencode((string) $recovery_code);
    }

    private function moduleConfig(string $name): array
    {
        $config = BootstrapContext::configProvider()?->moduleConfig($name);

        return is_array($config) ? $config : [];
    }

    /**
     * @return array<string,mixed>
     */
    private function validatePasswordRecoveryRequest($ui, $key, $ue = null): array
    {
        if (! Validator::hasValue($ui) || ! Validator::hasValue($key)) {
            throw new \Exception(Translator::phrase('error_31'));
        }
        if (! Validator::UnsignedNumber((string) $ui)) {
            throw new \Exception(Translator::phrase('error_31'));
        }

        $request = $this->loadActionRequest(self::PASSWORD_RECOVERY_BUCKET, (string) $ui);
        if (! is_array($request) || ! $this->isPasswordRecoveryRequestActive($request)) {
            $this->actionStore()->delete(self::PASSWORD_RECOVERY_BUCKET, (string) $ui);
            throw new \Exception(Translator::phrase('error_31'));
        }
        if (! $this->opaqueTokenMatches((string) ($request['token_hash'] ?? ''), (string) $key)) {
            throw new \Exception(Translator::phrase('error_31'));
        }

        $accountData = $this->accountInformation($ui);
        if (! is_array($accountData)) {
            throw new \Exception(Translator::phrase('error_31'));
        }

        if (Validator::hasValue($ue) && ! hash_equals((string) $accountData[_CLMN_EMAIL_], (string) $ue)) {
            throw new \Exception(Translator::phrase('error_31'));
        }

        return $accountData;
    }

    private function createPasswordRecoveryRequest(string $userId, string $email, string $token): bool
    {
        return $this->saveActionRequest(self::PASSWORD_RECOVERY_BUCKET, $userId, [
            'user_id'      => $userId,
            'email'        => $email,
            'token_hash'   => $this->hashOpaqueToken($token),
            'request_date' => time(),
        ]);
    }

    private function isPasswordRecoveryRequestActive(array $request): bool
    {
        $requestDate = (int) ($request['request_date'] ?? 0);
        return $requestDate > 0 && time() < ($requestDate + $this->passwordRecoveryTimeout());
    }

    private function passwordRecoveryTimeout(): int
    {
        return max(1, (int) ($this->moduleConfig('forgot-password')['recovery_request_timeout'] ?? 1)) * 3600;
    }

    private function storeEmailChangeRequest(string $accountId, string $newEmail, string $token): bool
    {
        return $this->saveActionRequest(self::EMAIL_CHANGE_BUCKET, $accountId, [
            'account_id'   => $accountId,
            'new_email'    => $newEmail,
            'token_hash'   => $this->hashOpaqueToken($token),
            'request_date' => time(),
        ]);
    }

    private function isEmailChangeRequestActive(array $request): bool
    {
        $requestDate = (int) ($request['request_date'] ?? 0);
        return $requestDate > 0 && time() < ($requestDate + 24 * 3600);
    }

    private function isRegistrationRequestActive(array $request): bool
    {
        $requestDate = (int) ($request['registration_date'] ?? 0);
        return $requestDate > 0 && time() < ($requestDate + $this->registrationVerificationTimeout());
    }

    private function registrationVerificationTimeout(): int
    {
        return max(1, (int) ($this->moduleConfig('register')['verification_timelimit'] ?? 24)) * 3600;
    }
}
