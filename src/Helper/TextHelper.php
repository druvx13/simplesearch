<?php

namespace SimpleSearch\Helper;

class TextHelper
{
    public static function snippet(string $text, int $maxLen = 200): string
    {
        $text = trim($text);
        if (mb_strlen($text) > $maxLen) {
            $text = mb_substr($text, 0, $maxLen) . '...';
        }
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeUrl(string $url): string
    {
        $url = trim($url);
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . preg_replace('/^\/\//', '', $url);
        }
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}
