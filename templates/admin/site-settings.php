<!-- SITE SETTINGS -->
<div class="breadcrumbs">
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <span>/</span>
    <a href="index.php?action=admin"><i class="fas fa-cog"></i> Admin</a>
    <span>/</span>
    <span>Site Settings</span>
</div>

<h2 class="page-title"><i class="fas fa-paint-brush"></i> Site Settings</h2>

<nav class="admin-nav">
    <a href="index.php?action=admin"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="index.php?action=menu_manager"><i class="fas fa-bars"></i> Menu</a>
    <a href="index.php?action=page_manager"><i class="fas fa-file-alt"></i> Pages</a>
    <a href="index.php?action=site_settings" class="active"><i class="fas fa-paint-brush"></i> Settings</a>
    <a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>

<?php if (!empty($successMsg)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMsg); ?></div>
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMsg); ?></div>
<?php endif; ?>

<form method="post" action="index.php?action=site_settings_save">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

    <!-- Header Settings -->
    <div class="card">
        <h3><i class="fas fa-heading"></i> Header</h3>
        <div class="form-group">
            <label for="header_text">Header Announcement Text <span style="color:var(--text-muted);font-weight:400;">(shown below the navigation bar, leave empty to hide)</span></label>
            <input type="text" id="header_text" name="header_text" value="<?php echo htmlspecialchars($settings['header_text'] ?? ''); ?>" placeholder="e.g. Welcome to our search engine!">
        </div>
    </div>

    <!-- Footer Settings -->
    <div class="card">
        <h3><i class="fas fa-shoe-prints"></i> Footer</h3>
        <div class="form-group">
            <label for="footer_text">Footer Text <span style="color:var(--text-muted);font-weight:400;">(replaces default footer text)</span></label>
            <input type="text" id="footer_text" name="footer_text" value="<?php echo htmlspecialchars($settings['footer_text'] ?? ''); ?>" placeholder="e.g. MySearch Engine — Powered by SimpleSearch">
        </div>
    </div>

    <!-- Homepage Settings -->
    <div class="card">
        <h3><i class="fas fa-home"></i> Homepage</h3>
        <div class="form-group">
            <label for="home_tagline">Homepage Tagline <span style="color:var(--text-muted);font-weight:400;">(shown below the logo on the homepage)</span></label>
            <input type="text" id="home_tagline" name="home_tagline" value="<?php echo htmlspecialchars($settings['home_tagline'] ?? ''); ?>" placeholder="e.g. Search across your indexed web pages">
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save All Settings</button>
</form>
