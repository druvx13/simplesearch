<?php

namespace SimpleSearch\Repository;

use PDO;

class MenuRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        return $this->db->query("SELECT * FROM menu_items ORDER BY sort_order ASC, id ASC")->fetchAll();
    }

    public function findActive(): array
    {
        return $this->db->query("SELECT * FROM menu_items WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function insert(string $label, string $url, string $icon, int $sortOrder, bool $isActive, bool $openNewTab): int
    {
        $stmt = $this->db->prepare("INSERT INTO menu_items (label, url, icon, sort_order, is_active, open_new_tab) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$label, $url, $icon, $sortOrder, $isActive ? 1 : 0, $openNewTab ? 1 : 0]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $label, string $url, string $icon, int $sortOrder, bool $isActive, bool $openNewTab): void
    {
        $stmt = $this->db->prepare("UPDATE menu_items SET label=?, url=?, icon=?, sort_order=?, is_active=?, open_new_tab=? WHERE id=?");
        $stmt->execute([$label, $url, $icon, $sortOrder, $isActive ? 1 : 0, $openNewTab ? 1 : 0, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function count(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
    }
}
