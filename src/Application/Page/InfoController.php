<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Infrastructure\View\ViewRenderer;

final class InfoController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $this->view->render('info', [
            'serverName'  => htmlspecialchars((string)(config('server_name',       true) ?? 'MU Online'), ENT_QUOTES, 'UTF-8'),
            'season'      => htmlspecialchars((string)(config('server_info_season',    true) ?? ''),       ENT_QUOTES, 'UTF-8'),
            'expType'     => htmlspecialchars((string)(config('server_info_exp_type',  true) ?? ''),       ENT_QUOTES, 'UTF-8'),
            'maxLevel'    => htmlspecialchars((string)(config('server_info_max_level', true) ?? '400'),    ENT_QUOTES, 'UTF-8'),
            'maxReset'    => htmlspecialchars((string)(config('server_info_max_reset', true) ?? '—'),      ENT_QUOTES, 'UTF-8'),
            'exp'         => htmlspecialchars((string)(config('server_info_exp',       true) ?? '—'),      ENT_QUOTES, 'UTF-8'),
            'masterExp'   => htmlspecialchars((string)(config('server_info_masterexp', true) ?? '—'),      ENT_QUOTES, 'UTF-8'),
            'drop'        => htmlspecialchars((string)(config('server_info_drop',      true) ?? '—'),      ENT_QUOTES, 'UTF-8'),
        ]);
    }
}
