<!-- EDIT MENU ITEM -->
<div class="breadcrumbs">
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <span>/</span>
    <a href="index.php?action=admin"><i class="fas fa-cog"></i> Admin</a>
    <span>/</span>
    <a href="index.php?action=menu_manager"><i class="fas fa-bars"></i> Menu</a>
    <span>/</span>
    <span>Edit: <?php echo htmlspecialchars($menuItem['label']); ?></span>
</div>

<h2 class="page-title"><i class="fas fa-edit"></i> Edit Menu Item</h2>

<nav class="admin-nav">
    <a href="index.php?action=admin"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="index.php?action=menu_manager" class="active"><i class="fas fa-bars"></i> Menu</a>
    <a href="index.php?action=page_manager"><i class="fas fa-file-alt"></i> Pages</a>
    <a href="index.php?action=site_settings"><i class="fas fa-paint-brush"></i> Settings</a>
    <a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>

<div class="card">
    <form method="post" action="index.php?action=menu_update">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <input type="hidden" name="id" value="<?php echo $menuItem['id']; ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="label">Label *</label>
                <input type="text" id="label" name="label" required value="<?php echo htmlspecialchars($menuItem['label']); ?>">
            </div>
            <div class="form-group">
                <label for="url">URL *</label>
                <input type="text" id="url" name="url" required value="<?php echo htmlspecialchars($menuItem['url']); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="icon">Icon Class <span style="color:var(--text-muted);font-weight:400;">(Font Awesome)</span></label>
                <input type="text" id="icon" name="icon" value="<?php echo htmlspecialchars($menuItem['icon']); ?>" placeholder="fas fa-info-circle">
            </div>
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" id="sort_order" name="sort_order" value="<?php echo $menuItem['sort_order']; ?>" min="0">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" <?php echo $menuItem['is_active'] ? 'checked' : ''; ?>> Active (visible in menu)</label>
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="open_new_tab" <?php echo $menuItem['open_new_tab'] ? 'checked' : ''; ?>> Open in new tab</label>
            </div>
        </div>
        <div class="btn-group">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            <a href="index.php?action=menu_manager" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
        </div>
    </form>
</div>
