<?php
/**
 * SimpleSearch - Front Controller
 * 
 * This is the single entry point for the application.
 * It bootstraps the DI container, matches routes, and dispatches.
 */

// Load autoloader
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    // Fallback: register PSR-4 autoloader manually
    spl_autoload_register(function (string $class) {
        $prefix = 'SimpleSearch\\';
        $baseDir = __DIR__ . '/../src/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    });
}

use SimpleSearch\Database\Connection;
use SimpleSearch\Database\SchemaManager;
use SimpleSearch\Security\CsrfToken;
use SimpleSearch\Security\PasswordHasher;
use SimpleSearch\Security\SessionManager;
use SimpleSearch\Repository\SiteRepository;
use SimpleSearch\Repository\QueryLogRepository;
use SimpleSearch\Repository\CrawlLogRepository;
use SimpleSearch\Repository\DictionaryRepository;
use SimpleSearch\Repository\MenuRepository;
use SimpleSearch\Repository\PageRepository;
use SimpleSearch\Repository\SettingsRepository;
use SimpleSearch\Service\SearchService;
use SimpleSearch\Service\CrawlerService;
use SimpleSearch\Service\DictionaryService;
use SimpleSearch\Service\ImportExportService;
use SimpleSearch\Middleware\AuthMiddleware;
use SimpleSearch\Middleware\CsrfMiddleware;
use SimpleSearch\Controller\PublicController;
use SimpleSearch\Controller\AdminController;
use SimpleSearch\Controller\AuthController;
use SimpleSearch\Template\TemplateEngine;

/* ========================== LOAD CONFIG ========================== */
$appConfig = require __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database.php';
$securityConfig = require __DIR__ . '/../config/security.php';

/* ── Compute asset base URL ── */
$assetBase = './assets';
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptDir !== '/' && $scriptDir !== '') {
    $assetBase = rtrim($scriptDir, '/') . '/assets';
}
$appConfig['assetBase'] = $assetBase;

/* ========================== BOOTSTRAP ========================== */

// Start session
$session = new SessionManager();
$session->start();

// Database
$connection = new Connection(
    $dbConfig['path'],
    $dbConfig['fallback_path'] ?? null
);
$pdo = $connection->getPdo();

// Initialize schema
$schemaManager = new SchemaManager($pdo);
$schemaManager->initialize();

// Security
$csrfToken = new CsrfToken($securityConfig['csrf_token_length']);
$passwordHasher = new PasswordHasher($securityConfig['admin_password_hash'], $securityConfig['admin_password_plaintext'] ?? '');

// Repositories
$siteRepo = new SiteRepository($pdo);
$queryLogRepo = new QueryLogRepository($pdo);
$crawlLogRepo = new CrawlLogRepository($pdo);
$dictionaryRepo = new DictionaryRepository($pdo, $appConfig['dictionary']['min_word_length'], $appConfig['dictionary']['max_word_length']);
$menuRepo = new MenuRepository($pdo);
$pageRepo = new PageRepository($pdo);
$settingsRepo = new SettingsRepository($pdo);

// Services
$searchService = new SearchService(
    $siteRepo,
    $queryLogRepo,
    $dictionaryRepo,
    $appConfig['results_per_page'],
    $appConfig['dictionary']['max_suggestion_distance']
);

$crawlerService = new CrawlerService(
    $siteRepo,
    $crawlLogRepo,
    $dictionaryRepo,
    [
        'timeout' => $appConfig['crawl']['timeout'],
        'max_redirects' => $appConfig['crawl']['max_redirects'],
        'user_agent' => $appConfig['crawl']['user_agent'],
        'max_content_length' => $appConfig['crawl']['max_content_length'],
        'sitemap_timeout' => $appConfig['sitemap']['timeout'],
    ]
);

$dictionaryService = new DictionaryService(
    $dictionaryRepo,
    $appConfig['dictionary']['max_suggestion_distance']
);

$importExportService = new ImportExportService(
    $siteRepo,
    $dictionaryRepo,
    $connection->getDbPath()
);

// Middleware
$authMiddleware = new AuthMiddleware($session);
$csrfMiddleware = new CsrfMiddleware($csrfToken);

// Template engine — inject global data (menu items, settings)
$menuItems = $menuRepo->findActive();
$siteSettings = $settingsRepo->getAll();

$templateEngine = new TemplateEngine(__DIR__ . '/../templates');
$templateEngine->setGlobalData([
    'assetBase' => $assetBase,
    'siteName'  => $appConfig['name'],
    'menuItems' => $menuItems,
    'settings'  => $siteSettings,
]);

// Controllers
$publicController = new PublicController(
    $searchService,
    $siteRepo,
    $queryLogRepo,
    $menuRepo,
    $pageRepo,
    $settingsRepo,
    $csrfToken,
    $session,
    $templateEngine,
    $appConfig
);

$adminController = new AdminController(
    $siteRepo,
    $crawlLogRepo,
    $queryLogRepo,
    $dictionaryRepo,
    $crawlerService,
    $importExportService,
    $menuRepo,
    $pageRepo,
    $settingsRepo,
    $csrfToken,
    $session,
    $authMiddleware,
    $csrfMiddleware,
    $templateEngine,
    $appConfig
);

$authController = new AuthController(
    $passwordHasher,
    $csrfToken,
    $session,
    $templateEngine,
    $appConfig
);

/* ========================== ROUTING & DISPATCH ========================== */
$action = $_GET['action'] ?? '';

// Route to appropriate controller action
switch ($action) {
    // AJAX endpoint for suggestions
    case 'suggest':
        $publicController->suggest();
        break;

    // Custom page (public)
    case 'page':
        echo $publicController->customPage();
        break;

    // Database backup
    case 'backup':
        $adminController->backup();
        break;

    // CSV Export
    case 'export':
        $adminController->export();
        break;

    // CSV Import processing
    case 'import':
        $adminController->import();
        break;

    // CSV Import form
    case 'import_form':
        echo $adminController->importForm();
        break;

    // Edit site form
    case 'edit':
        echo $adminController->edit();
        break;

    // Update site after edit
    case 'update':
        $adminController->update();
        break;

    // Sitemap crawl
    case 'sitemap_crawl':
        $adminController->sitemapCrawl();
        break;

    // Bulk URL crawl
    case 'bulk_crawl':
        $adminController->bulkCrawl();
        break;

    // Clear crawl log
    case 'clear_log':
        $adminController->clearLog();
        break;

    // Admin login
    case 'admin_login':
        echo $authController->login();
        break;

    // Admin panel
    case 'admin':
        echo $adminController->dashboard();
        break;

    // ─── Menu Management ───
    case 'menu_manager':
        echo $adminController->menuManager();
        break;

    case 'menu_add':
        $adminController->menuAdd();
        break;

    case 'menu_edit':
        echo $adminController->menuEdit();
        break;

    case 'menu_update':
        $adminController->menuUpdate();
        break;

    case 'menu_delete':
        $adminController->menuDelete();
        break;

    // ─── Custom Pages ───
    case 'page_manager':
        echo $adminController->pageManager();
        break;

    case 'page_add':
        echo $adminController->pageAdd();
        break;

    case 'page_edit':
        echo $adminController->pageEdit();
        break;

    case 'page_save':
        $adminController->pageSave();
        break;

    case 'page_delete':
        $adminController->pageDelete();
        break;

    // ─── Site Settings ───
    case 'site_settings':
        echo $adminController->siteSettings();
        break;

    case 'site_settings_save':
        $adminController->siteSettingsSave();
        break;

    // Crawl URL
    case 'crawl':
        $adminController->crawl();
        break;

    // Add site manually
    case 'add':
        $adminController->add();
        break;

    // Delete site
    case 'delete':
        $adminController->delete();
        break;

    // Re-crawl site
    case 'recrawl':
        $adminController->recrawl();
        break;

    // Open index (public)
    case 'openindex':
        echo $publicController->openIndex();
        break;

    // Logout
    case 'logout':
        $authController->logout();
        break;

    // Default: search page
    default:
        echo $publicController->index();
        break;
}
