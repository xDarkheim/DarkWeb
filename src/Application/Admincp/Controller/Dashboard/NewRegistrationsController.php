<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp\Controller\Dashboard;

use Darkheim\Application\Admincp\Layout\AdmincpUrlGenerator;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\View\ViewRenderer;

final class NewRegistrationsController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $database      = Connection::Database('MuOnline');
        $admincpUrl    = new AdmincpUrlGenerator();
        $registrations = $database->query_fetch(
            'SELECT TOP 200 ' . _CLMN_MEMBID_ . ', ' . _CLMN_USERNM_ . ', ' . _CLMN_EMAIL_ . ' FROM ' . _TBL_MI_ . ' ORDER BY ' . _CLMN_MEMBID_ . ' DESC',
        );

        $rows = [];
        if (is_array($registrations)) {
            foreach ($registrations as $registration) {
                if (! is_array($registration)) {
                    continue;
                }

                $memberId = (string) ($registration[_CLMN_MEMBID_] ?? '');
                $rows[]   = [
                    'id'             => $memberId,
                    'username'       => (string) ($registration[_CLMN_USERNM_] ?? ''),
                    'email'          => (string) ($registration[_CLMN_EMAIL_] ?? ''),
                    'accountInfoUrl' => $admincpUrl->base('accountinfo&id=' . $memberId),
                ];
            }
        }

        $this->view->render('admincp/newregistrations', [
            'pageTitle'      => 'New Registrations',
            'cardTitle'      => 'Latest 200 Registrations',
            'rows'           => $rows,
            'emptyStateText' => 'No registrations found.',
        ]);
    }
}
