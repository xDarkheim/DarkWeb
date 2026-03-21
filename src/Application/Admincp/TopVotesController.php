<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\Auth\Common;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\View\ViewRenderer;

final class TopVotesController
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
        $month = date('F Y');

        try {
            $db   = Connection::Database('MuOnline');
            $common = new Common();
            $startOfMonth = new \DateTimeImmutable('first day of this month 00:00:00');
            $startOfNextMonth = $startOfMonth->modify('+1 month');
            $ts1  = $startOfMonth->getTimestamp();
            $ts2  = $startOfNextMonth->getTimestamp();
            $logs = $db->query_fetch(
                'SELECT TOP 100 user_id, COUNT(*) as totalvotes FROM ' . Vote_Logs . ' WHERE timestamp BETWEEN ? AND ? GROUP BY user_id ORDER BY totalvotes DESC',
                [$ts1, $ts2]
            );
            if (!is_array($logs)) {
                throw new \RuntimeException('No vote logs found.');
            }
            foreach ($logs as $idx => $v) {
                $acc    = $common->accountInformation($v['user_id']);
                $rows[] = [
                    'rank'       => $idx + 1,
                    'username'   => (string) ($acc[_CLMN_USERNM_] ?? ''),
                    'totalVotes' => (string) ($v['totalvotes'] ?? '0'),
                ];
            }
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
        }

        $this->view->render('admincp/topvotes', [
            'rows'  => $rows,
            'month' => $month,
            'error' => $error,
        ]);
    }
}

