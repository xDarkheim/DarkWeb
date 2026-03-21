<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

final class DownloadsController
{
    public function render(): void
    {
        include __PATH_MODULES__ . 'downloads.php';
    }
}

