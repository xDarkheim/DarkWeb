<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Infrastructure\View\ViewRenderer;

final class TosController
{
    private ViewRenderer $view;
    public function __construct(?ViewRenderer $view = null) { $this->view = $view ?? new ViewRenderer(); }
    public function render(): void { $this->view->render('tos'); }
}
