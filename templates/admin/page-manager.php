<!-- CUSTOM PAGE MANAGER -->
<div class="breadcrumbs">
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <span>/</span>
    <a href="index.php?action=admin"><i class="fas fa-cog"></i> Admin</a>
    <span>/</span>
    <span>Custom Pages</span>
</div>

<h2 class="page-title"><i class="fas fa-file-alt"></i> Custom Pages</h2>

<nav class="admin-nav">
    <a href="index.php?action=admin"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="index.php?action=menu_manager"><i class="fas fa-bars"></i> Menu</a>
    <a href="index.php?action=page_manager" class="active"><i class="fas fa-file-alt"></i> Pages</a>
    <a href="index.php?action=site_settings"><i class="fas fa-paint-brush"></i> Settings</a>
    <a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>

<?php if (!empty($successMsg)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMsg); ?></div>
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMsg); ?></div>
<?php endif; ?>

<div style="margin-bottom:20px;">
    <a href="index.php?action=page_add" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Page</a>
</div>

<!-- Existing Pages -->
<div class="card">
    <h3><i class="fas fa-list"></i> All Pages (<?php echo count($customPages); ?>)</h3>
    <?php if (!empty($customPages)): ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customPages as $pg): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($pg['title']); ?></strong></td>
                    <td><code><?php echo htmlspecialchars($pg['slug']); ?></code></td>
                    <td>
                        <?php if ($pg['is_published']): ?>
                            <span class="status-badge status-success"><i class="fas fa-check"></i> Published</span>
                        <?php else: ?>
                            <span class="status-badge status-warning"><i class="fas fa-pause"></i> Draft</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;"><?php echo date('M j, Y', strtotime($pg['updated_at'])); ?></td>
                    <td>
                        <div class="actions">
                            <a href="index.php?action=page_edit&id=<?php echo $pg['id']; ?>"><i class="fas fa-edit"></i> Edit</a>
                            <a href="index.php?action=page&slug=<?php echo urlencode($pg['slug']); ?>" target="_blank"><i class="fas fa-eye"></i> View</a>
                            <a href="index.php?action=page_delete&id=<?php echo $pg['id']; ?>&csrf_token=<?php echo $csrfToken; ?>" class="delete" onclick="return confirm('Delete this page permanently?')"><i class="fas fa-trash-alt"></i> Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fas fa-info-circle"></i> No custom pages yet. Create one to add Terms, Privacy Policy, About, etc.</p>
    <?php endif; ?>
</div>
