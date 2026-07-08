<?php

namespace SimpleSearch\Repository;

use PDO;

class SiteRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM sites")->fetchColumn();
    }

    public function findAll(int $limit = 200): array
    {
        return $this->db->query("SELECT * FROM sites ORDER BY crawled_at DESC LIMIT {$limit}")->fetchAll();
    }

    public function findAllForIndex(): array
    {
        return $this->db->query("SELECT id, url, title, crawled_at FROM sites ORDER BY crawled_at DESC")->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM sites WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findUrlById(int $id): ?string
    {
        $stmt = $this->db->prepare("SELECT url FROM sites WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetchColumn();
        return $result ?: null;
    }

    public function insert(string $url, string $title, string $description, string $content): void
    {
        $stmt = $this->db->prepare("INSERT INTO sites (url, title, description, content) VALUES (?,?,?,?)
            ON CONFLICT(url) DO UPDATE SET title=excluded.title, description=excluded.description, content=excluded.content, crawled_at=CURRENT_TIMESTAMP");
        $stmt->execute([$url, $title, $description, $content]);
    }

    public function insertWithTimestamp(string $url, string $title, string $description, string $content, string $crawledAt): void
    {
        $stmt = $this->db->prepare("INSERT INTO sites (url, title, description, content, crawled_at) VALUES (?,?,?,?,?)");
        $stmt->execute([$url, $title, $description, $content, $crawledAt]);
    }

    public function update(int $id, string $url, string $title, string $description, string $content): void
    {
        $stmt = $this->db->prepare("UPDATE sites SET url=?, title=?, description=?, content=? WHERE id=?");
        $stmt->execute([$url, $title, $description, $content, $id]);
    }

    public function updateCrawled(int $id, string $title, string $description, string $content): void
    {
        $stmt = $this->db->prepare("UPDATE sites SET title=?, description=?, content=?, crawled_at=CURRENT_TIMESTAMP WHERE id=?");
        $stmt->execute([$title, $description, $content, $id]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM sites WHERE id = ?")->execute([$id]);
    }

    public function getLastCrawlDate(): ?string
    {
        return $this->db->query("SELECT MAX(crawled_at) FROM sites")->fetchColumn() ?: null;
    }

    public function searchFts(string $ftsQuery, int $limit, int $offset): array
    {
        $stmt = $this->db->prepare("SELECT s.* FROM sites s JOIN sites_fts f ON s.id = f.rowid WHERE sites_fts MATCH :q ORDER BY rank LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':q', $ftsQuery);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countFts(string $ftsQuery): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sites_fts WHERE sites_fts MATCH :q");
        $stmt->execute([':q' => $ftsQuery]);
        return (int) $stmt->fetchColumn();
    }

    public function searchLike(string $query, int $limit, int $offset): array
    {
        $stmt = $this->db->prepare("SELECT * FROM sites WHERE title LIKE :q OR description LIKE :q OR content LIKE :q ORDER BY crawled_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':q', "%{$query}%");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countLike(string $query): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sites WHERE title LIKE :q OR description LIKE :q OR content LIKE :q");
        $stmt->execute([':q' => "%{$query}%"]);
        return (int) $stmt->fetchColumn();
    }

    public function ftsHasData(): bool
    {
        try {
            $cnt = $this->db->query("SELECT COUNT(*) FROM sites_fts")->fetchColumn();
            return $cnt > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function exportAll(): array
    {
        return $this->db->query("SELECT * FROM sites ORDER BY id")->fetchAll();
    }
}
