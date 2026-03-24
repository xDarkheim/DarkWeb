<?php

declare(strict_types=1);

namespace Darkheim\Application\Auth;

use Darkheim\Infrastructure\Bootstrap\BootstrapContext;

/**
 * Admin Control Panel access guard.
 *
 * Replaces the global canAccessAdminCP() helper. Use this class directly
 * in new code; the global function delegates here.
 */
final class AdminGuard
{
    /**
     * Returns true when $username is listed in the CMS admins configuration.
     */
    public static function canAccess(string $username): bool
    {
        if ($username === '') {
            return false;
        }

        try {
            $cms = BootstrapContext::configProvider()?->cms();
        } catch (\Throwable) {
            return false;
        }

        return is_array($cms)
            && is_array($cms['admins'] ?? null)
            && array_key_exists($username, $cms['admins']);
    }
}
