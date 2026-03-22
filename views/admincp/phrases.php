<?php
/**
 * AdminCP phrases view.
 *
 * Variables:
 * - array       $rows   – array of ['key','value']
 * - int         $count
 * - string|null $error
 */
?>
<h1 class="page-header"><i class="bi bi-translate me-2"></i>Language Phrases</h1>

<?php if ($error !== null): ?>
    <?php \Darkheim\Application\View\MessageRenderer::toast('error', $error); ?>
<?php else: ?>
<div class="acp-card">
    <div class="acp-card-header">Current Language Phrases (<?php echo $count; ?>)</div>
    <table class="table table-hover mb-0">
        <thead><tr><th style="width:35%">Key</th><th>Value</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td><code><?php echo htmlspecialchars($row['key'], ENT_QUOTES, 'UTF-8'); ?></code></td>
            <td><?php echo htmlspecialchars($row['value'], ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

