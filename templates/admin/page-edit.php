<!-- EDIT / CREATE CUSTOM PAGE -->
<div class="breadcrumbs">
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <span>/</span>
    <a href="index.php?action=admin"><i class="fas fa-cog"></i> Admin</a>
    <span>/</span>
    <a href="index.php?action=page_manager"><i class="fas fa-file-alt"></i> Pages</a>
    <span>/</span>
    <span><?php echo !empty($page) ? 'Edit: ' . htmlspecialchars($page['title']) : 'Create Page'; ?></span>
</div>

<h2 class="page-title"><i class="fas fa-<?php echo !empty($page) ? 'edit' : 'plus-circle'; ?>"></i> <?php echo !empty($page) ? 'Edit Page' : 'Create New Page'; ?></h2>

<nav class="admin-nav">
    <a href="index.php?action=admin"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="index.php?action=menu_manager"><i class="fas fa-bars"></i> Menu</a>
    <a href="index.php?action=page_manager" class="active"><i class="fas fa-file-alt"></i> Pages</a>
    <a href="index.php?action=site_settings"><i class="fas fa-paint-brush"></i> Settings</a>
    <a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>

<div class="card">
    <form method="post" action="index.php?action=page_save">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <?php if (!empty($page)): ?>
            <input type="hidden" name="id" value="<?php echo $page['id']; ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label for="title">Page Title *</label>
                <input type="text" id="title" name="title" required value="<?php echo !empty($page) ? htmlspecialchars($page['title']) : ''; ?>" placeholder="e.g. Terms of Service">
            </div>
            <div class="form-group">
                <label for="slug">URL Slug <span style="color:var(--text-muted);font-weight:400;">(auto-generated from title if left empty)</span></label>
                <input type="text" id="slug" name="slug" value="<?php echo !empty($page) ? htmlspecialchars($page['slug']) : ''; ?>" placeholder="e.g. terms-of-service">
            </div>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_published" <?php echo (empty($page) || $page['is_published']) ? 'checked' : ''; ?>>
                Published (visible to visitors)
            </label>
        </div>

        <div class="form-group">
            <label for="content">Page Content</label>
            <textarea id="content" name="content" rows="16" placeholder="Write your page content here... You can use basic HTML tags like &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;a href='...'&gt;"><?php echo !empty($page) ? htmlspecialchars($page['content']) : ''; ?></textarea>
            <p style="color:var(--text-muted); font-size:0.8rem; margin-top:4px;"><i class="fas fa-info-circle"></i> Basic HTML tags are allowed: h1-h6, p, br, ul, ol, li, strong, em, a, blockquote, hr, table, th, td, tr</p>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?php echo !empty($page) ? 'Save Changes' : 'Create Page'; ?></button>
            <a href="index.php?action=page_manager" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
        </div>
    </form>
</div>
