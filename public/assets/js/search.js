/**
 * SimpleSearch - Autocomplete & Keyboard Navigation
 * Mobile-friendly with touch support
 * Works on both home page and results page
 */

// Debounce timer for fetch requests
var fetchTimer = null;

function fetchSuggestions(str) {
    var box = document.getElementById('suggestions');
    if (!box) return;  // No suggestions container (e.g., results page)

    if (str.length < 2) {
        box.style.display = 'none';
        return;
    }

    // Debounce: wait 200ms before fetching
    clearTimeout(fetchTimer);
    fetchTimer = setTimeout(function() {
        fetch('index.php?action=suggest&q=' + encodeURIComponent(str))
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (!box) return;
                box.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(function(item) {
                        var div = document.createElement('div');
                        div.setAttribute('role', 'option');
                        div.setAttribute('tabindex', '-1');
                        // Highlight matching prefix
                        var regex = new RegExp('(' + str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                        div.innerHTML = item.replace(regex, '<strong>$1</strong>');
                        div.onclick = function() {
                            var qInput = document.getElementById('q');
                            if (qInput) qInput.value = item;
                            box.style.display = 'none';
                            var form = document.getElementById('searchForm');
                            if (form) {
                                form.submit();
                            } else {
                                // Results page: submit the header search form
                                var resultsForm = document.querySelector('.results-search-form');
                                if (resultsForm) resultsForm.submit();
                            }
                        };
                        box.appendChild(div);
                    });
                    box.style.display = 'block';
                } else {
                    box.style.display = 'none';
                }
            })
            .catch(function() {
                if (box) box.style.display = 'none';
            });
    }, 200);
}

// Bind oninput to the #q search input (exists on both home & results pages)
var qInput = document.getElementById('q');
if (qInput) {
    qInput.addEventListener('input', function() {
        fetchSuggestions(this.value);
    });
}

// Close suggestions when clicking/tapping outside
document.addEventListener('click', function(e) {
    var searchBox = document.querySelector('.search-section');
    if (searchBox && !searchBox.contains(e.target)) {
        var box = document.getElementById('suggestions');
        if (box) box.style.display = 'none';
    }
});

// Close suggestions on touch outside (for mobile)
document.addEventListener('touchstart', function(e) {
    var searchBox = document.querySelector('.search-section');
    if (searchBox && !searchBox.contains(e.target)) {
        var box = document.getElementById('suggestions');
        if (box) box.style.display = 'none';
    }
}, { passive: true });

// Keyboard navigation for suggestions (home page only)
if (qInput) {
    qInput.addEventListener('keydown', function(e) {
        var box = document.getElementById('suggestions');
        if (!box || box.style.display === 'none') return;

        var items = box.querySelectorAll('div');
        if (items.length === 0) return;
        var active = box.querySelector('.active');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!active) {
                items[0].classList.add('active');
            } else {
                active.classList.remove('active');
                var next = active.nextElementSibling;
                if (next) next.classList.add('active');
                else items[0].classList.add('active');
            }
            // Scroll active item into view
            var newActive = box.querySelector('.active');
            if (newActive) newActive.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (!active) {
                items[items.length - 1].classList.add('active');
            } else {
                active.classList.remove('active');
                var prev = active.previousElementSibling;
                if (prev) prev.classList.add('active');
                else items[items.length - 1].classList.add('active');
            }
            var newActive = box.querySelector('.active');
            if (newActive) newActive.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter' && active) {
            e.preventDefault();
            active.click();
        } else if (e.key === 'Escape') {
            box.style.display = 'none';
        }
    });
}
