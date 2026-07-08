<?php

namespace SimpleSearch\Repository;

use PDO;

class CrawlLogRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function log(string $url, string $status, string $message = ''): void
    {
        $stmt = $this->db->prepare("INSERT INTO crawl_log (url, status, message) VALUES (?,?,?)");
        $stmt->execute([$url, $status, mb_substr($message, 0, 500)]);
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM crawl_log")->fetchColumn();
    }

    public function getRecent(int $limit = 50): array
    {
        return $this->db->query("SELECT * FROM crawl_log ORDER BY crawled_at DESC LIMIT {$limit}")->fetchAll();
    }

    public function clear(): void
    {
        $this->db->exec("DELETE FROM crawl_log");
    }
}
