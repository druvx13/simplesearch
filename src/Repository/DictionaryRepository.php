<?php

namespace SimpleSearch\Repository;

use PDO;

class DictionaryRepository
{
    private PDO $db;
    private int $minWordLength;
    private int $maxWordLength;

    public function __construct(PDO $db, int $minWordLength = 2, int $maxWordLength = 30)
    {
        $this->db = $db;
        $this->minWordLength = $minWordLength;
        $this->maxWordLength = $maxWordLength;
    }

    public function updateFromText(string $text): void
    {
        $words = preg_split('/\W+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
        $stmt = $this->db->prepare("INSERT OR IGNORE INTO dictionary (word) VALUES (?)");
        foreach ($words as $word) {
            if (mb_strlen($word) >= $this->minWordLength && mb_strlen($word) <= $this->maxWordLength) {
                $stmt->execute([$word]);
            }
        }
    }

    public function findSimilarWords(string $prefix, int $limit = 100): array
    {
        $stmt = $this->db->prepare("SELECT word FROM dictionary WHERE word LIKE ? LIMIT ?");
        $stmt->execute([$prefix . '%', $limit]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
