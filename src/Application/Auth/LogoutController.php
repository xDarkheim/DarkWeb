<?php

declare(strict_types=1);

namespace Darkheim\Application\Auth;

use Darkheim\Infrastructure\Http\Redirector;

final class LogoutController
{
    public function render(): void
    {
        if (! SessionManager::websiteAuthenticated()) {
            Redirector::go();
            return;
        }
        new AuthService()->logout();
        Redirector::go();
    }
}
