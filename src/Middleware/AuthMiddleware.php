<?php

namespace SimpleSearch\Middleware;

use SimpleSearch\Security\SessionManager;

class AuthMiddleware
{
    private SessionManager $session;

    public function __construct(SessionManager $session)
    {
        $this->session = $session;
    }

    /**
     * Handle authentication check. Returns true if admin, redirects and returns false otherwise.
     */
    public function handle(): bool
    {
        if (!$this->session->isAdmin()) {
            header('Location: index.php?action=admin_login');
            exit;
        }
        return true;
    }

    public function isAdmin(): bool
    {
        return $this->session->isAdmin();
    }
}
