<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp\Controller\Dashboard;

use Darkheim\Application\Admincp\Layout\AdmincpUrlGenerator;
use Darkheim\Application\Auth\Common;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\View\ViewRenderer;

final class LatestPaypalController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $rows  = [];
        $error = null;

        try {
            $db         = Connection::Database('MuOnline');
            $admincpUrl = new AdmincpUrlGenerator();
            $common     = new Common();
            $data       = $db->query_fetch('SELECT * FROM ' . PayPal_Transactions . ' ORDER BY id DESC');
            if (! is_array($data)) {
                throw new \RuntimeException('No PayPal transactions found.');
            }
            foreach ($data as $tx) {
                $userData = $common->accountInformation($tx['user_id']);
                $rows[]   = [
                    'transactionId'  => (string) ($tx['transaction_id'] ?? ''),
                    'username'       => (string) ($userData[_CLMN_USERNM_] ?? ''),
                    'accountInfoUrl' => $admincpUrl->base('accountinfo&id=' . ($tx['user_id'] ?? '')),
                    'amount'         => (string) ($tx['payment_amount'] ?? ''),
                    'paypalEmail'    => (string) ($tx['paypal_email'] ?? ''),
                    'date'           => date('Y-m-d H:i', (int) ($tx['transaction_date'] ?? 0)),
                    'statusOk'       => (int) ($tx['transaction_status'] ?? 0) === 1,
                ];
            }
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
        }

        $this->view->render('admincp/latestpaypal', [
            'rows'  => $rows,
            'error' => $error,
        ]);
    }
}
