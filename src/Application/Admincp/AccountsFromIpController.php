<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\Auth\Common;
use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\View\ViewRenderer;

final class AccountsFromIpController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $ipAddress = '';
        $results   = null;
        $error     = null;

        if (isset($_POST['ip_address'])) {
            $ipAddress = (string) $_POST['ip_address'];
            try {
                if (!Validator::Ip($ipAddress)) {
                    throw new \RuntimeException('You have entered an invalid IP address.');
                }
                $db     = Connection::Database('MuOnline');
                $common = new Common();
                $rows   = $db->query_fetch(
                    'SELECT ' . _CLMN_MS_MEMBID_ . ' FROM ' . _TBL_MS_ . ' WHERE ' . _CLMN_MS_IP_ . ' = ? GROUP BY ' . _CLMN_MS_MEMBID_,
                    [$ipAddress]
                );
                $results = [];
                if (is_array($rows)) {
                    foreach ($rows as $u) {
                        $membId   = (string) ($u[_CLMN_MS_MEMBID_] ?? '');
                        $results[] = [
                            'account'        => $membId,
                            'accountInfoUrl' => admincp_base('accountinfo&id=' . $common->retrieveUserID($membId)),
                        ];
                    }
                }
            } catch (\Exception $ex) {
                $error = $ex->getMessage();
            }
        }

        $this->view->render('admincp/accountsfromip', [
            'ipAddress' => $ipAddress,
            'results'   => $results,
            'error'     => $error,
        ]);
    }
}

