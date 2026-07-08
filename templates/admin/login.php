<!-- ADMIN LOGIN -->
<div class="card card-centered">
    <h3 class="text-center"><i class="fas fa-lock"></i> Admin Login</h3>
    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $errorMsg; ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autofocus>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-sign-in-alt"></i> Login</button>
        </div>
    </form>
    <p class="text-center mt-4" style="font-size:0.9rem;">
        <a href="index.php" style="display:inline-block; padding:8px 12px; min-height:44px; line-height:1.4;"><i class="fas fa-arrow-left"></i> Back to search</a>
    </p>
</div>
