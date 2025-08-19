<?php
header('Content-Type: application/json');
require_once '../controllers/movieController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['query'])) {
        echo json_encode(['movies' => []]);
        exit;
    }
    
    $movieController = new MovieController();
    $movies = $movieController->searchMovies($input['query']);
    
    echo json_encode(['movies' => $movies]);
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
