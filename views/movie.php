<?php
require_once '../config.php';

// Validate movie ID param
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "Invalid movie ID";
    exit;
}
$movieId = intval($_GET['id']);
$apiKey = TMDB_API_KEY;
$baseUrl = TMDB_BASE_URL;

// Helper to fetch from TMDB API
function tmdbGet($endpoint)
{
    global $apiKey, $baseUrl;
    $url = "{$baseUrl}{$endpoint}&api_key={$apiKey}&language=en-US";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Get movie details, credits, videos, and recommendations movies in a single request
$movieDetails = tmdbGet("/movie/{$movieId}?append_to_response=credits,videos,recommendations");

// Extract credits data
$credits = $movieDetails['credits'] ?? [];
$cast = array_slice($credits['cast'] ?? [], 0, 12);

$directorsCrew = [];
$writersCrew = [];
if (!empty($credits['crew'])) {
    foreach ($credits['crew'] as $crewMember) {
        if ($crewMember['job'] === 'Director') {
            $directorsCrew[] = $crewMember;
        }
        if (in_array($crewMember['job'], ['Writer', 'Screenplay', 'Author', 'Story'])) {
            $writersCrew[] = $crewMember;
        }
    }
}

$directorsCrew = array_unique($directorsCrew);
$writersCrew = array_unique($writersCrew);

// Formatting runtime to hh:mm
function formatRuntime($runtime)
{
    if (empty($runtime)) return 'N/A';
    $hours = floor($runtime / 60);
    $minutes = $runtime % 60;
    return "{$hours}h {$minutes}m";
}

// Trailer key
$trailerKey = null;
if (!empty($movieDetails['videos']['results'])) {
    foreach ($movieDetails['videos']['results'] as $video) {
        if ($video['type'] === 'Trailer' && $video['site'] === 'YouTube') {
            $trailerKey = $video['key'];
            break;
        }
    }
}

// recommendations movies
$recommendationsMovies = $movieDetails['recommendations']['results'] ?? [];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($movieDetails['title'] ?? 'Movie Details') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .initials-circle {
            height: 48px;
            width: 48px;
            border-radius: 9999px;
            background-color: #4B5563;
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        /* WebKit browsers */
        ::-webkit-scrollbar {
            height: 1px;
            width: 1px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 1px;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-neutral-900 text-gray-900 dark:text-gray-100 min-h-screen relative p-4">
    <a href="../index.php" class="block mb-4 text-blue-600 dark:text-blue-400">← Back to main menu</a>

    <!-- Top Poster -->
    <div class="md:flex md:space-x-8 max-w-7xl mx-auto">
        <div class="md:w-2/3 mb-6 md:mb-0 relative">
            <?php if (!empty($movieDetails['backdrop_path'])): ?>
                <img class="w-full h-[60vh] object-cover rounded-lg"
                    src="https://image.tmdb.org/t/p/original<?= htmlspecialchars($movieDetails['backdrop_path']) ?>"
                    alt="Backdrop for <?= htmlspecialchars($movieDetails['title']) ?>" />
            <?php else: ?>
                <div
                    class="w-full h-[60vh] bg-gray-300 dark:bg-neutral-700 rounded-lg flex items-center justify-center text-gray-500">No
                    backdrop available</div>
            <?php endif; ?>
        </div>
        <div class="md:w-1/3">
            <div class="flex space-x-4 items-start">
                <div class="flex-1">
                    <h1 class="text-4xl font-extrabold mb-2"><?= htmlspecialchars($movieDetails['title'] ?? 'Untitled') ?></h1>
                    <p class="text-gray-600 dark:text-gray-400 mb-2 italic"><?= htmlspecialchars($movieDetails['tagline'] ?? '') ?></p>
                </div>
                <div class="w-28 flex-shrink-0">
                    <?php if (!empty($movieDetails['poster_path'])): ?>
                        <img class="rounded-lg shadow-lg"
                            src="https://image.tmdb.org/t/p/w300<?= htmlspecialchars($movieDetails['poster_path']) ?>"
                            alt="Poster of <?= htmlspecialchars($movieDetails['title']) ?>" />
                    <?php else: ?>
                        <div
                            class="w-28 h-40 bg-gray-300 dark:bg-neutral-700 rounded-lg flex items-center justify-center text-gray-500">No
                            poster</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Details -->
            <div class="mt-5 text-gray-700 dark:text-gray-300 space-y-1">
                <p><span class="font-semibold">Release Date:</span> <?= htmlspecialchars($movieDetails['release_date'] ?? 'N/A') ?></p>
                <p><span class="font-semibold">Score:</span> <?= htmlspecialchars($movieDetails['vote_average'] ?? 'N/A') ?>/10</p>
                <p><span class="font-semibold">Runtime:</span> <?= formatRuntime($movieDetails['runtime'] ?? null) ?></p>
                <p><span class="font-semibold">Genres:</span>
                    <?= implode(', ', array_map(function ($g) {
                        return htmlspecialchars($g['name']);
                    }, $movieDetails['genres'] ?? [])) ?>
                </p>
                <?php if (!empty($movieDetails['production_companies'])): ?>
                    <p><span class="font-semibold">Production:</span>
                        <?= implode(', ', array_map(function ($c) {
                            return htmlspecialchars($c['name']);
                        }, $movieDetails['production_companies'])) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Overview -->
    <section class="max-w-7xl mx-auto my-8 px-4 md:px-0">
        <h2 class="text-2xl font-bold mb-2">Overview</h2>
        <p class="text-gray-800 dark:text-gray-300 leading-relaxed"><?= nl2br(htmlspecialchars($movieDetails['overview'] ?? 'No overview available')) ?></p>
    </section>

    <!-- Director and Writers -->
    <section class="max-w-7xl mx-auto my-8 px-4 md:px-0">
        <h2 class="text-2xl font-bold mb-4">Director<?= count($directorsCrew) > 1 ? 's' : '' ?></h2>
        <div class="flex space-x-6">


            <?php foreach ($directorsCrew as $director):
                $profile = $director['profile_path'] ? "https://image.tmdb.org/t/p/w185" . htmlspecialchars($director['profile_path']) : null;
                $initials = implode('', array_map(function ($n) {
                    return strtoupper($n[0]);
                }, explode(' ', $director['name'])));
            ?>

                <div class="text-center">
                    <?php if ($profile): ?>
                        <img src="<?= $profile ?>" alt="<?= htmlspecialchars($director['name']) ?>" class="mx-auto rounded-full h-24 w-24 object-cover mb-2 shadow-md" loading="lazy" />
                    <?php else: ?>
                        <div class="initials-circle mx-auto mb-2"><?= htmlspecialchars($initials) ?></div>
                    <?php endif; ?>
                    <p class="font-semibold"><?= htmlspecialchars($director['name']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Cast -->
    <section class="max-w-7xl mx-auto my-8 px-4 md:px-0">
        <h2 class="text-2xl font-bold mb-4">Cast</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-6">
            <?php foreach ($cast as $actor):
                $profile = $actor['profile_path'] ? "https://image.tmdb.org/t/p/w185" . htmlspecialchars($actor['profile_path']) : null;
                $initials = implode('', array_map(function ($n) {
                    return strtoupper($n[0]);
                }, explode(' ', $actor['name'])));
            ?>
                <div class="text-center">
                    <?php if ($profile): ?>
                        <img src="<?= $profile ?>" alt="<?= htmlspecialchars($actor['name']) ?>" class="mx-auto rounded-full h-24 w-24 object-cover mb-2 shadow-md" loading="lazy" />
                    <?php else: ?>
                        <div class="initials-circle mx-auto mb-2"><?= htmlspecialchars($initials) ?></div>
                    <?php endif; ?>
                    <p class="font-semibold"><?= htmlspecialchars($actor['name']) ?></p>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($actor['character'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Trailer -->
    <?php if ($trailerKey): ?>
        <section class="max-w-7xl mx-auto my-8 px-4 md:px-0">
            <h2 class="text-2xl font-bold mb-4">Trailer</h2>
            <div class="aspect-w-16 aspect-h-9">
                <iframe class="w-full h-96 rounded-lg"
                    src="https://www.youtube.com/embed/<?= htmlspecialchars($trailerKey) ?>"
                    title="Movie Trailer" allowfullscreen></iframe>
            </div>
        </section>
    <?php endif; ?>

    <!-- recommendations Movies -->
    <?php if (!empty($recommendationsMovies)) { ?>
        <section class="max-w-7xl overflow-x-auto mx-auto my-8 px-4 md:px-0">
            <h2 class="text-2xl font-bold mb-4">recommendations Movies</h2>
            <div class="flex space-x-5 overflow-x-auto w-full py-4">
                <?php foreach ($recommendationsMovies as $recommendations):
                    $poster = $recommendations['poster_path'] ? "https://image.tmdb.org/t/p/w300" . htmlspecialchars($recommendations['poster_path']) : 'assets/images/placeholder-poster.jpg';
                    $relYear = isset($recommendations['release_date']) ? date('Y', strtotime($recommendations['release_date'])) : 'N/A';
                ?>
                    <a href="movie.php?id=<?= $recommendations['id'] ?>" class="flex-shrink-0 w-48 bg-white dark:bg-neutral-800 rounded-lg overflow-hidden border border-gray-200 dark:border-neutral-700 hover:shadow-lg transition-transform transform hover:scale-105">
                        <img src="<?= $poster ?>" alt="<?= htmlspecialchars($recommendations['title']) ?>" class="w-full h-64 object-cover" loading="lazy" />
                        <div class="p-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-2"><?= htmlspecialchars($recommendations['title']) ?></h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?= $relYear ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php } ?>

</body>

</html>