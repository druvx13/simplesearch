<?php

namespace SimpleSearch\Database;

use PDO;
use PDOException;

class SchemaManager
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function initialize(): void
    {
        $this->createSitesTable();
        $this->createFtsTable();
        $this->createFtsTriggers();
        $this->populateFtsIfEmpty();
        $this->createDictionaryTable();
        $this->createQueryLogTable();
        $this->createCrawlLogTable();
        $this->createMenuItemsTable();
        $this->createCustomPagesTable();
        $this->createSiteSettingsTable();
        $this->seedDefaultMenuItems();
        $this->seedDefaultSettings();
    }

    private function createSitesTable(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS sites (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            url TEXT UNIQUE NOT NULL,
            title TEXT,
            description TEXT,
            content TEXT,
            crawled_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    private function createFtsTable(): void
    {
        $this->db->exec("CREATE VIRTUAL TABLE IF NOT EXISTS sites_fts USING fts5(
            url,
            title,
            description,
            content,
            content='sites',
            content_rowid='id'
        )");
    }

    private function createFtsTriggers(): void
    {
        $this->db->exec("CREATE TRIGGER IF NOT EXISTS sites_ai AFTER INSERT ON sites BEGIN
            INSERT INTO sites_fts(rowid, url, title, description, content)
            VALUES (new.id, new.url, new.title, new.description, new.content);
        END");

        $this->db->exec("CREATE TRIGGER IF NOT EXISTS sites_ad AFTER DELETE ON sites BEGIN
            INSERT INTO sites_fts(sites_fts, rowid, url, title, description, content)
            VALUES('delete', old.id, old.url, old.title, old.description, old.content);
        END");

        $this->db->exec("CREATE TRIGGER IF NOT EXISTS sites_au AFTER UPDATE ON sites BEGIN
            INSERT INTO sites_fts(sites_fts, rowid, url, title, description, content)
            VALUES('delete', old.id, old.url, old.title, old.description, old.content);
            INSERT INTO sites_fts(rowid, url, title, description, content)
            VALUES (new.id, new.url, new.title, new.description, new.content);
        END");
    }

    private function populateFtsIfEmpty(): void
    {
        try {
            $cnt = $this->db->query("SELECT COUNT(*) FROM sites_fts")->fetchColumn();
            if ($cnt == 0) {
                $rows = $this->db->query("SELECT id, url, title, description, content FROM sites")->fetchAll();
                if (!empty($rows)) {
                    $stmt = $this->db->prepare("INSERT INTO sites_fts(rowid, url, title, description, content) VALUES (?,?,?,?,?)");
                    foreach ($rows as $r) {
                        $stmt->execute([$r['id'], $r['url'], $r['title'], $r['description'], $r['content']]);
                    }
                }
            }
        } catch (PDOException $e) {
            // FTS5 not available - continue with LIKE fallback
        }
    }

    private function createDictionaryTable(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS dictionary (word TEXT UNIQUE)");
    }

    private function createQueryLogTable(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS query_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            query TEXT,
            searched_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    private function createCrawlLogTable(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS crawl_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            url TEXT,
            status TEXT,
            message TEXT,
            crawled_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    private function createMenuItemsTable(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS menu_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            label TEXT NOT NULL,
            url TEXT NOT NULL,
            icon TEXT DEFAULT '',
            sort_order INTEGER DEFAULT 0,
            is_active INTEGER DEFAULT 1,
            open_new_tab INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    private function createCustomPagesTable(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS custom_pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT UNIQUE NOT NULL,
            title TEXT NOT NULL,
            content TEXT DEFAULT '',
            is_published INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    private function createSiteSettingsTable(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS site_settings (
            key TEXT PRIMARY KEY,
            value TEXT DEFAULT '',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    private function seedDefaultMenuItems(): void
    {
        $cnt = $this->db->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
        if ($cnt == 0) {
            $stmt = $this->db->prepare("INSERT INTO menu_items (label, url, icon, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['Search', 'index.php', 'fas fa-search', 1, 1]);
            $stmt->execute(['Index', 'index.php?action=openindex', 'fas fa-list', 2, 1]);
        }
    }

    private function seedDefaultSettings(): void
    {
        $cnt = $this->db->query("SELECT COUNT(*) FROM site_settings")->fetchColumn();
        if ($cnt == 0) {
            $stmt = $this->db->prepare("INSERT INTO site_settings (key, value) VALUES (?, ?)");
            $stmt->execute(['header_text', '']);
            $stmt->execute(['footer_text', 'SimpleSearch Engine — Lightweight Search Solution']);
            $stmt->execute(['home_tagline', 'Search across your indexed web pages']);
        }
    }
}
