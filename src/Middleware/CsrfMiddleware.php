<?php

namespace SimpleSearch\Middleware;

use SimpleSearch\Security\CsrfToken;

class CsrfMiddleware
{
    private CsrfToken $csrfToken;

    public function __construct(CsrfToken $csrfToken)
    {
        $this->csrfToken = $csrfToken;
    }

    /**
     * Validate CSRF token from POST data. Returns true if valid, false if invalid.
     * Sets an error message in session on failure.
     */
    public function handle(): bool
    {
        if (!$this->csrfToken->validatePost()) {
            $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
            return false;
        }
        return true;
    }

    /**
     * Validate CSRF token from GET parameter (for delete/recrawl links).
     */
    public function validateGetToken(?string $token): bool
    {
        return $this->csrfToken->validate($token);
    }
}
