<!-- OPEN INDEX -->
<div class="breadcrumbs">
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <span>/</span>
    <span>Open Index</span>
</div>

<h2 class="page-title"><i class="fas fa-list"></i> Open Index &ndash; All Crawled URLs</h2>

<div class="card">
    <?php if (!empty($allSites)): ?>
    <p style="margin-bottom:16px; color:var(--text-muted);">Showing <?php echo number_format($totalIndexed); ?> indexed sites.</p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>Title</th><th>URL</th><th>Crawled</th></tr>
            </thead>
            <tbody>
                <?php foreach ($allSites as $i => $site): ?>
                <tr>
                    <td style="width:50px; text-align:center;"><?php echo $i + 1; ?></td>
                    <td><?php echo htmlspecialchars($site['title']); ?></td>
                    <td class="url-cell">
                        <a href="<?php echo htmlspecialchars($site['url']); ?>" target="_blank" rel="noopener">
                            <?php echo htmlspecialchars($site['url']); ?>
                        </a>
                    </td>
                    <td style="white-space:nowrap;"><?php echo date('M j, Y H:i', strtotime($site['crawled_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="no-results">
            <h3><i class="fas fa-inbox"></i> No sites indexed yet</h3>
            <p>The index is empty. Use the admin panel to crawl or add sites.</p>
        </div>
    <?php endif; ?>
</div>

<div class="btn-group mt-4">
    <a href="index.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Search</a>
    <?php if (!empty($isAdmin)): ?>
        <a href="index.php?action=admin" class="btn btn-secondary btn-sm"><i class="fas fa-cog"></i> Admin Panel</a>
    <?php endif; ?>
</div>
