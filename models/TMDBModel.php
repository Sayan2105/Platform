<?php
require_once __DIR__ . '/../config.php';

class TMDBModel
{
    private $apiKey;
    private $baseUrl;
    private $cacheDir;

    public function __construct()
    {
        $this->apiKey = TMDB_API_KEY;
        $this->baseUrl = TMDB_BASE_URL;
        $this->cacheDir = __DIR__ . '/../cache/';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    // Cache utility: uses file cache for TMDB responses with 1 hour expiry
    private function getCache($key)
    {
        $file = $this->cacheDir . md5($key) . '.json';
        if (file_exists($file) && filemtime($file) + 3600 > time()) {
            return json_decode(file_get_contents($file), true);
        }
        return null;
    }

    private function setCache($key, $data)
    {
        $file = $this->cacheDir . md5($key) . '.json';
        file_put_contents($file, json_encode($data));
    }

    // Simplified request with caching
    private function makeRequest(string $url): array
    {
        $cached = $this->getCache($url);
        if ($cached !== null) {
            return $cached;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $json = json_decode($response, true);
            if ($json !== null) {
                $this->setCache($url, $json);
                return $json;
            }
        }
        return [];
    }

    // Get genres from movie and tv endpoints combined
    public function getGenres()
    {
        $movieGenres = $this->makeRequest("{$this->baseUrl}/genre/movie/list?api_key={$this->apiKey}")['genres'] ?? [];
        $tvGenres = $this->makeRequest("{$this->baseUrl}/genre/tv/list?api_key={$this->apiKey}")['genres'] ?? [];

        // Merge and deduplicate by id
        $allGenres = [];
        $seenIds = [];
        foreach (array_merge($movieGenres, $tvGenres) as $genre) {
            if (!in_array($genre['id'], $seenIds)) {
                $allGenres[] = $genre;
                $seenIds[] = $genre['id'];
            }
        }
        return $allGenres;
    }

    // Discover movies with pagination and minimal enrichment
    public function discoverMovies($filters = [], $page = 1, $type = 'movie')
    {
        $endpoint = ($type === 'tv') ? '/discover/tv' : '/discover/movie';

        $params = [
            'api_key' => $this->apiKey,
            'sort_by' => 'popularity.desc',
            'include_adult' => 'false',
            'include_video' => 'false',
            'page' => $page,
            'vote_average.gte' => $filters['min_rating'] ?? 1
        ];

        if (!empty($filters['genres'])) {
            $params['with_genres'] = implode(',', $filters['genres']);
        }

        // For TV, runtime filters differ; skip or adjust as needed
        if ($type === 'movie') {
            if (!empty($filters['min_runtime'])) {
                $params['with_runtime.gte'] = $filters['min_runtime'];
            }
            if (!empty($filters['max_runtime'])) {
                $params['with_runtime.lte'] = $filters['max_runtime'];
            }
        }

        $url = $this->baseUrl . $endpoint . '?' . http_build_query($params);
        $response = $this->makeRequest($url);
        $results = $response['results'] ?? [];

        // Minimal enrichment for fast response: add type field, only basic info
        foreach ($results as &$item) {
            $item['type'] = $type;
        }

        return [
            'page' => $response['page'] ?? $page,
            'total_pages' => $response['total_pages'] ?? 1,
            'results' => $results,
        ];
    }

    // Search movies and TV shows separately, merge results for frontend
    public function searchAll($query, $page = 1)
    {
        $movieResults = $this->search('movie', $query, $page);
        $tvResults = $this->search('tv', $query, $page);

        // Merge and sort by popularity desc (example)
        $allResults = array_merge($movieResults['results'], $tvResults['results']);
        usort($allResults, fn($a, $b) => ($b['popularity'] ?? 0) <=> ($a['popularity'] ?? 0));

        return [
            'page' => $page,
            'total_pages' => max($movieResults['total_pages'], $tvResults['total_pages']),
            'results' => $allResults
        ];
    }

    private function search($type, $query, $page = 1)
    {
        $endpoint = ($type === 'tv') ? '/search/tv' : '/search/movie';

        $params = [
            'api_key' => $this->apiKey,
            'query' => $query,
            'page' => $page,
            'include_adult' => false,
        ];

        $url = $this->baseUrl . $endpoint . '?' . http_build_query($params);
        $response = $this->makeRequest($url);
        $results = $response['results'] ?? [];

        foreach ($results as &$item) {
            $item['type'] = $type;
        }

        return [
            'page' => $response['page'] ?? $page,
            'total_pages' => $response['total_pages'] ?? 1,
            'results' => $results,
        ];
    }
}
