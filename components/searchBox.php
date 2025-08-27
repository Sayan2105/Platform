<?php
class SearchBox
{
    public static function render()
    {
        return '
        <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 mb-6 border border-gray-200 dark:border-neutral-700">
            <div class="relative">
                <input type="text" id="movieSearch" placeholder="Search for movies..." 
                    class="w-full px-4 py-3 pr-12 border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent">
                <button id="searchBtn" class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-blue-600 transition-colors">
                    Search
                </button>
            </div>
        </div>';
    }
}
