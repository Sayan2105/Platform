<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../controllers/movieController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE || empty($input['query'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input or empty query']);
    exit;
}

$query = $input['query'];
$page = $input['page'] ?? 1;

$controller = new MovieController();
$search = $controller->searchAll($query, $page);

echo json_encode(['success' => true, 'page' => $search['page'], 'total_pages' => $search['total_pages'], 'movies' => $search['results']]);
