<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\Account\Account;
use Darkheim\Infrastructure\View\ViewRenderer;

final class OnlineAccountsController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $account = new Account();
        $serverList = $account->getServerList();
        $statBoxes = [];

        if (is_array($serverList)) {
            foreach ($serverList as $server) {
                $serverName = (string) $server;
                $statBoxes[] = [
                    'value' => number_format($account->getOnlineAccountCount($serverName)),
                    'label' => $serverName,
                    'accent' => false,
                ];
            }
        }

        $statBoxes[] = [
            'value' => number_format($account->getOnlineAccountCount()),
            'label' => 'Total Online',
            'accent' => true,
        ];

        $rows = [];
        $onlineAccounts = $account->getOnlineAccountList();
        if (is_array($onlineAccounts)) {
            foreach ($onlineAccounts as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $memberId = (string) ($row[_CLMN_MS_MEMBID_] ?? '');
                $rows[] = [
                    'accountHtml' => '<a href="' . htmlspecialchars(admincp_base('accountinfo&u=' . $memberId), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($memberId, ENT_QUOTES, 'UTF-8') . '</a>',
                    'ipAddress' => (string) ($row[_CLMN_MS_IP_] ?? ''),
                    'server' => (string) ($row[_CLMN_MS_GS_] ?? ''),
                ];
            }
        }

        $this->view->render('admincp/onlineaccounts', [
            'pageTitle' => 'Online Accounts',
            'statBoxes' => $statBoxes,
            'rows' => $rows,
            'emptyStateText' => 'There are no online accounts.',
        ]);
    }
}

