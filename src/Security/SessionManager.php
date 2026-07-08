<?php

namespace SimpleSearch\Security;

class SessionManager
{
    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function isAdmin(): bool
    {
        return !empty($_SESSION['admin']) && $_SESSION['admin'] === true;
    }

    public function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?action=admin_login');
            exit;
        }
    }

    public function loginAsAdmin(): void
    {
        $_SESSION['admin'] = true;
    }

    public function logout(): void
    {
        session_destroy();
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
}
