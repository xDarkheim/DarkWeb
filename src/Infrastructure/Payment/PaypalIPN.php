<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Payment;

use Darkheim\Infrastructure\Runtime\Contracts\PostStore;
use Darkheim\Infrastructure\Runtime\Native\NativePostStore;

/**
 * PaypalIPN — verifies PayPal Instant Payment Notifications via cURL postback.
 * PSR-4 port of the legacy PaypalIPN classmap class.
 */
class PaypalIPN
{
    /** @var bool Use sandbox endpoint for testing. */
    private bool $use_sandbox = false;
    /** @var bool Use bundled CA certificate instead of system certs. */
    private bool $use_local_certs = true;
    /** @var string Path to the CA certificate PEM file used for IPN verification. */
    private string $cert_path;
    private PostStore $post;

    public const string VERIFY_URI         = 'https://ipnpb.paypal.com/cgi-bin/webscr';
    public const string SANDBOX_VERIFY_URI = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

    public const string VALID   = 'VERIFIED';
    public const string INVALID = 'INVALID';

    public function __construct(?PostStore $post = null)
    {
        $this->post      = $post ?? new NativePostStore();
        $this->cert_path = $this->_defaultCertPath();
    }

    /**
     * Switch to the PayPal sandbox endpoint (for testing only).
     */
    public function useSandbox(): void
    {
        $this->use_sandbox = true;
    }

    public function getPaypalUri(): string
    {
        return $this->use_sandbox ? self::SANDBOX_VERIFY_URI : self::VERIFY_URI;
    }

    /**
     * Sends the incoming POST data back to PayPal for IPN verification.
     *
     * @return bool True when PayPal confirms VERIFIED.
     * @throws \Exception on cURL / HTTP errors.
     */
    public function verifyIPN(): bool
    {
        if ($this->post->count() === 0) {
            throw new \Exception('Missing POST Data');
        }

        $raw_post_data  = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost         = [];

        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                // Preserve '+' in payment_date
                if ($keyval[0] === 'payment_date' && substr_count($keyval[1], '+') === 1) {
                    $keyval[1] = str_replace('+', '%2B', $keyval[1]);
                }
                $myPost[$keyval[0]] = urldecode($keyval[1]);
            }
        }

        // Build verification body
        $req = 'cmd=_notify-validate';
        foreach ($myPost as $key => $value) {
            $req .= '&' . $key . '=' . urlencode($value);
        }

        $ch = curl_init($this->getPaypalUri());
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if ($this->use_local_certs) {
            curl_setopt($ch, CURLOPT_CAINFO, $this->cert_path);
        }

        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: PHP-IPN-Verification-Script',
            'Connection: Close',
        ]);

        $res = curl_exec($ch);
        if (! $res) {
            $errno  = curl_errno($ch);
            $errstr = curl_error($ch);
            curl_close($ch);
            throw new \Exception("cURL error: [$errno] $errstr");
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code != 200) {
            throw new \Exception("PayPal responded with http code $http_code");
        }

        return $res === self::VALID;
    }

    /**
     * Resolves the bundled certificate path.
     * Falls back to the legacy location under includes/classes/paypal/cert/.
     */
    private function _defaultCertPath(): string
    {
        // PSR-4 file lives at src/Infrastructure/Payment/ — walk up 3 levels to project root.
        $projectRoot = dirname(__DIR__, 3);
        $bundled     = $projectRoot . '/includes/paypal/cert/cacert.pem';
        if (is_file($bundled)) {
            return $bundled;
        }
        $legacy = $projectRoot . '/includes/classes/paypal/cert/cacert.pem';
        if (is_file($legacy)) {
            return $legacy;
        }
        // Fallback: cert next to this file (for custom deploys that copy it here).
        return __DIR__ . '/cert/cacert.pem';
    }
}
