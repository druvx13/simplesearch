<?php

namespace SimpleSearch\Database;

use PDO;
use PDOException;

class Connection
{
    private ?PDO $pdo = null;
    private string $dbPath;

    public function __construct(string $dbPath, ?string $fallbackPath = null)
    {
        // Try the primary path first
        if (file_exists($dbPath)) {
            $this->dbPath = $dbPath;
        } elseif ($fallbackPath !== null && file_exists($fallbackPath)) {
            // Fallback: use the existing search.db from old monolith location
            $this->dbPath = $fallbackPath;
        } else {
            // Neither exists — ensure the data directory exists and is writable,
            // then use the primary path (SchemaManager will create tables)
            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            if (!is_writable($dir)) {
                // Last resort: try the fallback path's directory (project root)
                if ($fallbackPath !== null) {
                    $this->dbPath = $fallbackPath;
                } else {
                    $this->dbPath = $dbPath; // Will fail with clear error below
                }
            } else {
                $this->dbPath = $dbPath;
            }
        }
    }

    public function getPdo(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        try {
            $this->pdo = new PDO("sqlite:{$this->dbPath}");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->exec("PRAGMA journal_mode=WAL");
            $this->pdo->exec("PRAGMA foreign_keys = ON");
        } catch (PDOException $e) {
            die(
                'Database connection failed: ' . $e->getMessage() . '<br><br>'
                . '<strong>Attempted path:</strong> ' . htmlspecialchars($this->dbPath) . '<br><br>'
                . '<strong>Troubleshooting:</strong><br>'
                . '1. Copy your existing <code>search.db</code> into the <code>data/</code> directory<br>'
                . '2. Make sure the <code>data/</code> directory is writable by the web server<br>'
                . '3. Check <code>config/database.php</code> path settings<br>'
                . '4. On Linux: <code>chmod 755 data/</code> and <code>chown www-data:www-data data/</code>'
            );
        }

        return $this->pdo;
    }

    public function getDbPath(): string
    {
        return $this->dbPath;
    }
}
