<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

final class CastleSiegeController
{
    public function render(): void
    {
        include __PATH_MODULES__ . 'castlesiege.php';
    }
}

