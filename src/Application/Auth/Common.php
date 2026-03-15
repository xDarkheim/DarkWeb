<?php

declare(strict_types=1);

namespace Darkheim\Application\Auth;

use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Domain\Validator;

/**
 * Common base class — shared account/auth utilities used by child classes.
 */
class Common
{
    protected $_passwordEncryption;
    protected $_sha256salt;
    protected $_debug = false;

    protected $muonline;

    public function __construct()
    {
        $this->muonline = Connection::Database('MuOnline');
        $this->_passwordEncryption = config('SQL_PASSWORD_ENCRYPTION', true);
        $this->_sha256salt          = config('SQL_SHA256_SALT', true);
        $this->_debug               = config('error_reporting', true);
    }

    public function emailExists($email)
    {
        if (!Validator::Email($email)) return;
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . _TBL_MI_ . " WHERE " . _CLMN_EMAIL_ . " = ?", array($email));
        if (is_array($result)) return true;
    }

    public function userExists($username)
    {
        if (!Validator::UsernameLength($username)) return;
        if (!Validator::AlphaNumeric($username)) return;
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . _TBL_MI_ . " WHERE " . _CLMN_USERNM_ . " = ?", array($username));
        if (is_array($result)) return true;
    }

    public function validateUser($username, $password)
    {
        if (!Validator::UsernameLength($username)) return;
        if (!Validator::AlphaNumeric($username)) return;
        if (!Validator::PasswordLength($password)) return;

        $data = ['username' => $username, 'password' => $password];

        switch ($this->_passwordEncryption) {
            case 'wzmd5':
                // noinspection SqlNoDataSourceInspection — fn_md5 is a MSSQL server-side stored function
                $query = /** @lang TSQL */ "SELECT * FROM " . _TBL_MI_ . " WHERE " . _CLMN_USERNM_ . " = :username AND " . _CLMN_PASSWD_ . " = [dbo].[fn_md5](:password, :username)";
                break;
            case 'phpmd5':
                $data['password'] = md5($password);
                $query = "SELECT * FROM " . _TBL_MI_ . " WHERE " . _CLMN_USERNM_ . " = :username AND " . _CLMN_PASSWD_ . " = :password";
                break;
            case 'sha256':
                $data['password'] = $password . $username . $this->_sha256salt;
                $query = "SELECT * FROM " . _TBL_MI_ . " WHERE " . _CLMN_USERNM_ . " = :username AND " . _CLMN_PASSWD_ . " = HASHBYTES('SHA2_256', CAST(:password AS VARCHAR(MAX)))";
                break;
            default:
                $query = "SELECT * FROM " . _TBL_MI_ . " WHERE " . _CLMN_USERNM_ . " = :username AND " . _CLMN_PASSWD_ . " = :password";
        }

        $result = $this->muonline->query_fetch_single($query, $data);
        return is_array($result);
    }

    public function retrieveUserID($username)
    {
        if (!Validator::UsernameLength($username)) return;
        if (!Validator::AlphaNumeric($username)) return;
        $result = $this->muonline->query_fetch_single("SELECT " . _CLMN_MEMBID_ . " FROM " . _TBL_MI_ . " WHERE " . _CLMN_USERNM_ . " = ?", array($username));
        if (is_array($result)) return $result[_CLMN_MEMBID_];
    }

    public function retrieveUserIDbyEmail($email)
    {
        if (!$this->emailExists($email)) return;
        $result = $this->muonline->query_fetch_single("SELECT " . _CLMN_MEMBID_ . " FROM " . _TBL_MI_ . " WHERE " . _CLMN_EMAIL_ . " = ?", array($email));
        if (is_array($result)) return $result[_CLMN_MEMBID_];
    }

    public function accountInformation($id)
    {
        if (!Validator::Number($id)) return;
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . _TBL_MI_ . " WHERE " . _CLMN_MEMBID_ . " = ?", array($id));
        if (is_array($result)) return $result;
    }

    public function accountOnline($username)
    {
        if (!Validator::UsernameLength($username)) return;
        if (!Validator::AlphaNumeric($username)) return;
        $result = $this->muonline->query_fetch_single("SELECT " . _CLMN_CONNSTAT_ . " FROM " . _TBL_MS_ . " WHERE " . _CLMN_USERNM_ . " = ? AND " . _CLMN_CONNSTAT_ . " = ?", array($username, 1));
        if (is_array($result)) return true;
    }

    public function changePassword($id, $username, $new_password)
    {
        if (!Validator::UnsignedNumber($id)) return;
        if (!Validator::UsernameLength($username)) return;
        if (!Validator::AlphaNumeric($username)) return;
        if (!Validator::PasswordLength($new_password)) return;

        switch ($this->_passwordEncryption) {
            case 'wzmd5':
                $data  = ['userid' => $id, 'username' => $username, 'password' => $new_password];
                $query = "UPDATE " . _TBL_MI_ . " SET " . _CLMN_PASSWD_ . " = [dbo].[fn_md5](:password, :username) WHERE " . _CLMN_MEMBID_ . " = :userid";
                break;
            case 'phpmd5':
                $data  = ['userid' => $id, 'password' => md5($new_password)];
                $query = "UPDATE " . _TBL_MI_ . " SET " . _CLMN_PASSWD_ . " = :password WHERE " . _CLMN_MEMBID_ . " = :userid";
                break;
            case 'sha256':
                $data  = ['userid' => $id, 'password' => '0x' . hash('sha256', $new_password . $username . $this->_sha256salt)];
                $query = "UPDATE " . _TBL_MI_ . " SET " . _CLMN_PASSWD_ . " = CONVERT(binary(32),:password,1) WHERE " . _CLMN_MEMBID_ . " = :userid";
                break;
            default:
                $data  = ['userid' => $id, 'password' => $new_password];
                $query = "UPDATE " . _TBL_MI_ . " SET " . _CLMN_PASSWD_ . " = :password WHERE " . _CLMN_MEMBID_ . " = :userid";
        }

        $result = $this->muonline->query($query, $data);
        if ($result) return true;
    }

    public function addPasswordChangeRequest($userid, $new_password, $auth_code)
    {
        if (!check_value($userid)) return;
        if (!check_value($new_password)) return;
        if (!check_value($auth_code)) return;
        if (!Validator::PasswordLength($new_password)) return;

        $data   = [$userid, $new_password, $auth_code, time()];
        $query  = "INSERT INTO " . Passchange_Request . " (user_id,new_password,auth_code,request_date) VALUES (?, ?, ?, ?)";
        $result = $this->muonline->query($query, $data);
        if ($result) return true;
    }

    public function hasActivePasswordChangeRequest($userid)
    {
        if (!check_value($userid)) return;
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . Passchange_Request . " WHERE user_id = ?", array($userid));
        if (!is_array($result)) return;
        $configs = loadConfigurations('usercp.mypassword');
        if (!is_array($configs)) return;
        $request_timeout = $configs['change_password_request_timeout'] * 3600;
        $request_date    = $result['request_date'] + $request_timeout;
        if (time() < $request_date) return true;
        $this->removePasswordChangeRequest($userid);
    }

    public function removePasswordChangeRequest($userid)
    {
        $result = $this->muonline->query("DELETE FROM " . Passchange_Request . " WHERE user_id = ?", array($userid));
        if ($result) return true;
    }

    public function generatePasswordChangeVerificationURL($user_id, $auth_code): string
    {
        return __BASE_URL__ . 'verifyemail/?op=1&uid=' . $user_id . '&key=' . $auth_code;
    }

    public function generateAccountRecoveryCode($user_id, $username): string
    {
        return md5(md5($user_id) . md5($username));
    }

    public function updateEmail($userid, $newEmail)
    {
        if (!check_value($userid)) return;
        if (!Validator::Email($newEmail)) return;
        $result = $this->muonline->query("UPDATE " . _TBL_MI_ . " SET " . _CLMN_EMAIL_ . " = ? WHERE " . _CLMN_MEMBID_ . " = ?", array($newEmail, $userid));
        if ($result) return true;
    }
}

