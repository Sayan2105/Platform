<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../controllers/movieController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

$filters = $input;
$page = $input['page'] ?? 1;
$type = $input['type'] ?? 'movie'; // add UI switch for 'movie', 'tv', or 'all' if desired

$controller = new MovieController();
$discovery = $controller->discoverMovies($filters, $page, $type);

echo json_encode(['success' => true, 'page' => $discovery['page'], 'total_pages' => $discovery['total_pages'], 'movies' => $discovery['results']]);
