<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

final class RankingsController
{
    public function render(): void
    {
        if (empty($_REQUEST['subpage'])) {
            \Darkheim\Infrastructure\Http\Redirector::go(1, $_REQUEST['page'] . '/' . \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('rankings_show_default') . '/');
        }
    }
}
