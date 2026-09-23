/**
 * iOS-Style Minimalist Search Controller (media_1790185361820.png)
 * Handles Recent Searches, Chip Presets, Live Dropdown Suggestions, and Clear Actions
 */

$(document).ready(function() {
  const modal = $('#searchModal');
  const input = $('#globalSearchInput');
  const clearBtn = $('#clearSearchInputBtn');
  const recentPanel = $('#searchRecentPanel');
  const recentChipsContainer = $('#recentChipsContainer');
  const clearAllRecentBtn = $('#clearAllRecentBtn');
  const dropdownCard = $('#searchDropdownCard');
  const resultsContainer = $('#searchResultsContainer');

  const STORAGE_KEY = 'softpay_recent_searches';
  const DEFAULT_RECENT = ['Trading', 'Deposit UPI', 'Daily Interest', 'Withdrawal', 'VIP 1.0%', 'USDT Desk'];

  function getRecentSearches() {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (saved) {
        const parsed = JSON.parse(saved);
        if (Array.isArray(parsed) && parsed.length > 0) return parsed;
      }
    } catch (e) {}
    return DEFAULT_RECENT;
  }

  function saveRecentSearch(term) {
    if (!term || typeof term !== 'string') return;
    const clean = term.trim();
    if (clean.length < 2) return;

    try {
      let recents = getRecentSearches();
      // Remove if already exists, then unshift
      recents = recents.filter(item => item.toLowerCase() !== clean.toLowerCase());
      recents.unshift(clean);
      if (recents.length > 8) recents = recents.slice(0, 8);
      localStorage.setItem(STORAGE_KEY, JSON.stringify(recents));
      renderRecentChips();
    } catch (e) {}
  }

  function renderRecentChips() {
    const list = getRecentSearches();
    recentChipsContainer.empty();

    if (!list || list.length === 0) {
      recentPanel.hide();
      return;
    }

    recentPanel.show();
    list.forEach(item => {
      const chip = $('<button type="button" class="ios-recent-chip"></button>').text(item);
      chip.on('click', function(e) {
        e.preventDefault();
        input.val(item);
        triggerSearch(item);
        input.focus();
      });
      recentChipsContainer.append(chip);
    });
  }

  clearAllRecentBtn.on('click', function(e) {
    e.preventDefault();
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify([]));
      renderRecentChips();
    } catch (err) {}
  });

  function openSearch() {
    modal.addClass('active');
    $('body').addClass('search-open');
    renderRecentChips();

    setTimeout(() => {
      input.val('').focus();
      clearBtn.hide();
      dropdownCard.hide();
      recentPanel.show();
    }, 60);

    if (navigator.vibrate) {
      navigator.vibrate(15);
    }
  }

  function closeSearch() {
    modal.removeClass('active');
    $('body').removeClass('search-open');
  }

  $('#openSearchBtn').on('click', openSearch);
  $('#closeSearchModalBtn').on('click', closeSearch);

  modal.on('click', function(e) {
    if ($(e.target).is('#searchModal')) {
      closeSearch();
    }
  });

  // Global Keyboard Shortcut: Ctrl + K / Cmd + K / ESC
  $(document).on('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      if (modal.hasClass('active')) {
        closeSearch();
      } else {
        openSearch();
      }
    }
    if (e.key === 'Escape' && modal.hasClass('active')) {
      closeSearch();
    }
  });

  // Clear Input Button (x)
  clearBtn.on('click', function(e) {
    e.preventDefault();
    input.val('').focus();
    clearBtn.hide();
    dropdownCard.hide();
    recentPanel.show();
  });

  let searchTimeout = null;

  input.on('input', function() {
    const q = $(this).val();
    if (q.length > 0) {
      clearBtn.show();
    } else {
      clearBtn.hide();
    }
    triggerSearch(q);
  });

  function triggerSearch(q) {
    const term = q.trim();
    clearTimeout(searchTimeout);

    if (term.length === 0) {
      dropdownCard.hide();
      recentPanel.show();
      return;
    }

    recentPanel.hide();
    dropdownCard.show();

    searchTimeout = setTimeout(function() {
      $.ajax({
        url: 'index.php?api=search',
        type: 'GET',
        data: { q: term },
        dataType: 'json',
        success: function(res) {
          if (res.success && res.results) {
            renderSuggestions(res.results, term);
          }
        },
        error: function() {
          resultsContainer.html(`
            <div style="padding: 18px 16px; color: var(--text-muted); font-size: 0.9rem; text-align: center;">
              Unable to reach search service
            </div>
          `);
        }
      });
    }, 120);
  }

  function renderSuggestions(items, query) {
    resultsContainer.empty();

    if (items.length === 0) {
      resultsContainer.html(`
        <div style="padding: 22px 18px; text-align: center; color: var(--text-muted); font-size: 0.9rem;">
          No matching products or services found for "<strong>${$('<div>').text(query).html()}</strong>"
        </div>
      `);
      return;
    }

    items.forEach(item => {
      // Highlight matching query in title
      const title = item.title;
      let displayTitle = $('<div>').text(title).html();
      const qSafe = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const regex = new RegExp(`(${qSafe})`, 'gi');
      displayTitle = displayTitle.replace(regex, '<strong>$1</strong>');

      const row = $(`
        <a href="${item.url}" class="ios-suggestion-row">
          <div class="ios-suggestion-title">
            ${displayTitle}
          </div>
          <span class="ios-suggestion-arrow">›</span>
        </a>
      `);

      row.on('click', function() {
        saveRecentSearch(item.title);
      });

      resultsContainer.append(row);
    });
  }

  // Initial render of recent searches
  renderRecentChips();
});
