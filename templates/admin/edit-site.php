<!-- EDIT SITE -->
<div class="breadcrumbs">
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <span>/</span>
    <a href="index.php?action=admin"><i class="fas fa-cog"></i> Admin</a>
    <span>/</span>
    <span>Edit Site #<?php echo $site['id']; ?></span>
</div>

<div class="card card-centered">
    <h3><i class="fas fa-edit"></i> Edit Site #<?php echo $site['id']; ?></h3>
    <form method="post" action="index.php?action=update">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <input type="hidden" name="id" value="<?php echo $site['id']; ?>">
        <div class="form-group">
            <label for="url">URL *</label>
            <input type="url" id="url" name="url" value="<?php echo htmlspecialchars($site['url']); ?>" required>
        </div>
        <div class="form-group">
            <label for="title">Title *</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($site['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($site['description']); ?>">
        </div>
        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="6"><?php echo htmlspecialchars($site['content']); ?></textarea>
        </div>
        <div class="btn-group">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Site</button>
            <a href="index.php?action=admin" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
        </div>
    </form>
</div>
