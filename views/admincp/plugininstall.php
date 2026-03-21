<?php
/**
 * AdminCP plugin install view.
 *
 * Variables:
 * - bool $systemEnabled
 */
?>
<h1 class="page-header"><i class="bi bi-download me-2"></i>Import Plugin</h1>

<?php if (!$systemEnabled): ?>
    <?php inline_message('warning', 'The plugin system is not enabled. Enable it in Website Settings.'); ?>
<?php endif; ?>

<div class="acp-card" style="max-width:480px;">
    <div class="acp-card-header">Upload Plugin File</div>
    <form action="" method="post" enctype="multipart/form-data" class="p-3">
        <div class="form-group">
            <label>Plugin file (.zip)</label>
            <input type="file" name="file" class="form-control" required/>
        </div>
        <p style="color:#666;font-size:12px;">Make sure you upload all the plugin files before importing.</p>
        <button type="submit" name="submit" class="btn btn-primary w-100">
            <i class="bi bi-upload me-1"></i>Install Plugin
        </button>
    </form>
</div>

