<?php
require_once 'config.php';
require_once 'controllers/movieController.php';
require_once 'components/filterBar.php';
require_once 'components/movieCard.php';
require_once 'components/searchBox.php';

$movieController = new MovieController();
$genres = $movieController->getGenres();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= APP_NAME ?> - <?= APP_TAGLINE ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <header class="bg-white dark:bg-neutral-800 border-b border-gray-200 dark:border-neutral-700">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?= APP_NAME ?></h1>
                <p class="text-gray-600 dark:text-gray-400"><?= APP_TAGLINE ?></p>
            </div>
            <button id="darkModeToggle" class="p-2 rounded-lg bg-gray-200 dark:bg-neutral-700 text-gray-700 dark:text-gray-300">Go Dark</button>
        </div>
    </header>
    <main class="max-w-7xl mx-auto px-4 py-8">
        <?= SearchBox::render() ?>
        <?= FilterBar::render($genres) ?>
        <div id="loadingSpinner" class="hidden text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Finding awesome movies...</p>
        </div>
        <div id="resultsCounter" class="hidden mb-4 text-center">
            <p class="text-gray-600 dark:text-gray-400">Found <span id="movieCount">0</span> movies matching your criteria</p>
        </div>
        <div id="moviesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"></div>
        <div id="noResults" class="hidden text-center py-12">
            <div class="text-6xl mb-4"></div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No movies found</h3>
            <p class="text-gray-600 dark:text-gray-400">Try adjusting your filters or search terms</p>
        </div>
    </main>
    <script src="assets/js/app.js"></script>
</body>

</html>