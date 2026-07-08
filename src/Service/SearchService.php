<?php

namespace SimpleSearch\Service;

use SimpleSearch\Repository\SiteRepository;
use SimpleSearch\Repository\QueryLogRepository;
use SimpleSearch\Repository\DictionaryRepository;

class SearchService
{
    private SiteRepository $siteRepo;
    private QueryLogRepository $queryLogRepo;
    private DictionaryRepository $dictionaryRepo;
    private int $resultsPerPage;
    private int $maxSuggestionDistance;

    public function __construct(
        SiteRepository $siteRepo,
        QueryLogRepository $queryLogRepo,
        DictionaryRepository $dictionaryRepo,
        int $resultsPerPage = 50,
        int $maxSuggestionDistance = 2
    ) {
        $this->siteRepo = $siteRepo;
        $this->queryLogRepo = $queryLogRepo;
        $this->dictionaryRepo = $dictionaryRepo;
        $this->resultsPerPage = $resultsPerPage;
        $this->maxSuggestionDistance = $maxSuggestionDistance;
    }

    public function search(string $query, int $page = 1): array
    {
        $query = trim($query);
        if ($query === '') {
            return $this->emptyResult();
        }

        $this->queryLogRepo->log($query);

        $currentPage = max(1, $page);
        $offset = ($currentPage - 1) * $this->resultsPerPage;
        $results = [];
        $totalResults = 0;

        // Try FTS5 first
        $ftsAvailable = $this->siteRepo->ftsHasData();

        if ($ftsAvailable) {
            $ftsQuery = $this->buildFtsQuery($query);

            try {
                $totalResults = $this->siteRepo->countFts($ftsQuery);
                $results = $this->siteRepo->searchFts($ftsQuery, $this->resultsPerPage, $offset);
            } catch (\Exception $e) {
                $ftsAvailable = false;
            }
        }

        // Fallback to LIKE
        if (!$ftsAvailable || empty($results)) {
            $totalResults = $this->siteRepo->countLike($query);
            $results = $this->siteRepo->searchLike($query, $this->resultsPerPage, $offset);
        }

        // "Did you mean?" if no results
        $didYouMean = '';
        if (empty($results)) {
            $didYouMean = $this->didYouMean($query);
        }

        $totalPages = (int) ceil($totalResults / $this->resultsPerPage);

        return [
            'query' => $query,
            'results' => $results,
            'totalResults' => $totalResults,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'didYouMean' => $didYouMean,
            'resultsPerPage' => $this->resultsPerPage,
        ];
    }

    public function suggest(string $prefix): array
    {
        $prefix = trim($prefix);
        if ($prefix === '' || mb_strlen($prefix) < 1) {
            return [];
        }
        return $this->queryLogRepo->suggest($prefix);
    }

    private function buildFtsQuery(string $query): string
    {
        $terms = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
        return implode(' ', array_map(function ($t) {
            return '"' . str_replace('"', '""', $t) . '"*';
        }, $terms));
    }

    private function didYouMean(string $query): string
    {
        $words = preg_split('/\W+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($words)) {
            return '';
        }

        $corrected = [];
        foreach ($words as $w) {
            $wLower = mb_strtolower($w);
            $dictWords = $this->dictionaryRepo->findSimilarWords($wLower[0]);

            $bestWord = $w;
            $bestDist = PHP_INT_MAX;
            foreach ($dictWords as $dw) {
                $dist = levenshtein($wLower, $dw);
                if ($dist < $bestDist && $dist <= $this->maxSuggestionDistance && $dist > 0) {
                    $bestDist = $dist;
                    $bestWord = $dw;
                }
            }
            $corrected[] = $bestWord;
        }

        $result = implode(' ', $corrected);
        return ($result !== $query) ? $result : '';
    }

    private function emptyResult(): array
    {
        return [
            'query' => '',
            'results' => [],
            'totalResults' => 0,
            'currentPage' => 1,
            'totalPages' => 0,
            'didYouMean' => '',
            'resultsPerPage' => $this->resultsPerPage,
        ];
    }
}
