<?php

namespace SimpleSearch\Controller;

use SimpleSearch\Service\CrawlerService;
use SimpleSearch\Service\ImportExportService;
use SimpleSearch\Repository\SiteRepository;
use SimpleSearch\Repository\CrawlLogRepository;
use SimpleSearch\Repository\QueryLogRepository;
use SimpleSearch\Repository\DictionaryRepository;
use SimpleSearch\Repository\MenuRepository;
use SimpleSearch\Repository\PageRepository;
use SimpleSearch\Repository\SettingsRepository;
use SimpleSearch\Security\CsrfToken;
use SimpleSearch\Security\SessionManager;
use SimpleSearch\Middleware\AuthMiddleware;
use SimpleSearch\Middleware\CsrfMiddleware;
use SimpleSearch\Helper\TextHelper;
use SimpleSearch\Template\TemplateEngine;

class AdminController
{
    private SiteRepository $siteRepo;
    private CrawlLogRepository $crawlLogRepo;
    private QueryLogRepository $queryLogRepo;
    private DictionaryRepository $dictionaryRepo;
    private CrawlerService $crawlerService;
    private ImportExportService $importExportService;
    private MenuRepository $menuRepo;
    private PageRepository $pageRepo;
    private SettingsRepository $settingsRepo;
    private CsrfToken $csrfToken;
    private SessionManager $session;
    private AuthMiddleware $authMiddleware;
    private CsrfMiddleware $csrfMiddleware;
    private TemplateEngine $template;
    private array $appConfig;

    public function __construct(
        SiteRepository $siteRepo,
        CrawlLogRepository $crawlLogRepo,
        QueryLogRepository $queryLogRepo,
        DictionaryRepository $dictionaryRepo,
        CrawlerService $crawlerService,
        ImportExportService $importExportService,
        MenuRepository $menuRepo,
        PageRepository $pageRepo,
        SettingsRepository $settingsRepo,
        CsrfToken $csrfToken,
        SessionManager $session,
        AuthMiddleware $authMiddleware,
        CsrfMiddleware $csrfMiddleware,
        TemplateEngine $template,
        array $appConfig
    ) {
        $this->siteRepo = $siteRepo;
        $this->crawlLogRepo = $crawlLogRepo;
        $this->queryLogRepo = $queryLogRepo;
        $this->dictionaryRepo = $dictionaryRepo;
        $this->crawlerService = $crawlerService;
        $this->importExportService = $importExportService;
        $this->menuRepo = $menuRepo;
        $this->pageRepo = $pageRepo;
        $this->settingsRepo = $settingsRepo;
        $this->csrfToken = $csrfToken;
        $this->session = $session;
        $this->authMiddleware = $authMiddleware;
        $this->csrfMiddleware = $csrfMiddleware;
        $this->template = $template;
        $this->appConfig = $appConfig;
    }

    /* ======================== DASHBOARD ======================== */

    public function dashboard(array $params = []): string
    {
        $this->authMiddleware->handle();

        $viewData = [
            'siteName' => $this->appConfig['name'],
            'isAdmin' => true,
            'csrfToken' => $this->csrfToken->generate(),
            'errorMsg' => $_SESSION['flash_error'] ?? '',
            'successMsg' => $_SESSION['flash_success'] ?? '',
            'totalSites' => $this->siteRepo->count(),
            'totalQueries' => $this->queryLogRepo->count(),
            'totalCrawls' => $this->crawlLogRepo->count(),
            'lastCrawl' => $this->siteRepo->getLastCrawlDate(),
            'allSites' => $this->siteRepo->findAll(200),
            'logRows' => $this->crawlLogRepo->getRecent(50),
        ];

        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        header('Content-Type: text/html; charset=utf-8');
        return $this->template->renderWithLayout('admin/dashboard', $viewData);
    }

    /* ======================== SITE EDIT ======================== */

    public function edit(array $params = []): string
    {
        $this->authMiddleware->handle();

        $id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?action=admin');
            exit;
        }

        $site = $this->siteRepo->findById($id);
        if (!$site) {
            header('Location: index.php?action=admin');
            exit;
        }

        $viewData = [
            'siteName' => $this->appConfig['name'],
            'isAdmin' => true,
            'csrfToken' => $this->csrfToken->generate(),
            'site' => $site,
        ];

        header('Content-Type: text/html; charset=utf-8');
        return $this->template->renderWithLayout('admin/edit-site', $viewData);
    }

    public function update(array $params = []): void
    {
        $this->authMiddleware->handle();

        if (!$this->csrfMiddleware->handle()) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            header('Location: index.php?action=admin');
            exit;
        }

        $id = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int)$_POST['id'] : 0;
        $url = TextHelper::sanitizeUrl(trim($_POST['url'] ?? ''));
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if ($id > 0 && !empty($url) && !empty($title)) {
            $this->siteRepo->update($id, $url, $title, $description, $content);
            $this->dictionaryRepo->updateFromText("$title $description $content");
            $_SESSION['flash_success'] = 'Site updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'URL and Title are required.';
        }

        header('Location: index.php?action=admin');
        exit;
    }

    public function add(array $params = []): void
    {
        $this->authMiddleware->handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url']) && !empty($_POST['title'])) {
            if (!$this->csrfMiddleware->handle()) {
                $_SESSION['flash_error'] = 'Invalid security token.';
                header('Location: index.php?action=admin');
                exit;
            }

            $url = TextHelper::sanitizeUrl(trim($_POST['url']));
            $title = trim($_POST['title']);
            $description = trim($_POST['description'] ?? '');
            $content = trim($_POST['content'] ?? '');

            if (empty($url) || empty($title)) {
                $_SESSION['flash_error'] = 'URL and Title are required.';
            } else {
                try {
                    $this->siteRepo->insert($url, $title, $description, $content);
                    $this->dictionaryRepo->updateFromText("$title $description $content");
                    $_SESSION['flash_success'] = 'Successfully added/updated: ' . htmlspecialchars($url);
                } catch (\Exception $e) {
                    $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
                }
            }
        }

        header('Location: index.php?action=admin');
        exit;
    }

    public function delete(array $params = []): void
    {
        $this->authMiddleware->handle();

        if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
            if (!$this->csrfMiddleware->validateGetToken($_GET['csrf_token'] ?? null)) {
                $_SESSION['flash_error'] = 'Invalid security token.';
                header('Location: index.php?action=admin');
                exit;
            }
            $this->siteRepo->delete((int)$_GET['id']);
        }

        header('Location: index.php?action=admin');
        exit;
    }

    /* ======================== CRAWL ======================== */

    public function crawl(array $params = []): void
    {
        $this->authMiddleware->handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url'])) {
            if (!$this->csrfMiddleware->handle()) {
                $_SESSION['flash_error'] = 'Invalid security token.';
                header('Location: index.php?action=admin');
                exit;
            }

            $url = TextHelper::sanitizeUrl(trim($_POST['url']));
            $result = $this->crawlerService->crawlAndSave($url);

            if ($result['success']) {
                $_SESSION['flash_success'] = $result['message'];
            } else {
                $_SESSION['flash_error'] = $result['message'];
            }
        }

        header('Location: index.php?action=admin');
        exit;
    }

    public function recrawl(array $params = []): void
    {
        $this->authMiddleware->handle();

        if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
            if (!$this->csrfMiddleware->validateGetToken($_GET['csrf_token'] ?? null)) {
                $_SESSION['flash_error'] = 'Invalid security token.';
                header('Location: index.php?action=admin');
                exit;
            }
            $result = $this->crawlerService->recrawl((int)$_GET['id']);
            if ($result) {
                $_SESSION['flash_success'] = 'Site re-crawled successfully.';
            } else {
                $_SESSION['flash_error'] = 'Re-crawl failed. Could not fetch URL.';
            }
        }

        header('Location: index.php?action=admin');
        exit;
    }

    public function bulkCrawl(array $params = []): void
    {
        $this->authMiddleware->handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['urls'])) {
            if (!$this->csrfMiddleware->handle()) {
                $_SESSION['flash_error'] = 'Invalid security token.';
                header('Location: index.php?action=admin');
                exit;
            }

            $urls = preg_split('/\r\n|\r|\n/', trim($_POST['urls']));
            $urls = array_map(function ($url) {
                return TextHelper::sanitizeUrl(trim($url));
            }, $urls);
            $urls = array_filter($urls, fn($u) => !empty($u));

            $result = $this->crawlerService->bulkCrawl($urls);
            $_SESSION['flash_success'] = "Bulk crawl complete: {$result['success']} succeeded, {$result['fail']} failed.";
        }

        header('Location: index.php?action=admin');
        exit;
    }

    public function sitemapCrawl(array $params = []): void
    {
        $this->authMiddleware->handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['sitemap_url'])) {
            if (!$this->csrfMiddleware->handle()) {
                $_SESSION['flash_error'] = 'Invalid security token.';
                header('Location: index.php?action=admin');
                exit;
            }

            $sitemapUrl = TextHelper::sanitizeUrl(trim($_POST['sitemap_url']));
            $result = $this->crawlerService->sitemapCrawl($sitemapUrl);

            if ($result['success']) {
                $_SESSION['flash_success'] = $result['message'];
            } else {
                $_SESSION['flash_error'] = $result['message'];
            }
        }

        header('Location: index.php?action=admin');
        exit;
    }

    public function clearLog(array $params = []): void
    {
        $this->authMiddleware->handle();

        if (!$this->csrfMiddleware->handle()) {
            $_SESSION['flash_error'] = 'Invalid security token.';
        } else {
            $this->crawlLogRepo->clear();
        }

        header('Location: index.php?action=admin');
        exit;
    }

    /* ======================== IMPORT / EXPORT ======================== */

    public function export(array $params = []): void
    {
        $this->authMiddleware->handle();
        $this->importExportService->exportCsv();
        exit;
    }

    public function import(array $params = []): void
    {
        $this->authMiddleware->handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->csrfMiddleware->handle()) {
                $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
                header('Location: index.php?action=admin');
                exit;
            }

            $result = $this->importExportService->importCsv($_FILES);
            if ($result['success']) {
                $_SESSION['flash_success'] = $result['message'];
            } else {
                $_SESSION['flash_error'] = $result['message'];
            }
        }

        header('Location: index.php?action=admin');
        exit;
    }

    public function importForm(array $params = []): string
    {
        $this->authMiddleware->handle();

        $viewData = [
            'siteName' => $this->appConfig['name'],
            'isAdmin' => true,
            'csrfToken' => $this->csrfToken->generate(),
        ];

        header('Content-Type: text/html; charset=utf-8');
        return $this->template->renderWithLayout('admin/import', $viewData);
    }

    public function backup(array $params = []): void
    {
        $this->authMiddleware->handle();
        $this->importExportService->backup();
        exit;
    }

    /* ======================== MENU MANAGEMENT ======================== */

    public function menuManager(array $params = []): string
    {
        $this->authMiddleware->handle();

        $viewData = [
            'siteName' => $this->appConfig['name'],
            'isAdmin' => true,
            'csrfToken' => $this->csrfToken->generate(),
            'menuItems' => $this->menuRepo->findAll(),
            'successMsg' => $_SESSION['flash_success'] ?? '',
            'errorMsg' => $_SESSION['flash_error'] ?? '',
        ];

        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        header('Content-Type: text/html; charset=utf-8');
        return $this->template->renderWithLayout('admin/menu-manager', $viewData);
    }

    public function menuAdd(array $params = []): void
    {
        $this->authMiddleware->handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->csrfMiddleware->handle()) {
                $_SESSION['flash_error'] = 'Invalid security token.';
                header('Location: index.php?action=menu_manager');
                exit;
            }

            $label = trim($_POST['label'] ?? '');
            $url = trim($_POST['url'] ?? '');
            $icon = trim($_POST['icon'] ?? '');
            $sortOrder = isset($_POST['sort_order']) && ctype_digit($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            $isActive = isset($_POST['is_active']);
            $openNewTab = isset($_POST['open_new_tab']);

            if (empty($label) || empty($url)) {
                $_SESSION['flash_error'] = 'Label and URL are required.';
            } else {
                $this->menuRepo->insert($label, $url, $icon, $sortOrder, $isActive, $openNewTab);
                $_SESSION['flash_success'] = 'Menu item added successfully.';
            }
        }

        header('Location: index.php?action=menu_manager');
        exit;
    }

    public function menuEdit(array $params = []): string
    {
        $this->authMiddleware->handle();

        $id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
        $item = $id > 0 ? $this->menuRepo->findById($id) : null;

        if (!$item) {
            $_SESSION['flash_error'] = 'Menu item not found.';
            header('Location: index.php?action=menu_manager');
            exit;
        }

        $viewData = [
            'siteName' => $this->appConfig['name'],
            'isAdmin' => true,
            'csrfToken' => $this->csrfToken->generate(),
            'menuItem' => $item,
        ];

        header('Content-Type: text/html; charset=utf-8');
        return $this->template->renderWithLayout('admin/menu-edit', $viewData);
    }

    public function menuUpdate(array $params = []): void
    {
        $this->authMiddleware->handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->csrfMiddleware->handle()) {
                $_SESSION['flash_error'] = 'Invalid security token.';
                header('Location: index.php?action=menu_manager');
                exit;
            }

            $id = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int)$_POST['id'] : 0;
            $label = trim($_POST['label'] ?? '');
            $url = trim($_POST['url'] ?? '');
            $icon = trim($_POST['icon'] ?? '');
            $sortOrder = isset($_POST['sort_order']) && ctype_digit($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            $isActive = isset($_POST['is_active']);
            $openNewTab = isset($_POST['open_new_tab']);

            if ($id > 0 && !empty($label) && !empty($url)) {
                $this->menuRepo->update($id, $label, $url, $icon, $sortOrder, $isActive, $openNewTab);
                $_SESSION['flash_success'] = 'Menu item updated successfully.';
            } else {
                $_SESSION['flash_error'] = 'Label and URL are required.';
            }
        }

        header('Location: index.php?action=menu_manager');
        exit;
    }

    public function menuDelete(array $params = []): void
    {
        $this->authMiddleware->handle();

        if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
            if (!$this->csrfMiddleware->validateGetToken($_GET['csrf_token'] ?? null)) {
                $_SESSION['flash_error'] = 'Invalid security token.';
                header('Location: index.php?action=menu_manager');
                exit;
            }
            $this->menuRepo->delete((int)$_GET['id']);
            $_SESSION['flash_success'] = 'Menu item deleted.';
        }

        header('Location: index.php?action=menu_manager');
        exit;
    }

    /* ======================== CUSTOM PAGES ======================== */

    public function pageManager(array $params = []): string
    {
        $this->authMiddleware->handle();

        $viewData = [
            'siteName' => $this->appConfig['name'],
            'isAdmin' => true,
            'csrfToken' => $this->csrfToken->generate(),
            'customPages' => $this->pageRepo->findAll(),
            'successMsg' => $_SESSION['flash_success'] ?? '',
            'errorMsg' => $_SESSION['flash_error'] ?? '',
        ];

        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        header('Content-Type: text/html; charset=utf-8');
        return $this->template->renderWithLayout('admin/page-manager', $viewData);
    }

    public function pageAdd(array $params = []): string
    {
        $this->authMiddleware->handle();

        $viewData = [
            'siteName' => $this->appConfig['name'],
            'isAdmin' => true,
            'csrfToken' => $this->csrfToken->generate(),
            'page' => null,
        ];

        header('Content-Type: text/html; charset=utf-8');
        return $this->template->renderWithLayout('admin/page-edit', $viewData);
    }

    public function pageEdit(array $params = []): string
    {
        $this->authMiddleware->handle();

        $id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
        $page = $id > 0 ? $this->pageRepo->findById($id) : null;

        if (!$page) {
            $_SESSION['flash_error'] = 'Page not found.';
            header('Location: index.php?action=page_manager');
            exit;
        }

        $viewData = [
            'siteName' => $this->appConfig['name'],
            'isAdmin' => true,
            'csrfToken' => $this->csrfToken->generate(),
            'page' => $page,
        ];

        header('Content-Type: text/html; charset=utf-8');
        return $this->template->renderWithLayout('admin/page-edit', $viewData);
    }

    public function pageSave(array $params = []): void
    {
        $this->authMiddleware->handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->csrfMiddleware->handle()) {
                $_SESSION['flash_error'] = 'Invalid security token.';
                header('Location: index.php?action=page_manager');
                exit;
            }

            $id = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int)$_POST['id'] : 0;
            $slug = trim($_POST['slug'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $isPublished = isset($_POST['is_published']);

            // Auto-generate slug from title if empty
            if (empty($slug) && !empty($title)) {
                $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)));
                $slug = trim($slug, '-');
            }

            if (empty($slug) || empty($title)) {
                $_SESSION['flash_error'] = 'Title is required (slug auto-generated from title).';
                header('Location: index.php?action=' . ($id > 0 ? 'page_edit&id=' . $id : 'page_add'));
                exit;
            }

            // Check slug uniqueness
            if ($this->pageRepo->slugExists($slug, $id > 0 ? $id : null)) {
                $_SESSION['flash_error'] = 'Slug "' . htmlspecialchars($slug) . '" already exists. Choose a different one.';
                header('Location: index.php?action=' . ($id > 0 ? 'page_edit&id=' . $id : 'page_add'));
                exit;
            }

            if ($id > 0) {
                $this->pageRepo->update($id, $slug, $title, $content, $isPublished);
                $_SESSION['flash_success'] = 'Page updated successfully.';
            } else {
                $this->pageRepo->insert($slug, $title, $content, $isPublished);
                $_SESSION['flash_success'] = 'Page created successfully.';
            }
        }

        header('Location: index.php?action=page_manager');
        exit;
    }

    public function pageDelete(array $params = []): void
    {
        $this->authMiddleware->handle();

        if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
            if (!$this->csrfMiddleware->validateGetToken($_GET['csrf_token'] ?? null)) {
                $_SESSION['flash_error'] = 'Invalid security token.';
                header('Location: index.php?action=page_manager');
                exit;
            }
            $this->pageRepo->delete((int)$_GET['id']);
            $_SESSION['flash_success'] = 'Page deleted.';
        }

        header('Location: index.php?action=page_manager');
        exit;
    }

    /* ======================== SITE SETTINGS ======================== */

    public function siteSettings(array $params = []): string
    {
        $this->authMiddleware->handle();

        $viewData = [
            'siteName' => $this->appConfig['name'],
            'isAdmin' => true,
            'csrfToken' => $this->csrfToken->generate(),
            'settings' => $this->settingsRepo->getAll(),
            'successMsg' => $_SESSION['flash_success'] ?? '',
            'errorMsg' => $_SESSION['flash_error'] ?? '',
        ];

        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        header('Content-Type: text/html; charset=utf-8');
        return $this->template->renderWithLayout('admin/site-settings', $viewData);
    }

    public function siteSettingsSave(array $params = []): void
    {
        $this->authMiddleware->handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->csrfMiddleware->handle()) {
                $_SESSION['flash_error'] = 'Invalid security token.';
                header('Location: index.php?action=site_settings');
                exit;
            }

            $this->settingsRepo->set('header_text', trim($_POST['header_text'] ?? ''));
            $this->settingsRepo->set('footer_text', trim($_POST['footer_text'] ?? ''));
            $this->settingsRepo->set('home_tagline', trim($_POST['home_tagline'] ?? ''));

            $_SESSION['flash_success'] = 'Settings saved successfully.';
        }

        header('Location: index.php?action=site_settings');
        exit;
    }
}
