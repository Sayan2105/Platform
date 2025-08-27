class MovieApp {
  constructor() {
    this.selectedGenres = [];
    this.currentMovies = [];
    this.maxGenres = 3;

    this.init();
  }

  init() {
    this.setupEventListeners();
    this.updateSliderValues();
    this.checkDarkMode();
  }

  setupEventListeners() {
    // Genre selection
    document.querySelectorAll(".genre-btn").forEach((btn) => {
      btn.addEventListener("click", (e) => this.toggleGenre(e));
    });

    // Sliders
    const sliders = ["minRuntime", "maxRuntime", "minRating"];
    sliders.forEach((id) => {
      const slider = document.getElementById(id);
      if (slider) {
        slider.addEventListener("input", () => this.updateSliderValues());
      }
    });

    // Buttons
    const findBtn = document.getElementById("findMovies");
    const randomBtn = document.getElementById("randomMovie");
    const clearBtn = document.getElementById("clearFilters");
    const searchBtn = document.getElementById("searchBtn");
    const darkToggle = document.getElementById("darkModeToggle");

    if (findBtn) findBtn.addEventListener("click", () => this.findMovies());
    if (randomBtn)
      randomBtn.addEventListener("click", () => this.getRandomMovie());
    if (clearBtn) clearBtn.addEventListener("click", () => this.clearFilters());
    if (searchBtn)
      searchBtn.addEventListener("click", () => this.searchMovies());
    if (darkToggle)
      darkToggle.addEventListener("click", () => this.toggleDarkMode());

    // Search on Enter
    const searchInput = document.getElementById("movieSearch");
    if (searchInput) {
      searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") this.searchMovies();
      });
    }
  }

  toggleGenre(e) {
    const btn = e.target;
    const genreId = btn.dataset.genreId;
    const genreName = btn.dataset.genreName;

    if (this.selectedGenres.includes(genreId)) {
      // Remove genre
      this.selectedGenres = this.selectedGenres.filter((id) => id !== genreId);
      btn.classList.remove(
        "bg-blue-100",
        "dark:bg-blue-900",
        "border-blue-500"
      );
      btn.classList.add("bg-white", "dark:bg-neutral-700");
    } else if (this.selectedGenres.length < this.maxGenres) {
      // Add genre
      this.selectedGenres.push(genreId);
      btn.classList.remove("bg-white", "dark:bg-neutral-700");
      btn.classList.add("bg-blue-100", "dark:bg-blue-900", "border-blue-500");
    }

    this.updateSelectedCount();
  }

  updateSelectedCount() {
    const counter = document.getElementById("selectedCount");
    if (counter) {
      counter.textContent = this.selectedGenres.length;
    }
  }

  updateSliderValues() {
    const minRuntime = document.getElementById("minRuntime");
    const maxRuntime = document.getElementById("maxRuntime");
    const minRating = document.getElementById("minRating");

    if (minRuntime) {
      document.getElementById("minRuntimeValue").textContent =
        minRuntime.value + " min";
    }
    if (maxRuntime) {
      document.getElementById("maxRuntimeValue").textContent =
        maxRuntime.value + " min";
    }
    if (minRating) {
      document.getElementById("minRatingValue").textContent =
        minRating.value + " ⭐";
    }
  }

  async findMovies() {
    this.showLoading(true);

    const filters = this.getFilters();

    try {
      const response = await fetch("api/get-movies.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(filters),
      });

      const data = await response.json();
      this.currentMovies = data.movies || [];
      this.displayMovies(this.currentMovies);
    } catch (error) {
      console.error("Error fetching movies:", error);
      this.showError("Failed to fetch movies. Please try again.");
    }

    this.showLoading(false);
  }

  async searchMovies() {
    const query = document.getElementById("movieSearch").value.trim();
    if (!query) return;

    this.showLoading(true);

    try {
      const response = await fetch("api/search-movies.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ query }),
      });

      const data = await response.json();
      this.currentMovies = data.movies || [];
      this.displayMovies(this.currentMovies);
    } catch (error) {
      console.error("Error searching movies:", error);
      this.showError("Failed to search movies. Please try again.");
    }

    this.showLoading(false);
  }

  getRandomMovie() {
    if (this.currentMovies.length === 0) {
      this.findMovies().then(() => {
        if (this.currentMovies.length > 0) {
          const randomMovie =
            this.currentMovies[
              Math.floor(Math.random() * this.currentMovies.length)
            ];
          this.displayMovies([randomMovie]);
        }
      });
    } else {
      const randomMovie =
        this.currentMovies[
          Math.floor(Math.random() * this.currentMovies.length)
        ];
      this.displayMovies([randomMovie]);
    }
  }

  getFilters() {
    return {
      genres: this.selectedGenres,
      min_runtime: document.getElementById("minRuntime")?.value || 60,
      max_runtime: document.getElementById("maxRuntime")?.value || 180,
      min_rating: document.getElementById("minRating")?.value || 1,
    };
  }

  displayMovies(movies) {
    const grid = document.getElementById("moviesGrid");
    const counter = document.getElementById("resultsCounter");
    const noResults = document.getElementById("noResults");
    const movieCount = document.getElementById("movieCount");

    if (movies.length === 0) {
      grid.innerHTML = "";
      counter.classList.add("hidden");
      noResults.classList.remove("hidden");
      return;
    }

    // Update counter
    if (movieCount) movieCount.textContent = movies.length;
    counter.classList.remove("hidden");
    noResults.classList.add("hidden");

    // Render movies
    grid.innerHTML = movies
      .map((movie) => this.createMovieCard(movie))
      .join("");
  }

  createMovieCard(movie) {
    const year = movie.release_date ? new Date(movie.release_date) : "N/A";
    const rating = movie.vote_average ? movie.vote_average.toFixed(1) : "N/A";
    const posterUrl = movie.poster_path
      ? `https://image.tmdb.org/t/p/w300${movie.poster_path}`
      : "assets/images/placeholder-poster.jpg";

    return `
            <div class="bg-white dark:bg-neutral-800 rounded-lg overflow-hidden border border-gray-200 dark:border-neutral-700 hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <div class="relative overflow-hidden">
                    <img src="${posterUrl}" 
                         alt="${movie.title}"
                         class="w-full h-80 object-cover"
                         loading="lazy">
                    <div class="absolute top-2 right-2">
                        <span class="bg-yellow-500 text-black px-2 py-1 rounded-full text-xs font-bold">
                            ⭐ ${rating}
                        </span>
                    </div>
                </div>
                
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white line-clamp-2">
                        ${movie.title}
                    </h3>
                    
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400">${year}</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">${
                          movie.runtime || "N/A"
                        } min</span>
                    </div>
                    
                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-4 line-clamp-3">
                        ${
                          movie.overview
                            ? movie.overview.substring(0, 120) + "..."
                            : "No description available."
                        }
                    </p>
                    
                    <div class="mb-4 text-xs">
                        <p class="text-gray-600 dark:text-gray-400 mb-1">
                            <span class="font-medium">Director:</span> ${
                              movie.director || "N/A"
                            }
                        </p>
                        <p class="text-gray-600 dark:text-gray-400">
                            <span class="font-medium">Cast:</span> ${
                              movie.cast || "N/A"
                            }
                        </p>
                    </div>
                    
                    <div class="flex gap-2">
                        <button class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md transition-colors">
                            View Details
                        </button>
                        <button class="px-3 py-2 border border-gray-300 dark:border-neutral-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
                            ❤️
                        </button>
                    </div>
                </div>
            </div>
        `;
  }

  clearFilters() {
    // Reset genres
    this.selectedGenres = [];
    document.querySelectorAll(".genre-btn").forEach((btn) => {
      btn.classList.remove(
        "bg-blue-100",
        "dark:bg-blue-900",
        "border-blue-500"
      );
      btn.classList.add("bg-white", "dark:bg-neutral-700");
    });

    // Reset sliders
    document.getElementById("minRuntime").value = 90;
    document.getElementById("maxRuntime").value = 150;
    document.getElementById("minRating").value = 6;

    // Reset search
    document.getElementById("movieSearch").value = "";

    // Update displays
    this.updateSelectedCount();
    this.updateSliderValues();

    // Clear results
    document.getElementById("moviesGrid").innerHTML = "";
    document.getElementById("resultsCounter").classList.add("hidden");
    document.getElementById("noResults").classList.add("hidden");
  }

  showLoading(show) {
    const spinner = document.getElementById("loadingSpinner");
    if (spinner) {
      spinner.classList.toggle("hidden", !show);
    }
  }

  showError(message) {
    // You can implement a toast notification here
    alert(message);
  }

  toggleDarkMode() {
    document.documentElement.classList.toggle("dark");
    localStorage.setItem(
      "darkMode",
      document.documentElement.classList.contains("dark")
    );

    const toggle = document.getElementById("darkModeToggle");
    toggle.textContent = document.documentElement.classList.contains("dark")
      ? "☀️"
      : "🌙";
  }

  checkDarkMode() {
    const isDark = localStorage.getItem("darkMode") === "true";
    if (isDark) {
      document.documentElement.classList.add("dark");
      document.getElementById("darkModeToggle").textContent = "☀️";
    }
  }
}

// Initialize app when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  new MovieApp();
});
