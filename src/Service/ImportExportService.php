<?php

namespace SimpleSearch\Service;

use SimpleSearch\Repository\SiteRepository;
use SimpleSearch\Repository\DictionaryRepository;
use SimpleSearch\Helper\TextHelper;

class ImportExportService
{
    private SiteRepository $siteRepo;
    private DictionaryRepository $dictionaryRepo;
    private string $dbPath;

    public function __construct(SiteRepository $siteRepo, DictionaryRepository $dictionaryRepo, string $dbPath)
    {
        $this->siteRepo = $siteRepo;
        $this->dictionaryRepo = $dictionaryRepo;
        $this->dbPath = $dbPath;
    }

    public function exportCsv(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="search-export-' . date('Ymd-His') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id', 'url', 'title', 'description', 'content', 'crawled_at']);
        $rows = $this->siteRepo->exportAll();
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
    }

    public function importCsv(array $file): array
    {
        if (!isset($file['csvfile']) || $file['csvfile']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Please select a valid CSV file.'];
        }

        $handle = fopen($file['csvfile']['tmp_name'], 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'Failed to open uploaded file.'];
        }

        fgetcsv($handle); // skip header
        $count = 0;
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) >= 4) {
                $url = TextHelper::sanitizeUrl(trim($data[1]));
                $title = trim($data[2]);
                $desc = trim($data[3] ?? '');
                $content = trim($data[4] ?? '');
                $crawled = trim($data[5] ?? date('Y-m-d H:i:s'));
                if (empty($url) || empty($title)) continue;
                try {
                    $this->siteRepo->insertWithTimestamp($url, $title, $desc, $content, $crawled);
                    $this->dictionaryRepo->updateFromText("$title $desc $content");
                    $count++;
                } catch (\Exception $e) {}
            }
        }
        fclose($handle);

        return ['success' => true, 'message' => "Imported $count records successfully."];
    }

    public function backup(): void
    {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="search-backup-' . date('Ymd-His') . '.db"');
        header('Content-Length: ' . filesize($this->dbPath));
        readfile($this->dbPath);
    }
}
