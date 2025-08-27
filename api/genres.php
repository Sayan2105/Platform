<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../controllers/movieController.php';

$controller = new MovieController();

$genres = $controller->getGenres();

echo json_encode(['success' => true, 'genres' => $genres]);
