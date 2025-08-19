<?php
require_once 'config.php';
error_log("In the controller");
class TMDBModel {
    private $apiKey;
    private $baseUrl;
    
    public function __construct() {
        $this->apiKey = TMDB_API_KEY;
        $this->baseUrl = TMDB_BASE_URL;
    }
    
    public function getGenres() {
        $url = $this->baseUrl . "/genre/movie/list?api_key=" . $this->apiKey;
        $response = $this->makeRequest($url);
        return $response['genres'] ?? [];
    }
    
    public function discoverMovies($filters = []) {
        $params = [
            'api_key' => $this->apiKey,
            'sort_by' => 'popularity.desc',
            'include_adult' => 'false',
            'include_video' => 'false',
            'page' => 1
        ];
        
        // Add filters
        if (!empty($filters['genres'])) {
            $params['with_genres'] = implode(',', $filters['genres']);
        }
        if (!empty($filters['min_runtime'])) {
            $params['with_runtime.gte'] = $filters['min_runtime'];
        }
        if (!empty($filters['max_runtime'])) {
            $params['with_runtime.lte'] = $filters['max_runtime'];
        }
        if (!empty($filters['min_rating'])) {
            $params['vote_average.gte'] = $filters['min_rating'];
        }
        
        $url = $this->baseUrl . "/discover/movie?" . http_build_query($params);
        $response = $this->makeRequest($url);
        
        return $this->enrichMovieData($response['results'] ?? []);
    }
    
    public function searchMovies($query) {
        $params = [
            'api_key' => $this->apiKey,
            'query' => $query,
            'include_adult' => 'false'
        ];
        
        $url = $this->baseUrl . "/search/movie?" . http_build_query($params);
        $response = $this->makeRequest($url);
        
        return $this->enrichMovieData($response['results'] ?? []);
    }
    
    private function enrichMovieData($movies) {
        foreach ($movies as &$movie) {
            // Get additional details for each movie
            $details = $this->getMovieDetails($movie['id']);
            $credits = $this->getMovieCredits($movie['id']);
            
            $movie['runtime'] = $details['runtime'] ?? 'N/A';
            $movie['director'] = $this->getDirector($credits);
            $movie['cast'] = $this->getTopCast($credits, 3);
        }
        return $movies;
    }
    
    public function getMovieDetails($movieId) {
        $url = $this->baseUrl . "/movie/{$movieId}?api_key=" . $this->apiKey;
        return $this->makeRequest($url);
    }
    
    public function getMovieCredits($movieId) {
        $url = $this->baseUrl . "/movie/{$movieId}/credits?api_key=" . $this->apiKey;
        return $this->makeRequest($url);
    }
    
    private function getDirector($credits) {
        if (isset($credits['crew'])) {
            foreach ($credits['crew'] as $person) {
                if ($person['job'] === 'Director') {
                    return $person['name'];
                }
            }
        }
        return 'N/A';
    }
    
    private function getTopCast($credits, $limit = 3) {
        if (isset($credits['cast']) && !empty($credits['cast'])) {
            $topCast = array_slice($credits['cast'], 0, $limit);
            return implode(', ', array_column($topCast, 'name'));
        }
        return 'N/A';
    }
    
    private function makeRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return [];
    }
}
?>
