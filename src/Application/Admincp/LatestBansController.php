<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\View\ViewRenderer;

final class LatestBansController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $db = Connection::Database('MuOnline');

        if (isset($_GET['liftban'])) {
            try {
                if (!Validator::UnsignedNumber($_GET['liftban'])) {
                    throw new \RuntimeException('Invalid ban id.');
                }
                $banInfo = $db->query_fetch_single('SELECT * FROM ' . Ban_Log . ' WHERE id = ?', [$_GET['liftban']]);
                if (!is_array($banInfo)) {
                    throw new \RuntimeException('Ban ID does not exist.');
                }
                $unban = $db->query(
                    'UPDATE ' . _TBL_MI_ . ' SET ' . _CLMN_BLOCCODE_ . ' = 0 WHERE ' . _CLMN_USERNM_ . ' = ?',
                    [$banInfo['account_id']]
                );
                if (!$unban) {
                    throw new \RuntimeException('Could not unban account.');
                }
                $db->query('DELETE FROM ' . Ban_Log . ' WHERE account_id = ?', [$banInfo['account_id']]);
                $db->query('DELETE FROM ' . Bans . ' WHERE account_id = ?', [$banInfo['account_id']]);
                message('success', 'Account ban lifted');
            } catch (\Exception $ex) {
                message('error', $ex->getMessage());
            }
        }

        $module = (string) ($_REQUEST['module'] ?? 'latestbans');

        $this->view->render('admincp/latestbans', [
            'temporalBans'  => $this->buildBanRows($db->query_fetch(
                'SELECT TOP 25 * FROM ' . Ban_Log . " WHERE ban_type = ? ORDER BY id DESC", ['temporal']
            ), $module),
            'permanentBans' => $this->buildBanRows($db->query_fetch(
                'SELECT TOP 25 * FROM ' . Ban_Log . " WHERE ban_type = ? ORDER BY id DESC", ['permanent']
            ), $module),
        ]);
    }

    /**
     * @param mixed $rows
     * @return array<int,array{account:string,bannedBy:string,banDate:string,banDays:string,banReason:string,liftBanUrl:string}>
     */
    private function buildBanRows(mixed $rows, string $module): array
    {
        if (!is_array($rows)) {
            return [];
        }
        $result = [];
        foreach ($rows as $ban) {
            $result[] = [
                'account'    => (string) ($ban['account_id'] ?? ''),
                'bannedBy'   => (string) ($ban['banned_by'] ?? ''),
                'banDate'    => date('Y-m-d H:i', (int) ($ban['ban_date'] ?? 0)),
                'banDays'    => (string) ($ban['ban_days'] ?? ''),
                'banReason'  => (string) ($ban['ban_reason'] ?? ''),
                'liftBanUrl' => admincp_base($module . '&liftban=' . ($ban['id'] ?? '')),
            ];
        }
        return $result;
    }
}

