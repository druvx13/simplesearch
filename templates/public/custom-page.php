<!-- CUSTOM PAGE (public) -->
<?php if (!empty($customPage)): ?>

<div class="breadcrumbs">
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <span>/</span>
    <span><?php echo htmlspecialchars($customPage['title']); ?></span>
</div>

<div class="custom-page">
    <h1 class="page-title"><?php echo htmlspecialchars($customPage['title']); ?></h1>
    <div class="custom-page-content">
        <?php
        // Allow safe HTML tags in custom page content
        $allowedTags = '<h1><h2><h3><h4><h5><h6><p><br><ul><ol><li><strong><em><a><blockquote><hr><table><th><td><tr><thead><tbody><pre><code><dl><dt><dd>';
        echo strip_tags($customPage['content'], $allowedTags);
        ?>
    </div>
</div>

<?php else: ?>
<!-- 404 PAGE -->

<div class="custom-page">
    <h1 class="page-title"><i class="fas fa-exclamation-triangle"></i> Page Not Found</h1>
    <div class="custom-page-content">
        <p><?php echo htmlspecialchars($errorMessage ?? 'The page you are looking for does not exist or has been removed.'); ?></p>
        <p><a href="index.php" class="btn btn-primary"><i class="fas fa-home"></i> Back to Home</a></p>
    </div>
</div>

<?php endif; ?>
