<?php

namespace SimpleSearch\Service;

use SimpleSearch\Repository\DictionaryRepository;

class DictionaryService
{
    private DictionaryRepository $dictionaryRepo;
    private int $maxSuggestionDistance;

    public function __construct(DictionaryRepository $dictionaryRepo, int $maxSuggestionDistance = 2)
    {
        $this->dictionaryRepo = $dictionaryRepo;
        $this->maxSuggestionDistance = $maxSuggestionDistance;
    }

    public function updateFromText(string $text): void
    {
        $this->dictionaryRepo->updateFromText($text);
    }

    public function didYouMean(string $query): string
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
}
