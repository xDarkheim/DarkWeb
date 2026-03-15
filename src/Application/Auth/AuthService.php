<?php

declare(strict_types=1);

namespace Darkheim\Application\Auth;

/**
 * PSR-4 service that delegates to the legacy `login` classmap class.
 * Keeps all existing business logic (brute-force throttle, session keys,
 * redirect-on-success) intact — we just provide a clean call-site.
 */
final class AuthService
{
    private SessionManager $session;

    public function __construct(?SessionManager $session = null)
    {
        $this->session = $session ?? new SessionManager();
    }

    public function session(): SessionManager
    {
        return $this->session;
    }

    /**
     * Validates credentials and, on success, starts the session and redirects.
     * On failure throws an Exception with a translated message — same as legacy.
     *
     * @throws \Exception
     */
    public function login(string $username, string $password): void
    {
        $handler = new Login();
        $handler->validateLogin($username, $password);
    }

    /**
     * Destroys the current session and redirects to home.
     */
    public function logout(): void
    {
        $this->session->clearSession();
        redirect();
    }
}

