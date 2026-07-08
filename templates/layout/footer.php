</div>

<!-- Page Footer -->
<footer class="page-footer">
    <div class="container">
        <p>
            <?php
            $footerText = $settings['footer_text'] ?? '';
            if (!empty($footerText)) {
                echo '<i class="fas fa-search"></i> ' . htmlspecialchars($footerText);
            } else {
                echo '<i class="fas fa-search"></i> ' . ($siteName ?? 'SimpleSearch') . ' Engine &mdash; Lightweight Search Solution';
            }
            ?>
        </p>
        <p>
            <?php if (!empty($isAdmin)): ?>
                <i class="fas fa-user-shield"></i> Logged in as Admin | <a href="index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="index.php?action=admin_login"><i class="fas fa-lock"></i> Admin Login</a>
            <?php endif; ?>
        </p>
    </div>
</footer>

<?php if (!empty($isAdmin)): ?>
<script src="<?php echo $assetBase ?? './assets'; ?>/js/search.js"></script>
<script src="<?php echo $assetBase ?? './assets'; ?>/js/admin.js"></script>
<?php else: ?>
<script src="<?php echo $assetBase ?? './assets'; ?>/js/search.js"></script>
<?php endif; ?>

<!-- Mobile Navigation Toggle -->
<script>
(function() {
    var toggle = document.getElementById('menuToggle');
    var nav = document.getElementById('navMenu');
    var overlay = document.getElementById('menuOverlay');
    if (!toggle || !nav) return;

    function openMenu() {
        toggle.classList.add('active');
        toggle.setAttribute('aria-expanded', 'true');
        nav.classList.add('active');
        if (overlay) overlay.classList.add('active');
        document.body.classList.add('menu-open');
    }

    function closeMenu() {
        toggle.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
        nav.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
        document.body.classList.remove('menu-open');
    }

    toggle.addEventListener('click', function() {
        if (nav.classList.contains('active')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    if (overlay) {
        overlay.addEventListener('click', closeMenu);
    }

    var links = nav.querySelectorAll('a');
    for (var i = 0; i < links.length; i++) {
        links[i].addEventListener('click', closeMenu);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && nav.classList.contains('active')) {
            closeMenu();
            toggle.focus();
        }
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && nav.classList.contains('active')) {
            closeMenu();
        }
    });
})();
</script>

</body>
</html>
