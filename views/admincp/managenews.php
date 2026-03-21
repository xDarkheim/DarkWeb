<?php
/**
 * AdminCP manage news view.
 *
 * Variables:
 * - array  $items    – array of ['id','title','author','date','publicUrl','translationLangs','addTransUrl','editUrl','deleteUrl']
 * - string $addUrl
 * - string $cacheUrl
 */
?>
<h1 class="page-header"><i class="bi bi-newspaper me-2"></i>Manage News</h1>

<div class="mb-3 d-flex gap-2">
    <a href="<?php echo htmlspecialchars($addUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Publish News
    </a>
    <a href="<?php echo htmlspecialchars($cacheUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-default">
        <i class="bi bi-arrow-clockwise me-1"></i>Rebuild Cache
    </a>
</div>

<?php if ($items !== []): ?>
    <?php foreach ($items as $item): ?>
    <div class="acp-card mb-3">
        <div class="acp-card-header d-flex justify-content-between align-items-center">
            <a href="<?php echo htmlspecialchars($item['publicUrl'], ENT_QUOTES, 'UTF-8'); ?>"
               target="_blank" style="color:var(--accent);">
                <?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
            <div class="d-flex gap-1">
                <a href="<?php echo htmlspecialchars($item['addTransUrl'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-default"><i class="bi bi-plus"></i> Translation</a>
                <a href="<?php echo htmlspecialchars($item['editUrl'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                <a href="<?php echo htmlspecialchars($item['deleteUrl'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete this news?')"><i class="bi bi-trash"></i> Delete</a>
            </div>
        </div>
        <div class="p-3">
            <table class="dash-table">
                <tr><td>News ID</td><td><?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><td>Author</td><td><?php echo htmlspecialchars($item['author'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><td>Date</td><td><?php echo htmlspecialchars($item['date'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <?php if ($item['translationLangs'] !== ''): ?>
                <tr><td>Translations</td><td><?php echo htmlspecialchars($item['translationLangs'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <?php inline_message('info', 'No news found.'); ?>
<?php endif; ?>

