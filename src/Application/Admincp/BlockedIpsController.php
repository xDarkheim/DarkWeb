<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\Auth\Common;
use Darkheim\Infrastructure\View\ViewRenderer;

final class BlockedIpsController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $common = new Common();
        $module = (string) ($_REQUEST['module'] ?? 'blockedips');

        if (isset($_POST['submit_block'], $_POST['ip_address'])) {
            if ($common->blockIpAddress($_POST['ip_address'], $_SESSION['username'])) {
                message('success', 'IP address blocked.');
            } else {
                message('error', 'Error blocking IP.');
            }
        }

        if (isset($_GET['unblock'])) {
            if ($common->unblockIpAddress($_REQUEST['unblock'])) {
                message('success', 'IP address unblocked.');
            } else {
                message('error', 'Error unblocking IP.');
            }
        }

        $rows       = [];
        $blockedIPs = $common->retrieveBlockedIPs();
        if (is_array($blockedIPs)) {
            foreach ($blockedIPs as $ip) {
                $rows[] = [
                    'ip'          => (string) ($ip['block_ip'] ?? ''),
                    'blockedBy'   => (string) ($ip['block_by'] ?? ''),
                    'date'        => date('Y-m-d H:i', (int) ($ip['block_date'] ?? 0)),
                    'unblockUrl'  => admincp_base($module . '&unblock=' . ($ip['id'] ?? '')),
                ];
            }
        }

        $this->view->render('admincp/blockedips', [
            'rows' => $rows,
        ]);
    }
}

