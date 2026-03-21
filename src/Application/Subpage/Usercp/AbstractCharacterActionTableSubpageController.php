<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Character\Character;
use Darkheim\Infrastructure\View\ViewRenderer;

abstract class AbstractCharacterActionTableSubpageController
{
    protected ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    final public function render(): void
    {
        if (!isLoggedIn()) {
            redirect(1, 'login');
            return;
        }

        try {
            if (!mconfig('active')) {
                throw new \Exception(lang('error_47', true));
            }

            $characterService = new Character();
            $accountCharacters = $characterService->AccountCharacter($_SESSION['username']);
            if (!is_array($accountCharacters)) {
                throw new \Exception(lang('error_46', true));
            }

            if ($this->isSubmitRequest()) {
                try {
                    $this->handleSubmit($characterService);
                } catch (\Exception $ex) {
                    message('error', $ex->getMessage());
                }
            }

            $rows = [];
            foreach ($accountCharacters as $characterName) {
                $row = $this->buildRow($characterService, (string) $characterName);
                if (is_array($row)) {
                    $rows[] = $row;
                }
            }

            $this->view->render('subpages/usercp/actiontables', [
                'pageTitle'         => $this->pageTitle(),
                'cardTitle'         => $this->cardTitle(),
                'cardIconClass'     => $this->cardIconClass(),
                'tableHeaders'      => $this->tableHeaders(),
                'rows'              => $rows,
                'requirementsLines' => $this->requirementsLines(),
            ]);
        } catch (\Exception $ex) {
            inline_message('error', $ex->getMessage());
        }
    }

    protected function isSubmitRequest(): bool
    {
        return isset($_POST['submit']);
    }

    /** @return array<int, string> */
    abstract protected function tableHeaders(): array;

    abstract protected function pageTitle(): string;

    abstract protected function cardTitle(): string;

    abstract protected function cardIconClass(): string;

    /**
     * @return array{character:string,cells:array<int,string>,buttonLabel:string}|null
     */
    abstract protected function buildRow(Character $characterService, string $characterName): ?array;

    abstract protected function handleSubmit(Character $characterService): void;

    /** @return array<int, string> */
    abstract protected function requirementsLines(): array;
}

