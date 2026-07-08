<!-- IMPORT CSV -->
<div class="breadcrumbs">
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <span>/</span>
    <a href="index.php?action=admin"><i class="fas fa-cog"></i> Admin</a>
    <span>/</span>
    <span>Import CSV</span>
</div>

<div class="card card-centered">
    <h3><i class="fas fa-file-import"></i> Import CSV</h3>
    <form method="post" enctype="multipart/form-data" action="index.php?action=import">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <div class="form-group">
            <label for="csvfile">CSV File</label>
            <input type="file" id="csvfile" name="csvfile" accept=".csv,text/csv" required>
        </div>
        <div class="alert alert-info" style="font-size:0.85rem;">
            <i class="fas fa-info-circle"></i> <strong>Expected columns:</strong> id, url, title, description, content, crawled_at (optional)<br>
            First row is treated as header and skipped.
        </div>
        <div class="btn-group">
            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload &amp; Import</button>
            <a href="index.php?action=admin" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
        </div>
    </form>
</div>
