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
        $common     = new Common();
        $admincpUrl = new AdmincpUrlGenerator();
        $module     = (string) ($_REQUEST['module'] ?? 'blockedips');

        if (isset($_POST['submit_block'], $_POST['ip_address'])) {
            if ($common->blockIpAddress($_POST['ip_address'], $_SESSION['username'])) {
                \Darkheim\Application\View\MessageRenderer::toast('success', 'IP address blocked.');
            } else {
                \Darkheim\Application\View\MessageRenderer::toast('error', 'Error blocking IP.');
            }
        }

        if (isset($_GET['unblock'])) {
            if ($common->unblockIpAddress($_REQUEST['unblock'])) {
                \Darkheim\Application\View\MessageRenderer::toast('success', 'IP address unblocked.');
            } else {
                \Darkheim\Application\View\MessageRenderer::toast('error', 'Error unblocking IP.');
            }
        }

        $rows       = [];
        $blockedIPs = $common->retrieveBlockedIPs();
        if (is_array($blockedIPs)) {
            foreach ($blockedIPs as $ip) {
                $rows[] = [
                    'ip'         => (string) ($ip['block_ip'] ?? ''),
                    'blockedBy'  => (string) ($ip['block_by'] ?? ''),
                    'date'       => date('Y-m-d H:i', (int) ($ip['block_date'] ?? 0)),
                    'unblockUrl' => $admincpUrl->base($module . '&unblock=' . ($ip['id'] ?? '')),
                ];
            }
        }

        $this->view->render('admincp/blockedips', [
            'rows' => $rows,
        ]);
    }
}
