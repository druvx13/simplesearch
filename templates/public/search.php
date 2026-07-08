<?php if (($pageType ?? '') === 'home'): ?>
<!-- ===== HOME PAGE: Big centered logo + search bar ===== -->
<div class="home-hero">
    <div class="logo">
        <h1><span class="primary">Simple</span><span class="accent">Search</span></h1>
        <p><?php echo htmlspecialchars($settings['home_tagline'] ?? 'Search across your indexed web pages'); ?></p>
    </div>

    <div class="search-section">
        <form method="get" action="index.php" autocomplete="off" id="searchForm">
            <div class="search-form">
                <input type="text" name="q" id="q" value="" placeholder="Search the web..." autofocus>
                <input type="submit" value="Search">
            </div>
            <div class="suggestions" id="suggestions"></div>
        </form>
    </div>
</div>

<?php elseif (($pageType ?? '') === 'results'): ?>
<!-- ===== RESULTS PAGE: Results only (search bar is in the header) ===== -->
<div class="results-body">
    <div class="results">
        <div class="results-header-info">
            <?php if ($totalResults > 0): ?>
                About <strong><?php echo number_format($totalResults); ?></strong> result<?php echo $totalResults !== 1 ? 's' : ''; ?>
                for "<strong><?php echo htmlspecialchars($query); ?></strong>"
                <?php if ($totalPages > 1): ?>
                    <span class="results-page-info">(page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>)</span>
                <?php endif; ?>
            <?php else: ?>
                No results found for "<strong><?php echo htmlspecialchars($query); ?></strong>"
            <?php endif; ?>
        </div>

        <?php if (!empty($didYouMean)): ?>
            <div class="did-you-mean">
                Did you mean: <a href="index.php?q=<?php echo urlencode($didYouMean); ?>"><?php echo htmlspecialchars($didYouMean); ?></a>
            </div>
        <?php endif; ?>

        <?php if (count($results) > 0): ?>
            <?php foreach ($results as $row): ?>
            <div class="result-item">
                <div class="result-title">
                    <a href="<?php echo htmlspecialchars($row['url']); ?>" target="_blank" rel="noopener">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </a>
                </div>
                <div class="result-url"><?php echo htmlspecialchars($row['url']); ?></div>
                <div class="result-snippet">
                    <?php
                    $snippet = $row['description'] ?: $row['content'];
                    echo \SimpleSearch\Helper\TextHelper::snippet($snippet, 250);
                    ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="index.php?q=<?php echo urlencode($query); ?>&page=<?php echo $currentPage - 1; ?>"><i class="fas fa-chevron-left"></i> Prev</a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-chevron-left"></i> Prev</span>
                <?php endif; ?>

                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                if ($startPage > 1) {
                    echo '<a href="index.php?q=' . urlencode($query) . '&page=1">1</a>';
                    if ($startPage > 2) echo '<span>...</span>';
                }
                for ($p = $startPage; $p <= $endPage; $p++) {
                    if ($p == $currentPage) {
                        echo '<span class="current">' . $p . '</span>';
                    } else {
                        echo '<a href="index.php?q=' . urlencode($query) . '&page=' . $p . '">' . $p . '</a>';
                    }
                }
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) echo '<span>...</span>';
                    echo '<a href="index.php?q=' . urlencode($query) . '&page=' . $totalPages . '">' . $totalPages . '</a>';
                }
                ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="index.php?q=<?php echo urlencode($query); ?>&page=<?php echo $currentPage + 1; ?>">Next <i class="fas fa-chevron-right"></i></a>
                <?php else: ?>
                    <span class="disabled">Next <i class="fas fa-chevron-right"></i></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-results">
                <h3><i class="fas fa-search"></i> No results found</h3>
                <p>Try different keywords or check the <a href="index.php?action=openindex">Open Index</a> to see what's available.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<!-- ===== DEFAULT: Fallback layout (should not normally be reached) ===== -->
<div class="logo">
    <h1><span class="primary">Simple</span><span class="accent">Search</span></h1>
    <p>Search across your indexed web pages</p>
</div>

<div class="search-section">
    <form method="get" action="index.php" autocomplete="off" id="searchForm">
        <div class="search-form">
            <input type="text" name="q" id="q" value="<?php echo htmlspecialchars($query ?? ''); ?>" placeholder="Search the web..." autofocus>
            <input type="submit" value="Search">
        </div>
        <div class="suggestions" id="suggestions"></div>
    </form>
</div>
<?php endif; ?>
