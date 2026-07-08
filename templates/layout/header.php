<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="description" content="SimpleSearch - A lightweight search engine">
<meta name="theme-color" content="#3366cc">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="format-detection" content="telephone=no">
<title><?php echo $siteName ?? 'SimpleSearch'; ?><?php if (!empty($query)): ?> — <?php echo htmlspecialchars($query); ?><?php elseif (!empty($pageTitle)): ?> — <?php echo htmlspecialchars($pageTitle); ?><?php endif; ?></title>
<link rel="stylesheet" href="<?php echo $assetBase ?? './assets'; ?>/css/fontawesome.min.css">
<link rel="stylesheet" href="<?php echo $assetBase ?? './assets'; ?>/css/app.css">
<?php if (isset($isAdmin) && $isAdmin): ?>
<link rel="stylesheet" href="<?php echo $assetBase ?? './assets'; ?>/css/admin.css">
<?php endif; ?>
</head>
<body class="page-<?php echo $pageType ?? 'default'; ?>">

<?php if (($pageType ?? '') === 'results'): ?>
<!-- ===== RESULTS MODE: Compact header with inline search bar ===== -->
<header class="results-header">
    <div class="results-header-inner">
        <a href="index.php" class="results-brand">
            <span class="primary">Simple</span><span class="accent">Search</span>
        </a>
        <form method="get" action="index.php" autocomplete="off" class="results-search-form">
            <div class="results-search-bar">
                <input type="text" name="q" value="<?php echo htmlspecialchars($query ?? ''); ?>" placeholder="Search..." id="q">
                <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
            </div>
        </form>
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation menu" aria-expanded="false">
            <span class="hamburger"></span>
        </button>
        <nav class="nav-links" id="navMenu">
            <?php if (!empty($menuItems)): ?>
                <?php foreach ($menuItems as $item): ?>
                    <a href="<?php echo htmlspecialchars($item['url']); ?>"<?php echo $item['open_new_tab'] ? ' target="_blank" rel="noopener"' : ''; ?>><?php if ($item['icon']): ?><i class="<?php echo htmlspecialchars($item['icon']); ?>"></i> <?php endif; ?><?php echo htmlspecialchars($item['label']); ?></a>
                <?php endforeach; ?>
            <?php else: ?>
                <a href="index.php"><i class="fas fa-search"></i> Search</a>
                <a href="index.php?action=openindex"><i class="fas fa-list"></i> Index</a>
            <?php endif; ?>
            <?php if (!empty($isAdmin)): ?>
                <a href="index.php?action=admin"><i class="fas fa-cog"></i> Admin</a>
                <a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="index.php?action=admin_login"><i class="fas fa-lock"></i> Admin</a>
            <?php endif; ?>
        </nav>
    </div>
    <div class="menu-overlay" id="menuOverlay"></div>
</header>

<?php elseif (($pageType ?? '') === 'home'): ?>
<!-- ===== HOME MODE: No header, clean homepage ===== -->
<button class="home-menu-toggle" id="menuToggle" aria-label="Toggle navigation menu" aria-expanded="false">
    <span class="hamburger"></span>
</button>
<nav class="home-nav-links" id="navMenu">
    <?php if (!empty($menuItems)): ?>
        <?php foreach ($menuItems as $item): ?>
            <a href="<?php echo htmlspecialchars($item['url']); ?>"<?php echo $item['open_new_tab'] ? ' target="_blank" rel="noopener"' : ''; ?>><?php if ($item['icon']): ?><i class="<?php echo htmlspecialchars($item['icon']); ?>"></i> <?php endif; ?><?php echo htmlspecialchars($item['label']); ?></a>
        <?php endforeach; ?>
    <?php else: ?>
        <a href="index.php"><i class="fas fa-search"></i> Search</a>
        <a href="index.php?action=openindex"><i class="fas fa-list"></i> Index</a>
    <?php endif; ?>
    <?php if (!empty($isAdmin)): ?>
        <a href="index.php?action=admin"><i class="fas fa-cog"></i> Admin</a>
        <a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    <?php else: ?>
        <a href="index.php?action=admin_login"><i class="fas fa-lock"></i> Admin</a>
    <?php endif; ?>
</nav>
<div class="menu-overlay" id="menuOverlay"></div>

<?php else: ?>
<!-- ===== DEFAULT MODE: Standard header for admin, login, custom pages, etc. ===== -->
<header class="page-header">
    <div class="container">
        <a href="index.php" class="brand">
            <span class="primary">Simple</span><span class="accent">Search</span>
        </a>
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation menu" aria-expanded="false">
            <span class="hamburger"></span>
        </button>
        <nav class="nav-links" id="navMenu">
            <?php if (!empty($menuItems)): ?>
                <?php foreach ($menuItems as $item): ?>
                    <a href="<?php echo htmlspecialchars($item['url']); ?>"<?php echo $item['open_new_tab'] ? ' target="_blank" rel="noopener"' : ''; ?>><?php if ($item['icon']): ?><i class="<?php echo htmlspecialchars($item['icon']); ?>"></i> <?php endif; ?><?php echo htmlspecialchars($item['label']); ?></a>
                <?php endforeach; ?>
            <?php else: ?>
                <a href="index.php"><i class="fas fa-search"></i> Search</a>
                <a href="index.php?action=openindex"><i class="fas fa-list"></i> Index</a>
            <?php endif; ?>
            <?php if (!empty($isAdmin)): ?>
                <a href="index.php?action=admin"><i class="fas fa-cog"></i> Admin</a>
                <a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="index.php?action=admin_login"><i class="fas fa-lock"></i> Admin</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<?php if (!empty($settings['header_text'])): ?>
<div class="header-announcement">
    <div class="container"><?php echo htmlspecialchars($settings['header_text']); ?></div>
</div>
<?php endif; ?>
<div class="menu-overlay" id="menuOverlay"></div>

<?php endif; ?>

<div class="container">
