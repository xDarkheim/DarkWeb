<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Infrastructure\View\ViewRenderer;

final class PhrasesController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $lang  = getLanguagePhrases();
        $error = null;
        $rows  = [];

        if (!is_array($lang)) {
            $error = 'Language file is empty.';
        } else {
            foreach ($lang as $key => $value) {
                $rows[] = [
                    'key'   => (string) $key,
                    'value' => (string) $value,
                ];
            }
        }

        $this->view->render('admincp/phrases', [
            'rows'  => $rows,
            'count' => count($rows),
            'error' => $error,
        ]);
    }
}

