<?php

namespace SimpleSearch\Controller;

use SimpleSearch\Security\PasswordHasher;
use SimpleSearch\Security\CsrfToken;
use SimpleSearch\Security\SessionManager;
use SimpleSearch\Template\TemplateEngine;

class AuthController
{
    private PasswordHasher $passwordHasher;
    private CsrfToken $csrfToken;
    private SessionManager $session;
    private TemplateEngine $template;
    private array $appConfig;

    public function __construct(
        PasswordHasher $passwordHasher,
        CsrfToken $csrfToken,
        SessionManager $session,
        TemplateEngine $template,
        array $appConfig
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->csrfToken = $csrfToken;
        $this->session = $session;
        $this->template = $template;
        $this->appConfig = $appConfig;
    }

    public function login(array $params = []): string
    {
        $errorMsg = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            // For backward compatibility, also check plain text during transition
            if ($this->passwordHasher->verify($password)) {
                $this->session->loginAsAdmin();
                header('Location: index.php?action=admin');
                exit;
            }
            $errorMsg = 'Invalid password. Please try again.';
        }

        $viewData = [
            'siteName' => $this->appConfig['name'],
            'isAdmin' => false,
            'csrfToken' => $this->csrfToken->generate(),
            'errorMsg' => $errorMsg,
        ];

        header('Content-Type: text/html; charset=utf-8');
        return $this->template->renderWithLayout('admin/login', $viewData);
    }

    public function logout(array $params = []): void
    {
        $this->session->logout();
        header('Location: index.php');
        exit;
    }
}
