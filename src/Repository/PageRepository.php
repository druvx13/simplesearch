<?php

namespace SimpleSearch\Repository;

use PDO;

class PageRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        return $this->db->query("SELECT * FROM custom_pages ORDER BY title ASC")->fetchAll();
    }

    public function findPublished(): array
    {
        return $this->db->query("SELECT * FROM custom_pages WHERE is_published = 1 ORDER BY title ASC")->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM custom_pages WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM custom_pages WHERE slug = ? AND is_published = 1");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM custom_pages WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM custom_pages WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    public function insert(string $slug, string $title, string $content, bool $isPublished): int
    {
        $stmt = $this->db->prepare("INSERT INTO custom_pages (slug, title, content, is_published) VALUES (?, ?, ?, ?)");
        $stmt->execute([$slug, $title, $content, $isPublished ? 1 : 0]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $slug, string $title, string $content, bool $isPublished): void
    {
        $stmt = $this->db->prepare("UPDATE custom_pages SET slug=?, title=?, content=?, is_published=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
        $stmt->execute([$slug, $title, $content, $isPublished ? 1 : 0, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM custom_pages WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function count(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM custom_pages")->fetchColumn();
    }
}
