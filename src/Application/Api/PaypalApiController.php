<?php

declare(strict_types=1);

namespace Darkheim\Application\Api;

use Darkheim\Application\Account\Account;
use Darkheim\Application\Auth\Common;
use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Payment\PaypalIPN;

final class PaypalApiController
{
    public function render(): void
    {
        $cfg = loadConfigurations('donation-paypal');
        if (!is_array($cfg)) {
            header('HTTP/1.1 500 Internal Server Error');
            return;
        }

        $enableSandbox = $cfg['paypal_enable_sandbox'];
        $sellerEmail = $cfg['paypal_email'];

        $ipn = new PaypalIPN();
        if ($enableSandbox == 1) {
            $ipn->useSandbox();
        }

        $verified = $ipn->verifyIPN();
        $paypalIpnStatus = 'VERIFICATION FAILED';

        if ($verified) {
            $paypalIpnStatus = $this->handleVerifiedPayment((string) $sellerEmail, $cfg);
        } elseif ($enableSandbox) {
            if (($_POST['test_ipn'] ?? null) != 1) {
                $paypalIpnStatus = 'RECEIVED FROM LIVE WHILE SANDBOXED';
            }
        } elseif (($_POST['test_ipn'] ?? null) == 1) {
            $paypalIpnStatus = 'RECEIVED FROM SANDBOX WHILE LIVE';
        }

        header('HTTP/1.1 200 OK');
    }

    /**
     * @param array<string,mixed> $cfg
     */
    private function handleVerifiedPayment(string $sellerEmail, array $cfg): string
    {
        try {
            if (strtolower((string) ($_POST['receiver_email'] ?? '')) !== strtolower($sellerEmail)) {
                throw new \RuntimeException('RECEIVER EMAIL MISMATCH');
            }

            $common = new Common();
            $itemNumber = (string) ($_POST['item_number'] ?? '');
            $paymentAmount = (float) ($_POST['mc_gross'] ?? 0);
            $txnId = (string) ($_POST['txn_id'] ?? '');
            $payerEmail = (string) ($_POST['payer_email'] ?? '');
            $userId = (string) ($_POST['custom'] ?? '');

            try {
                if (($_POST['payment_status'] ?? '') === 'Completed') {
                    $this->completePayment($cfg, $userId, $paymentAmount);
                    $common->paypal_transaction($txnId, $userId, $paymentAmount, $payerEmail, $itemNumber);
                    return 'Completed Successfully';
                }

                $common->blockAccount($userId);
                $common->paypal_transaction_reversed_updatestatus($itemNumber);
                return 'VERIFICATION FAILED';
            } catch (\Exception $ex) {
                return $ex->getMessage();
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * @param array<string,mixed> $cfg
     */
    private function completePayment(array $cfg, string $userId, float $paymentAmount): void
    {
        $addCredits = (int) floor($paymentAmount * (float) $cfg['paypal_conversion_rate']);
        if (!Validator::UnsignedNumber($userId)) {
            throw new \RuntimeException('invalid userid');
        }

        $account = new Account();
        $accountInfo = $account->accountInformation($userId);
        if (!is_array($accountInfo)) {
            throw new \RuntimeException('invalid account');
        }

        $creditSystem = new CreditSystem();
        $creditSystem->setConfigId($cfg['credit_config']);
        $configSettings = $creditSystem->showConfigs(true);
        switch ($configSettings['config_user_col_id']) {
            case 'userid':
                $creditSystem->setIdentifier($accountInfo[_CLMN_MEMBID_]);
                break;
            case 'username':
                $creditSystem->setIdentifier($accountInfo[_CLMN_USERNM_]);
                break;
            case 'email':
                $creditSystem->setIdentifier($accountInfo[_CLMN_EMAIL_]);
                break;
            default:
                throw new \RuntimeException('invalid identifier');
        }

        $_GET['page'] = 'api';
        $_GET['subpage'] = 'paypal';

        $creditSystem->addCredits($addCredits);
    }
}

