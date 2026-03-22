<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\Auth\AuthService;

final class LogoutController
{
    public function render(): void
    {
        if (!\Darkheim\Application\Auth\SessionManager::websiteAuthenticated()) {
            \Darkheim\Infrastructure\Http\Redirector::go();
            return;
        }
        (new AuthService())->logout();
        \Darkheim\Infrastructure\Http\Redirector::go();
    }
}
