<!-- MENU MANAGER -->
<div class="breadcrumbs">
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <span>/</span>
    <a href="index.php?action=admin"><i class="fas fa-cog"></i> Admin</a>
    <span>/</span>
    <span>Menu Items</span>
</div>

<h2 class="page-title"><i class="fas fa-bars"></i> Menu Manager</h2>

<nav class="admin-nav">
    <a href="index.php?action=admin"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="index.php?action=menu_manager" class="active"><i class="fas fa-bars"></i> Menu</a>
    <a href="index.php?action=page_manager"><i class="fas fa-file-alt"></i> Pages</a>
    <a href="index.php?action=site_settings"><i class="fas fa-paint-brush"></i> Settings</a>
    <a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>

<?php if (!empty($successMsg)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMsg); ?></div>
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMsg); ?></div>
<?php endif; ?>

<!-- Add Menu Item -->
<div class="card">
    <h3><i class="fas fa-plus-circle"></i> Add Menu Item</h3>
    <form method="post" action="index.php?action=menu_add">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="label">Label *</label>
                <input type="text" id="label" name="label" required placeholder="e.g. About Us">
            </div>
            <div class="form-group">
                <label for="url">URL *</label>
                <input type="text" id="url" name="url" required placeholder="e.g. index.php?action=page&slug=about">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="icon">Icon Class <span style="color:var(--text-muted);font-weight:400;">(Font Awesome, e.g. fas fa-info-circle)</span></label>
                <input type="text" id="icon" name="icon" placeholder="fas fa-info-circle" value="">
            </div>
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" id="sort_order" name="sort_order" value="0" min="0" placeholder="0">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" checked> Active (visible in menu)</label>
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="open_new_tab"> Open in new tab</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Menu Item</button>
    </form>
</div>

<!-- Existing Menu Items -->
<div class="card">
    <h3><i class="fas fa-list"></i> Current Menu Items (<?php echo count($menuItems); ?>)</h3>
    <?php if (!empty($menuItems)): ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Icon</th>
                    <th>Label</th>
                    <th>URL</th>
                    <th>Status</th>
                    <th>Target</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menuItems as $item): ?>
                <tr>
                    <td><?php echo $item['sort_order']; ?></td>
                    <td><?php if ($item['icon']): ?><i class="<?php echo htmlspecialchars($item['icon']); ?>"></i><?php else: ?><span style="color:var(--text-light);">—</span><?php endif; ?></td>
                    <td><strong><?php echo htmlspecialchars($item['label']); ?></strong></td>
                    <td class="url-cell"><?php echo htmlspecialchars($item['url']); ?></td>
                    <td>
                        <?php if ($item['is_active']): ?>
                            <span class="status-badge status-success"><i class="fas fa-check"></i> Active</span>
                        <?php else: ?>
                            <span class="status-badge status-warning"><i class="fas fa-pause"></i> Hidden</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $item['open_new_tab'] ? 'New tab' : 'Same tab'; ?></td>
                    <td>
                        <div class="actions">
                            <a href="index.php?action=menu_edit&id=<?php echo $item['id']; ?>"><i class="fas fa-edit"></i> Edit</a>
                            <a href="index.php?action=menu_delete&id=<?php echo $item['id']; ?>&csrf_token=<?php echo $csrfToken; ?>" class="delete" onclick="return confirm('Delete this menu item?')"><i class="fas fa-trash-alt"></i> Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p style="color:var(--text-muted);"><i class="fas fa-info-circle"></i> No menu items yet. Add one above.</p>
    <?php endif; ?>
</div>
