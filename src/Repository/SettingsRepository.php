<?php

namespace SimpleSearch\Repository;

use PDO;

class SettingsRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function get(string $key, string $default = ''): string
    {
        $stmt = $this->db->prepare("SELECT value FROM site_settings WHERE key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetchColumn();
        return $row !== false ? (string)$row : $default;
    }

    public function set(string $key, string $value): void
    {
        $stmt = $this->db->prepare("INSERT INTO site_settings (key, value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)
            ON CONFLICT(key) DO UPDATE SET value = ?, updated_at = CURRENT_TIMESTAMP");
        $stmt->execute([$key, $value, $value]);
    }

    public function getAll(): array
    {
        $rows = $this->db->query("SELECT key, value FROM site_settings")->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }
        return $settings;
    }
}
