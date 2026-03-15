<?php

declare(strict_types=1);

namespace Darkheim\Application\Auth;

/**
 * Read/write interface for the CMS session state.
 * Intentionally does NOT start or destroy the session — that belongs to the
 * legacy bootstrap (includes/cms.php).  This class works with the already-started
 * PHP session.
 */
final class SessionManager
{
    /** True when all required session keys are present. */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['valid'], $_SESSION['userid'], $_SESSION['username'], $_SESSION['timeout']);
    }

    public function userId(): ?int
    {
        return isset($_SESSION['userid']) ? (int) $_SESSION['userid'] : null;
    }

    public function username(): ?string
    {
        return $_SESSION['username'] ?? null;
    }

    public function lastActivity(): int
    {
        return isset($_SESSION['timeout']) ? (int) $_SESSION['timeout'] : 0;
    }

    /** Returns true when the idle time has exceeded $timeoutSeconds. */
    public function hasTimedOut(int $timeoutSeconds): bool
    {
        return (time() - $this->lastActivity()) > $timeoutSeconds;
    }

    /** Stamp the current time as the last activity timestamp. */
    public function refreshTimeout(): void
    {
        $_SESSION['timeout'] = time();
    }

    /** Clears session data without destroying the session itself. */
    public function clearSession(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}

