<?php
class FilterBar
{
    public static function render($genres = [])
    {
        return '
        <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 mb-6 border border-gray-200 dark:border-neutral-700">
            <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white text-center">Find Your Perfect Movie in Minutes</h2>
            
            <!-- Genre Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium mb-3 text-gray-700 dark:text-gray-300">Select Up to 3 Genres</label>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2" id="genreContainer">
                    ' . self::renderGenreButtons($genres) . '
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Selected: <span id="selectedCount">0</span>/3</p>
            </div>

            <!-- Runtime & Rating Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Min Runtime (minutes)</label>
                    <input type="range" id="minRuntime" min="60" max="180" value="90" 
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-neutral-600 slider">
                    <span class="text-sm text-gray-600 dark:text-gray-400" id="minRuntimeValue">90 min</span>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Max Runtime (minutes)</label>
                    <input type="range" id="maxRuntime" min="60" max="200" value="150" 
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-neutral-600 slider">
                    <span class="text-sm text-gray-600 dark:text-gray-400" id="maxRuntimeValue">150 min</span>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Min Rating</label>
                    <input type="range" id="minRating" min="1" max="10" value="6" step="0.1"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-neutral-600 slider">
                    <span class="text-sm text-gray-600 dark:text-gray-400" id="minRatingValue">6.0</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button id="findMovies" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium transition-colors">
                    Find Movies
                </button>
                <button id="randomMovie" class="px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-md font-medium transition-colors">
                    Random Pick
                </button>
                <button id="clearFilters" class="px-8 py-3 bg-gray-300 dark:bg-neutral-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 dark:hover:bg-neutral-500 transition-colors">
                    Clear All
                </button>
            </div>
        </div>';
    }

    private static function renderGenreButtons($genres)
    {
        $buttons = '';
        foreach ($genres as $genre) {
            $buttons .= '<button type="button" 
                class="genre-btn px-3 py-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-neutral-600 transition-colors" 
                data-genre-id="' . $genre['id'] . '"
                data-genre-name="' . $genre['name'] . '">'
                . $genre['name'] .
                '</button>';
        }
        return $buttons;
    }
}
