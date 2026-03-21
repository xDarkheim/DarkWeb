<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\Auth\Common;
use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\View\ViewRenderer;

final class SearchCharacterController
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

        if (isset($_POST['search_character'], $_POST['search_request'])) {
            $searchRequest = (string) $_POST['search_request'];
            try {
                if (!Validator::Length($searchRequest, 11, 2)) {
                    throw new \RuntimeException('The name can be 3 to 10 characters long.');
                }
                $db     = Connection::Database('MuOnline');
                $common = new Common();
                $rows   = $db->query_fetch(
                    'SELECT TOP 10 ' . _CLMN_CHR_NAME_ . ', ' . _CLMN_CHR_ACCID_ . ' FROM ' . _TBL_CHR_ . ' WHERE Name LIKE ?',
                    ['%' . $searchRequest . '%']
                );
                if (!is_array($rows)) {
                    throw new \RuntimeException('No results found.');
                }
                $results = [];
                foreach ($rows as $character) {
                    $accId   = (string) ($character[_CLMN_CHR_ACCID_] ?? '');
                    $results[] = [
                        'name'           => (string) ($character[_CLMN_CHR_NAME_] ?? ''),
                        'accountInfoUrl' => admincp_base('accountinfo&id=' . $common->retrieveUserID($accId)),
                        'editCharUrl'    => admincp_base('editcharacter&name=' . ($character[_CLMN_CHR_NAME_] ?? '')),
                    ];
                }
            } catch (\Exception $ex) {
                $error = $ex->getMessage();
            }
        }

        $this->view->render('admincp/searchcharacter', [
            'searchRequest' => $searchRequest,
            'results'       => $results,
            'error'         => $error,
        ]);
    }
}

