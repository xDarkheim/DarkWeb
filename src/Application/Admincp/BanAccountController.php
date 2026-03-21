<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\Auth\Common;
use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\View\ViewRenderer;

final class BanAccountController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $db     = Connection::Database('MuOnline');
        $common = new Common();

        // Ensure the temporal-bans cron entry exists
        $checkBanCron = $db->query_fetch_single('SELECT * FROM ' . Cron . " WHERE cron_file_run = ?", ['temporal_bans.php']);
        if (!is_array($checkBanCron)) {
            $db->query(
                "INSERT INTO " . Cron . " (cron_name, cron_description, cron_file_run, cron_run_time, cron_status, cron_protected, cron_file_md5)"
                . " VALUES ('Ban System', 'Scheduled task to lift temporal bans', 'temporal_bans.php', '3600', 1, 1, '1a3787c5179afddd1bfb09befda3d1c7')"
            );
        }

        if (isset($_POST['submit_ban'])) {
            try {
                if (!isset($_POST['ban_account'])) {
                    throw new \RuntimeException('Please enter the account username.');
                }
                if (!$common->userExists($_POST['ban_account'])) {
                    throw new \RuntimeException('Invalid account username.');
                }
                if (!isset($_POST['ban_days'])) {
                    throw new \RuntimeException('Please enter the amount of days.');
                }
                if (!Validator::UnsignedNumber($_POST['ban_days'])) {
                    throw new \RuntimeException('Invalid ban days.');
                }
                if (isset($_POST['ban_reason']) && !Validator::Length($_POST['ban_reason'], 100, 1)) {
                    throw new \RuntimeException('Invalid ban reason.');
                }
                if ($common->accountOnline($_POST['ban_account'])) {
                    throw new \RuntimeException('The account is currently online.');
                }

                $userID      = $common->retrieveUserID($_POST['ban_account']);
                $accountData = $common->accountInformation($userID);

                if ($accountData[_CLMN_BLOCCODE_] == 1) {
                    throw new \RuntimeException('This account is already banned.');
                }

                $banType    = ((int) $_POST['ban_days'] >= 1 ? 'temporal' : 'permanent');
                $banLogData = [
                    'acc'    => $_POST['ban_account'],
                    'by'     => $_SESSION['username'],
                    'type'   => $banType,
                    'date'   => time(),
                    'days'   => $_POST['ban_days'],
                    'reason' => $_POST['ban_reason'] ?? '',
                ];

                $logBan = $db->query(
                    'INSERT INTO ' . Ban_Log . ' (account_id, banned_by, ban_type, ban_date, ban_days, ban_reason) VALUES (:acc, :by, :type, :date, :days, :reason)',
                    $banLogData
                );
                if (!$logBan) {
                    throw new \RuntimeException('Could not log ban (check tables)[1].');
                }

                if ($banType === 'temporal') {
                    $tempBan = $db->query(
                        'INSERT INTO ' . Bans . ' (account_id, banned_by, ban_date, ban_days, ban_reason) VALUES (:acc, :by, :date, :days, :reason)',
                        ['acc' => $_POST['ban_account'], 'by' => $_SESSION['username'], 'date' => time(), 'days' => $_POST['ban_days'], 'reason' => $_POST['ban_reason'] ?? '']
                    );
                    if (!$tempBan) {
                        throw new \RuntimeException('Could not add temporal ban (check tables)[2].');
                    }
                }

                $banAccount = $db->query(
                    'UPDATE ' . _TBL_MI_ . ' SET ' . _CLMN_BLOCCODE_ . ' = ? WHERE ' . _CLMN_USERNM_ . ' = ?',
                    [1, $_POST['ban_account']]
                );
                if (!$banAccount) {
                    throw new \RuntimeException('Could not ban account.');
                }
                message('success', 'Account Banned');
            } catch (\Exception $ex) {
                message('error', $ex->getMessage());
            }
        }

        $this->view->render('admincp/banaccount', []);
    }
}

