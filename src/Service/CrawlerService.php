<?php

namespace SimpleSearch\Service;

use SimpleSearch\Repository\SiteRepository;
use SimpleSearch\Repository\CrawlLogRepository;
use SimpleSearch\Repository\DictionaryRepository;

class CrawlerService
{
    private SiteRepository $siteRepo;
    private CrawlLogRepository $crawlLogRepo;
    private DictionaryRepository $dictionaryRepo;
    private array $config;

    public function __construct(
        SiteRepository $siteRepo,
        CrawlLogRepository $crawlLogRepo,
        DictionaryRepository $dictionaryRepo,
        array $config = []
    ) {
        $this->siteRepo = $siteRepo;
        $this->crawlLogRepo = $crawlLogRepo;
        $this->dictionaryRepo = $dictionaryRepo;
        $this->config = array_merge([
            'timeout' => 15,
            'max_redirects' => 3,
            'user_agent' => 'SimpleSearch/1.0 (+https://example.com/bot)',
            'max_content_length' => 10000,
            'sitemap_timeout' => 30,
        ], $config);
    }

    public function crawlUrl(string $url): array|false
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $html = false;
        $ctxOptions = [
            'http' => [
                'timeout' => $this->config['timeout'],
                'follow_location' => 1,
                'max_redirects' => $this->config['max_redirects'],
                'user_agent' => $this->config['user_agent']
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => $this->config['timeout'],
                CURLOPT_MAXREDIRS => $this->config['max_redirects'],
                CURLOPT_USERAGENT => $this->config['user_agent'],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_ENCODING => ''
            ]);
            $html = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($html === false || $httpCode >= 400) return false;
        } else {
            $html = file_get_contents($url, false, stream_context_create($ctxOptions));
            if ($html === false) return false;
        }

        // Extract title
        preg_match('/<title[^>]*>([^<]*)<\/title>/i', $html, $m);
        $title = isset($m[1]) ? trim($m[1]) : 'No Title';

        // Extract description meta - fixed regex patterns
        $description = '';
        if (preg_match('/<meta\s+[^>]*name\s*=\s*["\']description["\']\s*[^>]*content\s*=\s*["\']([^"\']*)["\'](?:\s|>)/i', $html, $desc)) {
            $description = trim($desc[1]);
        } elseif (preg_match('/<meta\s+[^>]*content\s*=\s*["\']([^"\']*)["\'](?:\s|>)[^>]*name\s*=\s*["\']description["\']/i', $html, $desc)) {
            $description = trim($desc[1]);
        }

        // Clean HTML and extract body text
        $html = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);
        $html = preg_replace('/<nav[^>]*>.*?<\/nav>/si', '', $html);
        $html = preg_replace('/<footer[^>]*>.*?<\/footer>/si', '', $html);
        $html = preg_replace('/<header[^>]*>.*?<\/header>/si', '', $html);
        $body = strip_tags($html);
        $body = preg_replace('/\s+/', ' ', $body);
        $content = mb_substr(trim($body), 0, $this->config['max_content_length']);

        return compact('title', 'description', 'content');
    }

    public function crawlAndSave(string $url): array
    {
        $data = $this->crawlUrl($url);
        if ($data === false) {
            $this->crawlLogRepo->log($url, 'failed', 'Manual crawl failed');
            return ['success' => false, 'message' => 'Failed to fetch URL. Please check the URL and try again.'];
        }

        try {
            $this->siteRepo->insert($url, $data['title'], $data['description'], $data['content']);
            $this->dictionaryRepo->updateFromText("{$data['title']} {$data['description']} {$data['content']}");
            $this->crawlLogRepo->log($url, 'success');
            return ['success' => true, 'message' => 'Successfully crawled and saved: ' . htmlspecialchars($url)];
        } catch (\Exception $e) {
            $this->crawlLogRepo->log($url, 'error', $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function recrawl(int $id): void
    {
        $url = $this->siteRepo->findUrlById($id);
        if ($url) {
            $data = $this->crawlUrl($url);
            if ($data) {
                $this->siteRepo->updateCrawled($id, $data['title'], $data['description'], $data['content']);
                $this->dictionaryRepo->updateFromText("{$data['title']} {$data['description']} {$data['content']}");
                $this->crawlLogRepo->log($url, 'recrawled');
            } else {
                $this->crawlLogRepo->log($url, 'recrawl failed');
            }
        }
    }

    public function bulkCrawl(array $urls): array
    {
        $success = 0;
        $fail = 0;

        foreach ($urls as $url) {
            $url = trim($url);
            if (empty($url)) continue;

            $data = $this->crawlUrl($url);
            if ($data) {
                try {
                    $this->siteRepo->insert($url, $data['title'], $data['description'], $data['content']);
                    $this->dictionaryRepo->updateFromText("{$data['title']} {$data['description']} {$data['content']}");
                    $this->crawlLogRepo->log($url, 'success');
                    $success++;
                } catch (\Exception $e) {
                    $this->crawlLogRepo->log($url, 'error', $e->getMessage());
                    $fail++;
                }
            } else {
                $this->crawlLogRepo->log($url, 'failed', 'Could not fetch or parse URL');
                $fail++;
            }
        }

        return ['success' => $success, 'fail' => $fail];
    }

    public function sitemapCrawl(string $sitemapUrl): array
    {
        $xml = @file_get_contents($sitemapUrl, false, stream_context_create([
            'http' => ['timeout' => $this->config['sitemap_timeout'], 'user_agent' => $this->config['user_agent']],
            'ssl' => ['verify_peer' => false]
        ]));

        if ($xml === false) {
            return ['success' => false, 'message' => 'Could not fetch sitemap. Check URL and try again.'];
        }

        // Handle gzip
        if (substr($xml, 0, 2) === "\x1f\x8b") {
            $xml = gzdecode($xml);
        }

        libxml_use_internal_errors(true);
        $sitemap = simplexml_load_string($xml);

        if ($sitemap === false) {
            return ['success' => false, 'message' => 'Invalid XML format. Please provide a valid sitemap.xml file.'];
        }

        $urls = [];
        if ($sitemap->getName() === 'sitemapindex') {
            foreach ($sitemap->sitemap as $sm) {
                $subUrl = (string)$sm->loc;
                $subXml = @file_get_contents($subUrl, false, stream_context_create([
                    'http' => ['timeout' => $this->config['sitemap_timeout'], 'user_agent' => $this->config['user_agent']],
                    'ssl' => ['verify_peer' => false]
                ]));
                if ($subXml) {
                    if (substr($subXml, 0, 2) === "\x1f\x8b") {
                        $subXml = gzdecode($subXml);
                    }
                    $subMap = simplexml_load_string($subXml);
                    if ($subMap && $subMap->getName() === 'urlset') {
                        foreach ($subMap->url as $u) {
                            $urls[] = (string)$u->loc;
                        }
                    }
                }
            }
        } elseif ($sitemap->getName() === 'urlset') {
            foreach ($sitemap->url as $u) {
                $urls[] = (string)$u->loc;
            }
        }

        $result = $this->bulkCrawl(array_unique($urls));
        return [
            'success' => true,
            'message' => "Sitemap crawl complete: {$result['success']} succeeded, {$result['fail']} failed."
        ];
    }
}
