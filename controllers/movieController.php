<?php
require_once __DIR__ . '/../models/TMDBModel.php';

class MovieController
{
    private $tmdb;

    public function __construct()
    {
        $this->tmdb = new TMDBModel();
    }

    public function getGenres()
    {
        return $this->tmdb->getGenres();
    }

    public function discoverMovies($filters = [], $page = 1, $type = 'movie')
    {
        return $this->tmdb->discoverMovies($filters, $page, $type);
    }

    public function searchAll($query, $page = 1)
    {
        return $this->tmdb->searchAll($query, $page);
    }
}
