<?php

namespace SimpleSearch\Security;

class CsrfToken
{
    private int $tokenLength;

    public function __construct(int $tokenLength = 32)
    {
        $this->tokenLength = $tokenLength;
    }

    public function generate(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes($this->tokenLength));
        }
        return $_SESSION['csrf_token'];
    }

    public function validate(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public function validatePost(): bool
    {
        return $this->validate($_POST['csrf_token'] ?? null);
    }

    public function regenerate(): string
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes($this->tokenLength));
        return $_SESSION['csrf_token'];
    }
}
