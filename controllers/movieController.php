<?php
require_once 'models/TMDBModel.php';


class MovieController {
    private $tmdbModel;
    
    public function __construct() {
        $this->tmdbModel = new TMDBModel();
    }
    
    public function getGenres() {
        return $this->tmdbModel->getGenres();
    }
    
    public function discoverMovies($filters) {
        return $this->tmdbModel->discoverMovies($filters);
    }
    
    public function searchMovies($query) {
        return $this->tmdbModel->searchMovies($query);
    }
    
    public function getRandomMovie($movies) {
        if (!empty($movies)) {
            return $movies[array_rand($movies)];
        }
        return null;
    }
}
?>
