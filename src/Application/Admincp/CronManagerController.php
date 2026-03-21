<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Infrastructure\Cron\CronManager;
use Darkheim\Infrastructure\View\ViewRenderer;

final class CronManagerController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        try {
            $cronManager = new CronManager();

            if (isset($_GET['action'])) {
                try {
                    switch ($_GET['action']) {
                        case 'enable':    $cronManager->setId($_GET['id']); $cronManager->enableCron(); break;
                        case 'disable':   $cronManager->setId($_GET['id']); $cronManager->disableCron(); break;
                        case 'delete':    $cronManager->setId($_GET['id']); $cronManager->deleteCron(); break;
                        case 'reset':     $cronManager->setId($_GET['id']); $cronManager->resetCronLastRun(); break;
                        case 'allenable':  $cronManager->enableAll(); break;
                        case 'alldisable': $cronManager->disableAll(); break;
                        case 'allreset':   $cronManager->resetAllLastRun(); break;
                        default: throw new \RuntimeException('Invalid action.');
                    }
                    redirect(3, admincp_base('cronmanager'));
                } catch (\Exception $ex) {
                    message('error', $ex->getMessage());
                }
            }

            if (isset($_POST['submit'])) {
                try {
                    $cronManager->_name     = $_POST['cron_name'];
                    $cronManager->_interval = $_POST['cron_time'];
                    $cronManager->setFile($_POST['cron_file']);
                    $cronManager->addCron();
                    redirect(3, admincp_base('cronmanager'));
                } catch (\Exception $ex) {
                    message('error', $ex->getMessage());
                }
            }

            $cronList = $cronManager->getCronList();
            $rows     = [];
            if (is_array($cronList)) {
                foreach ($cronList as $row) {
                    $interval = sec_to_hms($row['cron_run_time']);
                    $rows[]   = [
                        'id'          => (string) ($row['cron_id'] ?? ''),
                        'name'        => (string) ($row['cron_name'] ?? ''),
                        'file'        => (string) ($row['cron_file_run'] ?? ''),
                        'interval'    => $interval[0] . 'h ' . $interval[1] . 'm',
                        'lastRun'     => check_value($row['cron_last_run']) ? date('Y-m-d H:i', (int) $row['cron_last_run']) : null,
                        'isOn'        => (int) ($row['cron_status'] ?? 0) === 1,
                        'protected'   => (bool) ($row['cron_protected'] ?? false),
                        'enableUrl'   => admincp_base('cronmanager&action=enable&id=' . ($row['cron_id'] ?? '')),
                        'disableUrl'  => admincp_base('cronmanager&action=disable&id=' . ($row['cron_id'] ?? '')),
                        'resetUrl'    => admincp_base('cronmanager&action=reset&id=' . ($row['cron_id'] ?? '')),
                        'deleteUrl'   => admincp_base('cronmanager&action=delete&id=' . ($row['cron_id'] ?? '')),
                    ];
                }
            }

            $intervalOptions = [];
            if (is_array($cronManager->_commonIntervals)) {
                foreach ($cronManager->_commonIntervals as $s => $d) {
                    $intervalOptions[] = ['value' => (string) $s, 'label' => (string) $d];
                }
            } else {
                $intervalOptions[] = ['value' => '300', 'label' => '5 Minutes'];
            }

            $this->view->render('admincp/cronmanager', [
                'rows'            => $rows,
                'intervalOptions' => $intervalOptions,
                'cronFilesHtml'   => $cronManager->listCronFiles(),
                'addUrl'          => admincp_base('cronmanager'),
                'bulkEnableUrl'   => admincp_base('cronmanager&action=allenable'),
                'bulkDisableUrl'  => admincp_base('cronmanager&action=alldisable'),
                'bulkResetUrl'    => admincp_base('cronmanager&action=allreset'),
            ]);
        } catch (\Exception $ex) {
            message('error', $ex->getMessage());
        }
    }
}

