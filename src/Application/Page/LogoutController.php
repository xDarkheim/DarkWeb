<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\Auth\AuthService;

final class LogoutController
{
    public function render(): void
    {
        if (!isLoggedIn()) {
            redirect();
            return;
        }
        (new AuthService())->logout();
        redirect();
    }
}
