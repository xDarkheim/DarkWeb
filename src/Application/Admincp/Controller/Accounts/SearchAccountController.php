<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp\Controller\Accounts;

use Darkheim\Application\Admincp\Layout\AdmincpUrlGenerator;
use Darkheim\Domain\Validation\Validator;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\View\ViewRenderer;

final class SearchAccountController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $results       = null;
        $searchRequest = '';
        $error         = null;

        if (isset($_POST['search_account'], $_POST['search_request'])) {
            $searchRequest = (string) $_POST['search_request'];
            try {
                if (! Validator::Length($searchRequest, 11, 2)) {
                    throw new \RuntimeException('The username can be 3 to 10 characters long.');
                }
                $db         = Connection::Database('MuOnline');
                $admincpUrl = new AdmincpUrlGenerator();
                $rows       = $db->query_fetch(
                    'SELECT ' . _CLMN_MEMBID_ . ', ' . _CLMN_USERNM_ . ' FROM ' . _TBL_MI_ . ' WHERE ' . _CLMN_USERNM_ . ' LIKE ?',
                    ['%' . $searchRequest . '%'],
                );
                if (! is_array($rows)) {
                    throw new \RuntimeException('No results found.');
                }
                $results = array_map(static function (array $account) use ($admincpUrl): array {
                    return [
                        'id'             => (string) ($account[_CLMN_MEMBID_] ?? ''),
                        'username'       => (string) ($account[_CLMN_USERNM_] ?? ''),
                        'accountInfoUrl' => $admincpUrl->base('accountinfo&id=' . ($account[_CLMN_MEMBID_] ?? '')),
                    ];
                }, $rows);
            } catch (\Exception $ex) {
                $error = $ex->getMessage();
            }
        }

        $this->view->render('admincp/searchaccount', [
            'searchRequest' => $searchRequest,
            'results'       => $results,
            'error'         => $error,
        ]);
    }
}
