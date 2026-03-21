<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

final class InfoController
{
    public function render(): void
    {
        include __PATH_MODULES__ . 'info.php';
    }
}

