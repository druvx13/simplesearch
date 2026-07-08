<?php

namespace SimpleSearch\Repository;

use PDO;

class QueryLogRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function log(string $query): void
    {
        $stmt = $this->db->prepare("INSERT INTO query_log (query) VALUES (?)");
        $stmt->execute([mb_substr(trim($query), 0, 255)]);
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM query_log")->fetchColumn();
    }

    public function suggest(string $prefix, int $limit = 8): array
    {
        $stmt = $this->db->prepare("SELECT DISTINCT query FROM query_log WHERE query LIKE :q ORDER BY searched_at DESC LIMIT :limit");
        $stmt->execute([':q' => $prefix . '%', ':limit' => $limit]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
