<?php

declare(strict_types=1);

namespace Darkheim\Application\Auth;

use Darkheim\Domain\Validation\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\Security\OneTimeActionStore;

/**
 * Common base class — shared account/auth utilities used by child classes.
 */
class Common
{
    protected const string PASSWORD_CHANGE_REQUEST_BUCKET = 'password-change-requests';

    protected $_passwordEncryption;
    protected $_sha256salt;
    protected $_debug = false;

    protected $muonline;
    private ?OneTimeActionStore $oneTimeActionStore = null;

    public function __construct()
    {
        $this->muonline            = Connection::Database('MuOnline');
        $this->_passwordEncryption = BootstrapContext::cmsValue('SQL_PASSWORD_ENCRYPTION', true);
        $this->_sha256salt         = BootstrapContext::cmsValue('SQL_SHA256_SALT', true);
        $this->_debug              = BootstrapContext::cmsValue('error_reporting', true);
    }

    public function emailExists($email)
    {
        if (! Validator::Email($email)) {
            return;
        }
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . _TBL_MI_ . " WHERE " . _CLMN_EMAIL_ . " = ?", [$email]);
        if (is_array($result)) {
            return true;
        }
    }

    public function userExists($username)
    {
        if (! Validator::UsernameLength($username)) {
            return;
        }
        if (! Validator::AlphaNumeric($username)) {
            return;
        }
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . _TBL_MI_ . " WHERE " . _CLMN_USERNM_ . " = ?", [$username]);
        if (is_array($result)) {
            return true;
        }
    }

    public function validateUser($username, $password)
    {
        if (! Validator::UsernameLength($username)) {
            return;
        }
        if (! Validator::AlphaNumeric($username)) {
            return;
        }
        if (! Validator::PasswordLength($password)) {
            return;
        }

        $data = ['username' => $username, 'password' => $password];

        switch ($this->passwordEncryptionMode()) {
            case 'none':
                $query = "SELECT * FROM " . _TBL_MI_ . " WHERE " . _CLMN_USERNM_ . " = :username AND " . _CLMN_PASSWD_ . " = :password";
                break;
            case 'wzmd5':
                // noinspection SqlNoDataSourceInspection — fn_md5 is a MSSQL server-side stored function
                $query = /** @lang TSQL */ "SELECT * FROM " . _TBL_MI_ . " WHERE " . _CLMN_USERNM_ . " = :username AND " . _CLMN_PASSWD_ . " = [dbo].[fn_md5](:password, :username)";
                break;
            case 'phpmd5':
                $data['password'] = md5($password);
                $query            = "SELECT * FROM " . _TBL_MI_ . " WHERE " . _CLMN_USERNM_ . " = :username AND " . _CLMN_PASSWD_ . " = :password";
                break;
            case 'sha256':
                $data['password'] = $password . $username . $this->_sha256salt;
                $query            = "SELECT * FROM " . _TBL_MI_ . " WHERE " . _CLMN_USERNM_ . " = :username AND " . _CLMN_PASSWD_ . " = HASHBYTES('SHA2_256', CAST(:password AS VARCHAR(MAX)))";
                break;
            default:
                throw new \RuntimeException('Unsupported password encryption setting.');
        }

        $result = $this->muonline->query_fetch_single($query, $data);
        return is_array($result);
    }

    public function retrieveUserID($username)
    {
        if (! Validator::UsernameLength($username)) {
            return;
        }
        if (! Validator::AlphaNumeric($username)) {
            return;
        }
        $result = $this->muonline->query_fetch_single("SELECT " . _CLMN_MEMBID_ . " FROM " . _TBL_MI_ . " WHERE " . _CLMN_USERNM_ . " = ?", [$username]);
        if (is_array($result)) {
            return $result[_CLMN_MEMBID_];
        }
    }

    public function retrieveUserIDbyEmail($email)
    {
        if (! $this->emailExists($email)) {
            return;
        }
        $result = $this->muonline->query_fetch_single("SELECT " . _CLMN_MEMBID_ . " FROM " . _TBL_MI_ . " WHERE " . _CLMN_EMAIL_ . " = ?", [$email]);
        if (is_array($result)) {
            return $result[_CLMN_MEMBID_];
        }
    }

    public function accountInformation($id)
    {
        if (! Validator::Number($id)) {
            return;
        }
        $result = $this->muonline->query_fetch_single("SELECT * FROM " . _TBL_MI_ . " WHERE " . _CLMN_MEMBID_ . " = ?", [$id]);
        if (is_array($result)) {
            return $result;
        }
    }

    public function accountOnline($username)
    {
        if (! Validator::UsernameLength($username)) {
            return;
        }
        if (! Validator::AlphaNumeric($username)) {
            return;
        }
        $result = $this->muonline->query_fetch_single("SELECT " . _CLMN_CONNSTAT_ . " FROM " . _TBL_MS_ . " WHERE " . _CLMN_USERNM_ . " = ? AND " . _CLMN_CONNSTAT_ . " = ?", [$username, 1]);
        if (is_array($result)) {
            return true;
        }
    }

    public function changePassword($id, $username, $new_password)
    {
        if (! Validator::UnsignedNumber($id)) {
            return;
        }
        if (! Validator::UsernameLength($username)) {
            return;
        }
        if (! Validator::AlphaNumeric($username)) {
            return;
        }
        if (! Validator::PasswordLength($new_password)) {
            return;
        }

        $encodedPassword = $this->encodePasswordForStorage((string) $username, (string) $new_password);
        return $this->updatePasswordWithEncodedValue($id, $encodedPassword, null);
    }

    public function addPasswordChangeRequest($userid, $new_password, $auth_code, $username = null)
    {
        if (! Validator::hasValue($userid)) {
            return;
        }
        if (! Validator::hasValue($new_password)) {
            return;
        }
        if (! Validator::hasValue($auth_code)) {
            return;
        }
        if (! Validator::PasswordLength($new_password)) {
            return;
        }

        if (Validator::hasValue($username)) {
            return $this->storePasswordChangeRequest((string) $userid, (string) $username, (string) $new_password, (string) $auth_code);
        }

        $data   = [$userid, $new_password, $auth_code, time()];
        $query  = "INSERT INTO " . Passchange_Request . " (user_id,new_password,auth_code,request_date) VALUES (?, ?, ?, ?)";
        $result = $this->muonline->query($query, $data);
        if ($result) {
            return true;
        }
    }

    public function hasActivePasswordChangeRequest($userid)
    {
        if (! Validator::hasValue($userid)) {
            return;
        }

        $storedRequest = $this->loadActionRequest(self::PASSWORD_CHANGE_REQUEST_BUCKET, (string) $userid);
        if (is_array($storedRequest)) {
            if ($this->isPasswordChangeRequestActive($storedRequest)) {
                return true;
            }

            $this->removePasswordChangeRequest($userid);
            return;
        }

        $result = $this->muonline->query_fetch_single("SELECT * FROM " . Passchange_Request . " WHERE user_id = ?", [$userid]);
        if (! is_array($result)) {
            return;
        }
        $request_date = (int) ($result['request_date'] ?? 0) + $this->passwordChangeRequestTimeout();
        if (time() < $request_date) {
            return true;
        }
        $this->removePasswordChangeRequest($userid);
    }

    public function removePasswordChangeRequest($userid)
    {
        $storedRequest = $this->loadActionRequest(self::PASSWORD_CHANGE_REQUEST_BUCKET, (string) $userid);
        if (is_array($storedRequest)) {
            if ($this->actionStore()->delete(self::PASSWORD_CHANGE_REQUEST_BUCKET, (string) $userid)) {
                return true;
            }
        }

        $result = $this->muonline->query("DELETE FROM " . Passchange_Request . " WHERE user_id = ?", [$userid]);
        if ($result) {
            return true;
        }
    }

    public function generatePasswordChangeVerificationURL($user_id, $auth_code): string
    {
        return __BASE_URL__ . 'verifyemail/?op=1&uid=' . $user_id . '&key=' . $auth_code;
    }

    public function generateAccountRecoveryCode(): string
    {
        return $this->generateOpaqueToken();
    }

    public function updateEmail($userid, $newEmail)
    {
        if (! Validator::hasValue($userid)) {
            return;
        }
        if (! Validator::Email($newEmail)) {
            return;
        }
        $result = $this->muonline->query("UPDATE " . _TBL_MI_ . " SET " . _CLMN_EMAIL_ . " = ? WHERE " . _CLMN_MEMBID_ . " = ?", [$newEmail, $userid]);
        if ($result) {
            return true;
        }
    }

    public function paypal_transaction($transactionId, $userId, $paymentAmount, $paypalEmail, $orderId): bool
    {
        if (! Validator::hasValue($transactionId) || ! Validator::UnsignedNumber($userId) || ! Validator::hasValue($paypalEmail) || ! Validator::hasValue($orderId)) {
            return false;
        }

        return $this->muonline->query(
            'INSERT INTO ' . PayPal_Transactions . ' (transaction_id, user_id, payment_amount, paypal_email, transaction_date, transaction_status, order_id) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                (string) $transactionId,
                (int) $userId,
                (string) $paymentAmount,
                (string) $paypalEmail,
                time(),
                1,
                (string) $orderId,
            ],
        );
    }

    public function paypal_transaction_reversed_updatestatus($orderId): bool
    {
        if (! Validator::hasValue($orderId)) {
            return false;
        }

        return $this->muonline->query(
            'UPDATE ' . PayPal_Transactions . ' SET transaction_status = ? WHERE order_id = ?',
            [0, (string) $orderId],
        );
    }

    public function blockAccount($userId): bool
    {
        if (! Validator::UnsignedNumber($userId)) {
            return false;
        }

        return $this->muonline->query(
            'UPDATE ' . _TBL_MI_ . ' SET ' . _CLMN_BLOCCODE_ . ' = ? WHERE ' . _CLMN_MEMBID_ . ' = ?',
            [1, (int) $userId],
        );
    }

    public function retrieveBlockedIPs(): array|false
    {
        $result = $this->muonline->query_fetch(
            "SELECT * FROM " . Blocked_IP . " ORDER BY block_date DESC",
        );
        return is_array($result) ? $result : false;
    }

    public function blockIpAddress(string $ip, string $blockedBy): bool
    {
        if (! Validator::hasValue($ip)) {
            return false;
        }
        if (! Validator::hasValue($blockedBy)) {
            return false;
        }

        return $this->muonline->query(
            "INSERT INTO " . Blocked_IP . " (block_ip, block_by, block_date) VALUES (?, ?, ?)",
            [$ip, $blockedBy, time()],
        );
    }

    public function unblockIpAddress($id): bool
    {
        if (! Validator::Number($id)) {
            return false;
        }

        return $this->muonline->query("DELETE FROM " . Blocked_IP . " WHERE id = ?", [$id]);
    }

    public static function supportedPasswordEncryptionModes(): array
    {
        return ['none', 'wzmd5', 'phpmd5', 'sha256'];
    }

    protected function passwordEncryptionMode(?string $mode = null): string
    {
        $normalized = strtolower((string) ($mode ?? $this->_passwordEncryption));
        if (! in_array($normalized, self::supportedPasswordEncryptionModes(), true)) {
            throw new \RuntimeException('Unsupported password encryption setting.');
        }

        return $normalized;
    }

    protected function encodePasswordForStorage(string $username, string $password, ?string $mode = null): string
    {
        return match ($this->passwordEncryptionMode($mode)) {
            'none'   => $password,
            'wzmd5'  => $this->encodeWzMd5Password($username, $password),
            'phpmd5' => md5($password),
            'sha256' => '0x' . hash('sha256', $password . $username . $this->_sha256salt),
            default  => throw new \RuntimeException('Unsupported password encryption setting.'),
        };
    }

    protected function updatePasswordWithEncodedValue(int|string $userId, string $encodedPassword, ?string $mode = null): bool
    {
        return match ($this->passwordEncryptionMode($mode)) {
            'none', 'wzmd5', 'phpmd5' => $this->muonline->query(
                "UPDATE " . _TBL_MI_ . " SET " . _CLMN_PASSWD_ . " = :password WHERE " . _CLMN_MEMBID_ . " = :userid",
                ['userid' => $userId, 'password' => $encodedPassword],
            ),
            'sha256' => $this->muonline->query(
                "UPDATE " . _TBL_MI_ . " SET " . _CLMN_PASSWD_ . " = CONVERT(binary(32),:password,1) WHERE " . _CLMN_MEMBID_ . " = :userid",
                ['userid' => $userId, 'password' => $encodedPassword],
            ),
            default => throw new \RuntimeException('Unsupported password encryption setting.'),
        };
    }

    protected function insertAccountWithEncodedPassword(string $username, string $encodedPassword, string $email, ?string $mode = null): bool
    {
        $data = [
            'username' => $username,
            'password' => $encodedPassword,
            'name'     => $username,
            'serial'   => '1111111111111',
            'email'    => $email,
        ];

        $query = match ($this->passwordEncryptionMode($mode)) {
            'none', 'wzmd5', 'phpmd5' => "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, :password, :name, :serial, :email, 0, 0)",
            'sha256' => "INSERT INTO " . _TBL_MI_ . " (" . _CLMN_USERNM_ . ", " . _CLMN_PASSWD_ . ", " . _CLMN_MEMBNAME_ . ", " . _CLMN_SNONUMBER_ . ", " . _CLMN_EMAIL_ . ", " . _CLMN_BLOCCODE_ . ", " . _CLMN_CTLCODE_ . ") VALUES (:username, CONVERT(binary(32),:password,1), :name, :serial, :email, 0, 0)",
            default => throw new \RuntimeException('Unsupported password encryption setting.'),
        };

        return $this->muonline->query($query, $data);
    }

    protected function actionStore(): OneTimeActionStore
    {
        if (! $this->oneTimeActionStore instanceof OneTimeActionStore) {
            $this->oneTimeActionStore = new OneTimeActionStore();
        }

        return $this->oneTimeActionStore;
    }

    /**
     * @param array<string,mixed> $payload
     */
    protected function saveActionRequest(string $bucket, string $key, array $payload): bool
    {
        return $this->actionStore()->save($bucket, $key, $payload);
    }

    /**
     * @return array<string,mixed>|null
     */
    protected function loadActionRequest(string $bucket, string $key): ?array
    {
        return $this->actionStore()->load($bucket, $key);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    protected function loadAllActionRequests(string $bucket): array
    {
        return $this->actionStore()->all($bucket);
    }

    protected function generateOpaqueToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    protected function hashOpaqueToken(string $token): string
    {
        return hash('sha256', $token);
    }

    protected function opaqueTokenMatches(string $expectedHash, string $token): bool
    {
        return hash_equals($expectedHash, $this->hashOpaqueToken($token));
    }

    protected function storePasswordChangeRequest(string $userId, string $username, string $newPassword, string $authCode): bool
    {
        $encodedPassword = $this->encodePasswordForStorage($username, $newPassword);

        return $this->saveActionRequest(self::PASSWORD_CHANGE_REQUEST_BUCKET, $userId, [
            'user_id'          => $userId,
            'username'         => $username,
            'encoded_password' => $encodedPassword,
            'password_mode'    => $this->passwordEncryptionMode(),
            'token_hash'       => $this->hashOpaqueToken($authCode),
            'request_date'     => time(),
        ]);
    }

    /**
     * @return array<string,mixed>|null
     */
    protected function loadPasswordChangeRequest(string $userId): ?array
    {
        return $this->loadActionRequest(self::PASSWORD_CHANGE_REQUEST_BUCKET, $userId);
    }

    protected function isPasswordChangeRequestActive(array $request): bool
    {
        $requestDate = (int) ($request['request_date'] ?? 0);
        return $requestDate > 0 && time() < ($requestDate + $this->passwordChangeRequestTimeout());
    }

    protected function passwordChangeRequestTimeout(): int
    {
        $configs = BootstrapContext::configProvider()?->moduleConfig('my-password');
        if (! is_array($configs)) {
            return 12 * 3600;
        }

        return max(1, (int) ($configs['change_password_request_timeout'] ?? 12)) * 3600;
    }

    private function encodeWzMd5Password(string $username, string $password): string
    {
        $result = $this->muonline->query_fetch_single(
            /** @lang TSQL */
            "SELECT [dbo].[fn_md5](?, ?) AS password_hash",
            [$password, $username],
        );

        if (! is_array($result)) {
            throw new \RuntimeException('Could not hash password.');
        }

        $hash = $result['password_hash'] ?? reset($result);
        if (! is_string($hash) || $hash === '') {
            throw new \RuntimeException('Could not hash password.');
        }

        return $hash;
    }
}
