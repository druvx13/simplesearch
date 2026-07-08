<!-- ADMIN PANEL -->
<div class="breadcrumbs">
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <span>/</span>
    <span>Admin Panel</span>
</div>

<h2 class="page-title"><i class="fas fa-cog"></i> Admin Panel</h2>

<nav class="admin-nav">
    <a href="index.php?action=admin" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="index.php?action=menu_manager"><i class="fas fa-bars"></i> Menu</a>
    <a href="index.php?action=page_manager"><i class="fas fa-file-alt"></i> Pages</a>
    <a href="index.php?action=site_settings"><i class="fas fa-paint-brush"></i> Settings</a>
    <a href="index.php?action=openindex"><i class="fas fa-list"></i> Index</a>
    <a href="index.php?action=export"><i class="fas fa-file-export"></i> Export</a>
    <a href="index.php?action=import_form"><i class="fas fa-file-import"></i> Import</a>
    <a href="index.php?action=backup"><i class="fas fa-database"></i> Backup</a>
    <a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>

<?php if (!empty($successMsg)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMsg); ?></div>
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMsg); ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="number"><?php echo number_format($totalSites); ?></div>
        <div class="label"><i class="fas fa-globe"></i> Indexed Sites</div>
    </div>
    <div class="stat-card">
        <div class="number"><?php echo number_format($totalQueries); ?></div>
        <div class="label"><i class="fas fa-search"></i> Total Queries</div>
    </div>
    <div class="stat-card">
        <div class="number"><?php echo number_format($totalCrawls); ?></div>
        <div class="label"><i class="fas fa-spider"></i> Crawl Operations</div>
    </div>
    <div class="stat-card">
        <div class="number small-text"><?php echo $lastCrawl ? date('M j, Y', strtotime($lastCrawl)) : 'Never'; ?></div>
        <div class="label"><i class="fas fa-clock"></i> Last Crawl</div>
    </div>
</div>

<!-- Add Site -->
<div class="card">
    <h3><i class="fas fa-plus-circle"></i> Add Site Manually</h3>
    <form method="post" action="index.php?action=add">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <div class="form-group">
            <label for="url">URL *</label>
            <input type="url" id="url" name="url" required placeholder="https://example.com">
        </div>
        <div class="form-group">
            <label for="title">Title *</label>
            <input type="text" id="title" name="title" required placeholder="Page Title">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <input type="text" id="description" name="description" placeholder="Brief description">
        </div>
        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="3" placeholder="Page content or notes..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add / Update Site</button>
    </form>
</div>

<!-- Crawl Forms -->
<div class="card">
    <h3><i class="fas fa-spider"></i> Crawl Operations</h3>

    <div class="card-section">
        <h4>Single URL</h4>
        <form method="post" action="index.php?action=crawl" class="form-inline">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="url" name="url" required placeholder="https://example.com/page">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-spider"></i> Crawl</button>
        </form>
    </div>

    <div class="card-section">
        <h4>Bulk URLs</h4>
        <form method="post" action="index.php?action=bulk_crawl">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <div class="form-group">
                <textarea name="urls" rows="4" required placeholder="https://example.com/page1&#10;https://example.com/page2&#10;https://example.com/page3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-layer-group"></i> Crawl All URLs</button>
        </form>
    </div>

    <div class="card-section">
        <h4>Sitemap.xml</h4>
        <form method="post" action="index.php?action=sitemap_crawl" class="form-inline">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="url" name="sitemap_url" required placeholder="https://example.com/sitemap.xml">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-sitemap"></i> Crawl Sitemap</button>
        </form>
    </div>
</div>

<!-- Indexed Sites -->
<div class="card">
    <h3><i class="fas fa-globe"></i> Indexed Sites (<?php echo number_format($totalSites); ?>)</h3>
    <?php if (!empty($allSites)): ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>URL</th>
                    <th>Title</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allSites as $site): ?>
                <tr>
                    <td><?php echo $site['id']; ?></td>
                    <td class="url-cell">
                        <a href="<?php echo htmlspecialchars($site['url']); ?>" target="_blank" rel="noopener">
                            <?php echo htmlspecialchars($site['url']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($site['title']); ?></td>
                    <td>
                        <div class="actions">
                            <a href="index.php?action=edit&id=<?php echo $site['id']; ?>&csrf_token=<?php echo $csrfToken; ?>"><i class="fas fa-edit"></i> Edit</a>
                            <a href="index.php?action=recrawl&id=<?php echo $site['id']; ?>&csrf_token=<?php echo $csrfToken; ?>" title="Re-crawl"><i class="fas fa-sync-alt"></i> Refresh</a>
                            <a href="index.php?action=delete&id=<?php echo $site['id']; ?>&csrf_token=<?php echo $csrfToken; ?>" class="delete" onclick="return confirm('Delete this site permanently?')"><i class="fas fa-trash-alt"></i> Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalSites > 200): ?>
        <p style="margin-top:12px; color:var(--text-muted); font-size:0.85rem;">Showing last 200 of <?php echo number_format($totalSites); ?> sites.</p>
    <?php endif; ?>
    <?php else: ?>
        <div class="no-results">
            <h3><i class="fas fa-inbox"></i> No sites indexed yet</h3>
            <p>Use the crawl tools above to add your first site.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Crawl Log -->
<div class="card">
    <h3><i class="fas fa-clipboard-list"></i> Crawl Log</h3>
    <?php if (!empty($logRows)): ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>URL</th><th>Status</th><th>Message</th><th>Time</th></tr>
            </thead>
            <tbody>
                <?php foreach ($logRows as $l): ?>
                <tr>
                    <td class="url-cell"><?php echo htmlspecialchars($l['url']); ?></td>
                    <td>
                        <?php
                        $statusClass = match($l['status']) {
                            'success', 'recrawled' => 'status-success',
                            'error', 'failed', 'recrawl failed' => 'status-error',
                            default => 'status-warning'
                        };
                        $statusIcon = match($l['status']) {
                            'success', 'recrawled' => 'fa-check-circle',
                            'error', 'failed', 'recrawl failed' => 'fa-times-circle',
                            default => 'fa-exclamation-triangle'
                        };
                        ?>
                        <span class="status-badge <?php echo $statusClass; ?>"><i class="fas <?php echo $statusIcon; ?>"></i> <?php echo htmlspecialchars($l['status']); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($l['message']); ?></td>
                    <td style="white-space:nowrap;"><?php echo date('M j, H:i', strtotime($l['crawled_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <form method="post" action="index.php?action=clear_log" style="margin-top:12px;">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Clear all crawl log entries?')"><i class="fas fa-trash"></i> Clear Log</button>
    </form>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fas fa-info-circle"></i> No crawl log entries yet.</p>
    <?php endif; ?>
</div>
