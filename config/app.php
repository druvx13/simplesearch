<?php

return [
    'name' => 'SimpleSearch',
    'version' => '1.0.0',
    'results_per_page' => 50,
    'crawl' => [
        'timeout' => 15,
        'max_redirects' => 3,
        'user_agent' => 'SimpleSearch/1.0 (+https://example.com/bot)',
        'max_content_length' => 10000,
    ],
    'sitemap' => [
        'timeout' => 30,
    ],
    'dictionary' => [
        'min_word_length' => 2,
        'max_word_length' => 30,
        'max_suggestion_distance' => 2,
    ],
];
