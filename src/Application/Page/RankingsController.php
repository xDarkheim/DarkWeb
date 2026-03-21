<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

final class RankingsController
{
    public function render(): void
    {
        if (empty($_REQUEST['subpage'])) {
            redirect(1, $_REQUEST['page'] . '/' . mconfig('rankings_show_default') . '/');
        }
    }
}
