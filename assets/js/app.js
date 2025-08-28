class MovieApp {
  constructor() {
    this.selectedGenres = [];
    this.currentMovies = [];
    this.maxGenres = 3;
    this.allGenres = [
      { id: "28", name: "Action" },
      { id: "12", name: "Adventure" },
      { id: "16", name: "Animation" },
      { id: "35", name: "Comedy" },
      { id: "80", name: "Crime" },
      { id: "99", name: "Documentary" },
      { id: "18", name: "Drama" },
      { id: "10751", name: "Family" },
      { id: "14", name: "Fantasy" },
      { id: "36", name: "History" },
      { id: "27", name: "Horror" },
      { id: "10402", name: "Music" },
      { id: "9648", name: "Mystery" },
      { id: "10749", name: "Romance" },
      { id: "878", name: "Science Fiction" },
      { id: "10770", name: "TV Movie" },
    ];

    this.init();
  }

  init() {
    this.renderGenres();
    this.setupEventListeners();
    this.updateSliderValues();
  }

  // Dynamically render genres with top 8 visible, rest hidden initially
  renderGenres() {
    const container = document.getElementById("genreContainer");
    container.innerHTML = "";

    // Show top 8 genres visible
    this.allGenres.forEach((genre, index) => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className =
        "genre-btn px-3 py-2 mb-2 text-sm border rounded-md transition-colors " +
        "bg-white dark:bg-neutral-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-neutral-600 border-gray-300 dark:border-neutral-600";
      btn.dataset.genreId = genre.id;
      btn.dataset.genreName = genre.name;
      btn.textContent = genre.name;

      // Hide genres beyond top 8 initially
      if (index >= 8) {
        btn.style.display = "none";
      }
      container.appendChild(btn);
    });

    // Add "Show More" / "Show Less" Button
    const showMoreBtn = document.createElement("button");
    showMoreBtn.id = "showMoreGenres";
    showMoreBtn.textContent = "Show More";
    showMoreBtn.className =
      "px-3 py-2 mt-2 text-sm rounded-md font-semibold bg-gray-200 dark:bg-neutral-600 text-gray-800 dark:text-gray-200";

    container.appendChild(showMoreBtn);

    // Add 18+ Button separately
    const adultBtn = document.createElement("button");
    adultBtn.id = "adultConfirmBtn";
    adultBtn.textContent = "18+";
    adultBtn.className =
      "px-3 py-2 mt-2 ml-4 text-sm rounded-md font-bold bg-red-600 text-white hover:bg-red-700";
    container.appendChild(adultBtn);

    // Setup Show More toggle listener
    showMoreBtn.addEventListener("click", () => {
      const hiddenGenres = Array.from(
        container.querySelectorAll("button.genre-btn")
      ).filter((b, i) => i >= 8);

      const isHidden = hiddenGenres.some((b) => b.style.display === "none");

      hiddenGenres.forEach((btn) => {
        btn.style.display = isHidden ? "inline-block" : "none";
      });

      showMoreBtn.textContent = isHidden ? "Show Less" : "Show More";
    });

    // Setup 18+ adult confirmation modal listener
    adultBtn.addEventListener("click", () => {
      this.showAdultModal();
    });
  }

  setupEventListeners() {
    // Attach genre button listeners (including dynamically shown genres)
    document.querySelectorAll(".genre-btn").forEach((btn) => {
      btn.addEventListener("click", (e) => this.toggleGenre(e));
    });

    // Sliders and other buttons as before...
    const sliders = ["minRuntime", "maxRuntime", "minRating"];
    sliders.forEach((id) => {
      const slider = document.getElementById(id);
      if (slider) {
        slider.addEventListener("input", () => this.updateSliderValues());
      }
    });

    document
      .getElementById("findMovies")
      ?.addEventListener("click", () => this.findMovies());
    document
      .getElementById("randomMovie")
      ?.addEventListener("click", () => this.getRandomMovie());
    document
      .getElementById("clearFilters")
      ?.addEventListener("click", () => this.clearFilters());
    document
      .getElementById("searchBtn")
      ?.addEventListener("click", () => this.searchMovies());

    const searchInput = document.getElementById("movieSearch");
    if (searchInput) {
      searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") this.searchMovies();
      });
    }
  }

  // Adult modal with confirm or cancel, then redirects on confirm
  showAdultModal() {
    // Create modal elements
    const modal = document.createElement("div");
    modal.id = "adultModal";
    modal.style.position = "fixed";
    modal.style.top = 0;
    modal.style.left = 0;
    modal.style.width = "100vw";
    modal.style.height = "100vh";
    modal.style.backgroundColor = "rgba(0,0,0,0.5)";
    modal.style.display = "flex";
    modal.style.justifyContent = "center";
    modal.style.alignItems = "center";
    modal.style.zIndex = 9999;

    const modalContent = document.createElement("div");
    modalContent.style.backgroundColor = "white";
    modalContent.style.padding = "2rem";
    modalContent.style.borderRadius = "8px";
    modalContent.style.textAlign = "center";
    modalContent.style.maxWidth = "300px";

    modalContent.innerHTML = `
      <h2 style="font-weight:bold; margin-bottom:1rem;">Age Confirmation</h2>
      <p>Hmmm saale, naughty horha.</p>
      <div style="margin-top:1.5rem;">
        <button id="cancelAdult" style="margin-right:1rem; padding: 0.5rem 1rem;">Cancel</button>
        <button id="confirmAdult" style="background-color:#e11d48; color:white; padding: 0.5rem 1rem;">😈</button>
      </div>
    `;
    modal.appendChild(modalContent);
    document.body.appendChild(modal);

    document.getElementById("cancelAdult").addEventListener("click", () => {
      document.body.removeChild(modal);
    });

    document.getElementById("confirmAdult").addEventListener("click", () => {
      // Redirect to porn website as a fun easter egg
      window.location.href = "https://www.google.com/search?q=pornhub.com";
    });
  }

  toggleGenre(e) {
    const btn = e.target;
    const genreId = btn.dataset.genreId;

    if (this.selectedGenres.includes(genreId)) {
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
    if (counter) counter.textContent = this.selectedGenres.length;
  }

  updateSliderValues() {
    const minRuntime = document.getElementById("minRuntime");
    const maxRuntime = document.getElementById("maxRuntime");
    const minRating = document.getElementById("minRating");

    if (minRuntime)
      document.getElementById("minRuntimeValue").textContent =
        minRuntime.value + " min";
    if (maxRuntime)
      document.getElementById("maxRuntimeValue").textContent =
        maxRuntime.value + " min";
    if (minRating)
      document.getElementById("minRatingValue").textContent =
        minRating.value + " ⭐";
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

    if (movieCount) movieCount.textContent = movies.length;
    counter.classList.remove("hidden");
    noResults.classList.add("hidden");

    grid.innerHTML = movies
      .map((movie) => this.createMovieCard(movie))
      .join("");
  }

  createMovieCard(movie) {
    const year = movie.release_date
      ? new Date(movie.release_date).getFullYear()
      : "N/A";
    const rating = movie.vote_average ? movie.vote_average : "N/A";
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

  showLoading(show) {
    const spinner = document.getElementById("loadingSpinner");
    if (spinner) spinner.classList.toggle("hidden", !show);
  }

  showError(message) {
    alert(message);
  }
}

// Initialize app when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  new MovieApp();
});
