<?php

namespace SimpleSearch\Security;

class PasswordHasher
{
    private string $adminPasswordHash;
    private string $adminPasswordPlaintext;

    public function __construct(string $adminPasswordHash, string $adminPasswordPlaintext = '')
    {
        $this->adminPasswordHash = $adminPasswordHash;
        $this->adminPasswordPlaintext = $adminPasswordPlaintext;
    }

    public function verify(string $password): bool
    {
        // For backward compatibility, also check plain text during transition
        if (password_verify($password, $this->adminPasswordHash)) {
            return true;
        }
        if ($this->adminPasswordPlaintext !== '' && $password === $this->adminPasswordPlaintext) {
            return true;
        }
        return false;
    }

    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}
