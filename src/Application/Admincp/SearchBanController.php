<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\Auth\Common;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\View\ViewRenderer;

final class SearchBanController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $searchRequest = '';
        $results       = null;
        $error         = null;

        if (isset($_POST['search_ban'], $_POST['search_request'])) {
            $searchRequest = (string) $_POST['search_request'];
            try {
                $db     = Connection::Database('MuOnline');
                $admincpUrl = new AdmincpUrlGenerator();
                $common = new Common();
                $rows   = $db->query_fetch(
                    'SELECT TOP 25 * FROM ' . Ban_Log . ' WHERE account_id LIKE ?',
                    ['%' . $searchRequest . '%']
                );
                if (!is_array($rows)) {
                    throw new \RuntimeException('No results found.');
                }
                $results = [];
                foreach ($rows as $ban) {
                    $accId    = (string) ($ban['account_id'] ?? '');
                    $results[] = [
                        'account'        => $accId,
                        'accountInfoUrl' => $admincpUrl->base('accountinfo&id=' . $common->retrieveUserID($accId)),
                        'bannedBy'       => (string) ($ban['banned_by'] ?? ''),
                        'banType'        => (string) ($ban['ban_type'] ?? ''),
                        'banDate'        => date('Y-m-d H:i', (int) ($ban['ban_date'] ?? 0)),
                        'banDays'        => (string) ($ban['ban_days'] ?? ''),
                        'liftBanUrl'     => $admincpUrl->base('latestbans&liftban=' . ($ban['id'] ?? '')),
                    ];
                }
            } catch (\Exception $ex) {
                $error = $ex->getMessage();
            }
        }

        $this->view->render('admincp/searchban', [
            'searchRequest' => $searchRequest,
            'results'       => $results,
            'error'         => $error,
        ]);
    }
}

