<?php

// Resolve the database path relative to the project root
// This ensures it works regardless of which directory is the web root
$projectRoot = dirname(__DIR__);

return [
    'driver' => 'sqlite',
    // Default: store in data/ directory inside project root
    'path' => $projectRoot . '/data/search.db',
    // Fallback: look for search.db next to the old index.php (project root)
    'fallback_path' => $projectRoot . '/search.db',
];
