<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp\Controller\Accounts;

use Darkheim\Application\Admincp\Layout\AdmincpUrlGenerator;
use Darkheim\Application\Auth\Common;
use Darkheim\Application\Character\Character;
use Darkheim\Application\Shared\UI\MessageRenderer;
use Darkheim\Domain\Validation\Validator;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\Email\Email;
use Darkheim\Infrastructure\Http\Redirector;
use Darkheim\Infrastructure\View\ViewRenderer;

final class AccountInfoController
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

        // Redirect by username → account ID
        if (isset($_GET['u'])) {
            try {
                $userId = $common->retrieveUserID($_GET['u']);
                if (Validator::hasValue($userId)) {
                    Redirector::go(3, $admincpUrl->base('accountinfo&id=' . $userId));
                }
            } catch (\Exception $ex) {
                MessageRenderer::toast('error', $ex->getMessage());
            }
        }

        if (! isset($_GET['id'])) {
            MessageRenderer::toast('error', 'Please provide a valid user id.');
            return;
        }

        try {
            $this->handlePostAction($common);

            $db          = Connection::Database('MuOnline');
            $accountInfo = $common->accountInformation($_GET['id']);
            if (! $accountInfo) {
                throw new \RuntimeException('Could not retrieve account information (invalid account).');
            }

            // Status
            $statusData = $db->query_fetch_single(
                'SELECT * FROM ' . _TBL_MS_ . ' WHERE ' . _CLMN_MS_MEMBID_ . ' = ?',
                [$accountInfo[_CLMN_USERNM_]],
            );

            // Characters
            $character  = new Character();
            $charList   = $character->AccountCharacter($accountInfo[_CLMN_USERNM_]);
            $characters = [];
            if (is_array($charList)) {
                foreach ($charList as $charName) {
                    $characters[] = [
                        'name'    => (string) $charName,
                        'editUrl' => $admincpUrl->base('editcharacter&name=' . $charName),
                    ];
                }
            }

            // IP data
            $muLogExIps        = $this->getMuLogExIps($db, $common, $accountInfo[_CLMN_USERNM_]);
            $connectionIps     = $this->getConnectionHistoryIps($db, $accountInfo[_CLMN_USERNM_]);
            $connectionHistory = $this->getConnectionHistory($db, $accountInfo[_CLMN_USERNM_]);

            $this->view->render('admincp/accountinfo', [
                'account' => [
                    'id'       => (string) ($accountInfo[_CLMN_MEMBID_] ?? ''),
                    'username' => (string) ($accountInfo[_CLMN_USERNM_] ?? ''),
                    'email'    => (string) ($accountInfo[_CLMN_EMAIL_] ?? ''),
                    'isBanned' => (int) ($accountInfo[_CLMN_BLOCCODE_] ?? 0) === 1,
                ],
                'status' => is_array($statusData) ? [
                    'isOnline' => (int) ($statusData[_CLMN_CONNSTAT_] ?? 0) === 1,
                    'server'   => (string) ($statusData[_CLMN_MS_GS_] ?? ''),
                ] : null,
                'characters'           => $characters,
                'muLogExIps'           => $muLogExIps,
                'connectionIps'        => $connectionIps,
                'connectionHistory'    => $connectionHistory,
                'hasMuLogEx'           => $muLogExIps    !== null,
                'hasConnectionHistory' => $connectionIps !== null,
            ]);
        } catch (\Exception $ex) {
            MessageRenderer::toast('error', $ex->getMessage());
        }
    }

    private function handlePostAction(Common $common): void
    {
        if (! isset($_POST['editaccount_submit'])) {
            return;
        }
        try {
            if (! isset($_POST['action'])) {
                throw new \RuntimeException('Invalid request.');
            }
            $accountInfo = $common->accountInformation($_GET['id']);
            if (! $accountInfo) {
                throw new \RuntimeException('Could not retrieve account information (invalid account).');
            }
            $sendEmail = isset($_POST['editaccount_sendmail']) && $_POST['editaccount_sendmail'] == 1;

            switch ($_POST['action']) {
                case 'changepassword':
                    if (! isset($_POST['changepassword_newpw'])) {
                        throw new \RuntimeException('Please enter the new password.');
                    }
                    if (! Validator::PasswordLength($_POST['changepassword_newpw'])) {
                        throw new \RuntimeException('Invalid password.');
                    }
                    if (! $common->changePassword($accountInfo[_CLMN_MEMBID_], $accountInfo[_CLMN_USERNM_], $_POST['changepassword_newpw'])) {
                        throw new \RuntimeException('Could not change password.');
                    }
                    MessageRenderer::toast('success', 'Password updated!');
                    if ($sendEmail) {
                        $email = new Email();
                        $email->setTemplate('ADMIN_CHANGE_PASSWORD');
                        $email->addVariable('{USERNAME}', $accountInfo[_CLMN_USERNM_]);
                        $email->addVariable('{NEW_PASSWORD}', $_POST['changepassword_newpw']);
                        $email->addAddress($accountInfo[_CLMN_EMAIL_]);
                        $email->send();
                    }
                    break;

                case 'changeemail':
                    if (! isset($_POST['changeemail_newemail'])) {
                        throw new \RuntimeException('Please enter the new email.');
                    }
                    if (! Validator::Email($_POST['changeemail_newemail'])) {
                        throw new \RuntimeException('Invalid email address.');
                    }
                    if ($common->emailExists($_POST['changeemail_newemail'])) {
                        throw new \RuntimeException('Another account with the same email already exists.');
                    }
                    if (! $common->updateEmail($accountInfo[_CLMN_MEMBID_], $_POST['changeemail_newemail'])) {
                        throw new \RuntimeException('Could not update email.');
                    }
                    MessageRenderer::toast('success', 'Email address updated!');
                    if ($sendEmail) {
                        $email = new Email();
                        $email->setTemplate('ADMIN_CHANGE_EMAIL');
                        $email->addVariable('{USERNAME}', $accountInfo[_CLMN_USERNM_]);
                        $email->addVariable('{NEW_EMAIL}', $_POST['changeemail_newemail']);
                        $email->addAddress($accountInfo[_CLMN_EMAIL_]);
                        $email->send();
                    }
                    break;

                default:
                    throw new \RuntimeException('Invalid request.');
            }
        } catch (\Exception $ex) {
            MessageRenderer::toast('error', $ex->getMessage());
        }
    }

    /** @return array<int,string>|null */
    private function getMuLogExIps(mixed $db, mixed $common, string $username): ?array
    {
        if (! defined('_TBL_LOGEX_') || ! defined('_CLMN_LOGEX_IP_')) {
            return null;
        }
        $tblLogEx    = constant('_TBL_LOGEX_');
        $clmnLogExIp = constant('_CLMN_LOGEX_IP_');
        $tableExists = $db->query_fetch_single("SELECT * FROM sysobjects WHERE xtype = 'U' AND name = ?", [$tblLogEx]);
        if (! $tableExists) {
            return null;
        }
        $rows = (is_object($common) && method_exists($common, 'retrieveAccountIPs'))
            ? ($common->retrieveAccountIPs($username) ?? [])
            : [];
        return array_map(static fn($r) => (string) ($r[$clmnLogExIp] ?? ''), (array) $rows);
    }

    /** @return array<int,string>|null */
    private function getConnectionHistoryIps(mixed $db, string $username): ?array
    {
        if (! $this->hasConnectionHistoryDefs()) {
            return null;
        }
        $rows = $db->query_fetch(
            'SELECT DISTINCT(' . constant('_CLMN_CH_IP_') . ') FROM ' . constant('_TBL_CH_') . ' WHERE ' . constant('_CLMN_CH_ACCID_') . ' = ?',
            [$username],
        );
        if (! is_array($rows)) {
            return [];
        }
        $col = constant('_CLMN_CH_IP_');
        return array_map(static fn($r) => (string) ($r[$col] ?? ''), $rows);
    }

    /** @return array<int,array{date:string,server:string,ip:string,hwid:string}>|null */
    private function getConnectionHistory(mixed $db, string $username): ?array
    {
        if (! $this->hasConnectionHistoryDefs()) {
            return null;
        }
        $rows = $db->query_fetch(
            'SELECT TOP 25 * FROM ' . constant('_TBL_CH_') . ' WHERE ' . constant('_CLMN_CH_ACCID_') . ' = ? AND ' . constant('_CLMN_CH_STATE_') . " = ? ORDER BY " . constant('_CLMN_CH_ID_') . ' DESC',
            [$username, 'Connect'],
        );
        if (! is_array($rows)) {
            return [];
        }
        return array_map(static fn($r) => [
            'date'   => (string) ($r[constant('_CLMN_CH_DATE_')] ?? ''),
            'server' => (string) ($r[constant('_CLMN_CH_SRVNM_')] ?? ''),
            'ip'     => (string) ($r[constant('_CLMN_CH_IP_')] ?? ''),
            'hwid'   => (string) ($r[constant('_CLMN_CH_HWID_')] ?? ''),
        ], $rows);
    }

    private function hasConnectionHistoryDefs(): bool
    {
        return defined('_TBL_CH_') && defined('_CLMN_CH_IP_') && defined('_CLMN_CH_ACCID_')
                                   && defined('_CLMN_CH_STATE_') && defined('_CLMN_CH_ID_') && defined('_CLMN_CH_DATE_')
                                   && defined('_CLMN_CH_SRVNM_') && defined('_CLMN_CH_HWID_');
    }
}
