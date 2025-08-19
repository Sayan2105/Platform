<?php
class MovieCard {
    public static function render($movie) {
        $year = date('Y', strtotime($movie['release_date'] ?? 'now'));
        $rating = number_format($movie['vote_average'] ?? 0, 1);
        $posterUrl = $movie['poster_path'] ? 
            'https://image.tmdb.org/t/p/w300' . $movie['poster_path'] : 
            'assets/images/placeholder-poster.jpg';
        
        return '
        <div class="bg-white dark:bg-neutral-800 rounded-lg overflow-hidden border border-gray-200 dark:border-neutral-700 hover:shadow-xl transition-all duration-300 transform hover:scale-105">
            <!-- Poster Image -->
            <div class="relative overflow-hidden">
                <img src="' . $posterUrl . '" 
                     alt="' . htmlspecialchars($movie['title']) . '"
                     class="w-full h-80 object-cover">
                <div class="absolute top-2 right-2">
                    <span class="bg-yellow-500 text-black px-2 py-1 rounded-full text-xs font-bold">
                        ' . $rating . '
                    </span>
                </div>
            </div>
            
            <!-- Movie Details -->
            <div class="p-4">
                <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white line-clamp-2">
                    ' . htmlspecialchars($movie['title']) . '
                </h3>
                
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm text-gray-600 dark:text-gray-400">' . $year . '</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">' . ($movie['runtime'] ?? 'N/A') . ' min</span>
                </div>
                
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-4 line-clamp-3">
                    ' . htmlspecialchars(substr($movie['overview'] ?? '', 0, 120)) . '...
                </p>
                
                <!-- Cast & Director Info -->
                <div class="mb-4 text-xs">
                    <p class="text-gray-600 dark:text-gray-400 mb-1">
                        <span class="font-medium">Director:</span> ' . ($movie['director'] ?? 'N/A') . '
                    </p>
                    <p class="text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Cast:</span> ' . ($movie['cast'] ?? 'N/A') . '
                    </p>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <button class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md transition-colors">
                        View Details
                    </button>
                    <button class="px-3 py-2 border border-gray-300 dark:border-neutral-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
                    Fav
                    </button>
                </div>
            </div>
        </div>';
    }
}
?>
