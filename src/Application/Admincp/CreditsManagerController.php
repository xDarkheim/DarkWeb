<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Infrastructure\View\ViewRenderer;

final class CreditsManagerController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $creditSystem = new CreditSystem();

        if (isset($_POST['creditsconfig'], $_POST['identifier'], $_POST['credits'], $_POST['transaction'])) {
            try {
                $creditSystem->setConfigId($_POST['creditsconfig']);
                $creditSystem->setIdentifier($_POST['identifier']);
                match ($_POST['transaction']) {
                    'add'      => $creditSystem->addCredits($_POST['credits']),
                    'subtract' => $creditSystem->subtractCredits($_POST['credits']),
                    default    => throw new \RuntimeException('Invalid transaction.'),
                };
                \Darkheim\Application\View\MessageRenderer::toast('success', $_POST['transaction'] === 'add' ? 'Credits added.' : 'Credits subtracted.');
            } catch (\Exception $ex) {
                \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
            }
        }

        $logs = $creditSystem->getLogs();
        $logRows = [];
        if (is_array($logs)) {
            foreach ($logs as $d) {
                $logRows[] = [
                    'config'      => (string) ($d['log_config'] ?? ''),
                    'identifier'  => (string) ($d['log_identifier'] ?? ''),
                    'credits'     => (string) ($d['log_credits'] ?? ''),
                    'transaction' => (string) ($d['log_transaction'] ?? ''),
                    'date'        => date('Y-m-d H:i', (int) ($d['log_date'] ?? 0)),
                    'module'      => (string) ($d['log_module'] ?? ''),
                    'inAdmincp'   => (int) ($d['log_inadmincp'] ?? 0) === 1,
                ];
            }
        }

        $this->view->render('admincp/creditsmanager', [
            'configSelectHtml' => $creditSystem->buildSelectInput('creditsconfig', 1, 'form-control'),
            'logRows'          => $logRows,
        ]);
    }
}

