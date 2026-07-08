<?php

namespace SimpleSearch\Controller;

use SimpleSearch\Service\SearchService;
use SimpleSearch\Repository\SiteRepository;
use SimpleSearch\Repository\QueryLogRepository;
use SimpleSearch\Repository\MenuRepository;
use SimpleSearch\Repository\PageRepository;
use SimpleSearch\Repository\SettingsRepository;
use SimpleSearch\Security\CsrfToken;
use SimpleSearch\Security\SessionManager;
use SimpleSearch\Template\TemplateEngine;

class PublicController
{
    private SearchService $searchService;
    private SiteRepository $siteRepo;
    private QueryLogRepository $queryLogRepo;
    private MenuRepository $menuRepo;
    private PageRepository $pageRepo;
    private SettingsRepository $settingsRepo;
    private CsrfToken $csrfToken;
    private SessionManager $session;
    private TemplateEngine $template;
    private array $appConfig;

    public function __construct(
        SearchService $searchService,
        SiteRepository $siteRepo,
        QueryLogRepository $queryLogRepo,
        MenuRepository $menuRepo,
        PageRepository $pageRepo,
        SettingsRepository $settingsRepo,
        CsrfToken $csrfToken,
        SessionManager $session,
        TemplateEngine $template,
        array $appConfig
    ) {
        $this->searchService = $searchService;
        $this->siteRepo = $siteRepo;
        $this->queryLogRepo = $queryLogRepo;
        $this->menuRepo = $menuRepo;
        $this->pageRepo = $pageRepo;
        $this->settingsRepo = $settingsRepo;
        $this->csrfToken = $csrfToken;
        $this->session = $session;
        $this->template = $template;
        $this->appConfig = $appConfig;
    }

    public function index(array $params = []): string
    {
        $query = trim($_GET['q'] ?? '');
        $page = isset($_GET['page']) && ctype_digit($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

        $searchResult = $this->searchService->search($query, $page);

        $viewData = array_merge($searchResult, [
            'siteName' => $this->appConfig['name'],
            'isAdmin' => $this->session->isAdmin(),
            'csrfToken' => $this->csrfToken->generate(),
            'pageType' => $query !== '' ? 'results' : 'home',
            'menuItems' => $this->menuRepo->findActive(),
            'settings' => $this->settingsRepo->getAll(),
        ]);

        header('Content-Type: text/html; charset=utf-8');
        return $this->template->renderWithLayout('public/search', $viewData);
    }

    public function suggest(array $params = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $prefix = trim($_GET['q'] ?? '');
        if ($prefix === '' || mb_strlen($prefix) < 1) {
            echo json_encode([]);
            exit;
        }
        $suggestions = $this->searchService->suggest($prefix);
        echo json_encode($suggestions);
        exit;
    }

    public function openIndex(array $params = []): string
    {
        $allSites = $this->siteRepo->findAllForIndex();

        $viewData = [
            'siteName' => $this->appConfig['name'],
            'isAdmin' => $this->session->isAdmin(),
            'csrfToken' => $this->csrfToken->generate(),
            'allSites' => $allSites,
            'totalIndexed' => count($allSites),
            'menuItems' => $this->menuRepo->findActive(),
            'settings' => $this->settingsRepo->getAll(),
        ];

        header('Content-Type: text/html; charset=utf-8');
        return $this->template->renderWithLayout('public/index-listing', $viewData);
    }

    public function customPage(array $params = []): string
    {
        $slug = trim($_GET['slug'] ?? '');
        if (empty($slug)) {
            header('Location: index.php');
            exit;
        }

        $page = $this->pageRepo->findBySlug($slug);
        if (!$page) {
            http_response_code(404);
            $viewData = [
                'siteName' => $this->appConfig['name'],
                'isAdmin' => $this->session->isAdmin(),
                'csrfToken' => $this->csrfToken->generate(),
                'pageType' => 'default',
                'menuItems' => $this->menuRepo->findActive(),
                'settings' => $this->settingsRepo->getAll(),
                'pageTitle' => 'Page Not Found',
                'errorMessage' => 'The page you are looking for does not exist or has been removed.',
            ];
            header('Content-Type: text/html; charset=utf-8');
            return $this->template->renderWithLayout('public/custom-page', $viewData);
        }

        $viewData = [
            'siteName' => $this->appConfig['name'],
            'isAdmin' => $this->session->isAdmin(),
            'csrfToken' => $this->csrfToken->generate(),
            'pageType' => 'default',
            'menuItems' => $this->menuRepo->findActive(),
            'settings' => $this->settingsRepo->getAll(),
            'customPage' => $page,
            'pageTitle' => $page['title'],
        ];

        header('Content-Type: text/html; charset=utf-8');
        return $this->template->renderWithLayout('public/custom-page', $viewData);
    }
}
