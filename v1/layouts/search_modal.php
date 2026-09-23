<div class="search-modal-container" id="searchModal">
  <div class="search-modal-card ios-search-card">
    
    <!-- Top Header: Title & Close -->
    <div class="ios-search-header">
      <h2 class="ios-search-title">Search Products</h2>
      <button type="button" class="ios-search-close-btn" id="closeSearchModalBtn" aria-label="Close search">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>

    <!-- Rounded Search Pill Input -->
    <div class="ios-search-input-box">
      <div class="ios-search-icon-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#98a2b3" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </div>
      <input type="text" id="globalSearchInput" class="ios-search-input" placeholder="Search for products, services, trades..." autocomplete="off">
      <button type="button" class="ios-search-clear-btn" id="clearSearchInputBtn" title="Clear input" style="display: none;">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>

    <!-- State 1: Recent Searches Panel (Shown when input is empty) -->
    <div class="ios-search-recent-panel" id="searchRecentPanel">
      <div class="ios-recent-header">
        <span class="ios-recent-title">Recent searches</span>
        <button type="button" class="ios-clear-all-btn" id="clearAllRecentBtn">Clear all</button>
      </div>
      <div class="ios-recent-chips-wrap" id="recentChipsContainer">
        <!-- Rendered dynamically from localStorage or defaults via search.js -->
      </div>
    </div>

    <!-- State 2: Floating Suggestions Dropdown (Shown when typing) -->
    <div class="ios-search-dropdown-card" id="searchDropdownCard" style="display: none;">
      <div class="ios-suggestions-list" id="searchResultsContainer">
        <!-- Live suggestion items -->
      </div>
    </div>

  </div>
</div>
