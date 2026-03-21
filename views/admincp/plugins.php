<?php
/**
 * AdminCP plugins manager view.
 *
 * Variables:
 * - bool   $systemEnabled
 * - string $importUrl
 * - array  $rows – array of ['id','name','author','version','compatibility','installDate','isEnabled',
 *                            'enableUrl','disableUrl','uninstallUrl','allowUninstall']
 */
?>
<h1 class="page-header"><i class="bi bi-plug-fill me-2"></i>Plugin Manager</h1>

<?php if (!$systemEnabled): ?>
    <?php inline_message('warning', 'The plugin system is not enabled. Enable it in Website Settings.'); ?>
<?php endif; ?>

<div class="mb-3">
    <a href="<?php echo htmlspecialchars($importUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">
        <i class="bi bi-download me-1"></i>Import Plugin
    </a>
</div>

<?php if ($rows !== []): ?>
<div class="acp-card">
    <div class="acp-card-header">Installed Plugins</div>
    <table class="table table-hover mb-0">
        <thead>
            <tr><th>Name</th><th>Author</th><th>Version</th><th>Compatibility</th><th>Installed</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
            <td><?php echo htmlspecialchars($row['author'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($row['version'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($row['compatibility'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($row['installDate'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
                <?php if ($row['isEnabled']): ?>
                    <span class="badge-status on">Enabled</span>
                <?php else: ?>
                    <span class="badge-status off">Disabled</span>
                <?php endif; ?>
            </td>
            <td class="text-end">
                <?php if ($row['isEnabled']): ?>
                    <a href="<?php echo htmlspecialchars($row['disableUrl'], ENT_QUOTES, 'UTF-8'); ?>"
                       class="btn btn-sm btn-default">Disable</a>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($row['enableUrl'], ENT_QUOTES, 'UTF-8'); ?>"
                       class="btn btn-sm btn-success">Enable</a>
                <?php endif; ?>
                <?php if ($row['allowUninstall']): ?>
                    <a href="<?php echo htmlspecialchars($row['uninstallUrl'], ENT_QUOTES, 'UTF-8'); ?>"
                       class="btn btn-sm btn-danger ms-1"
                       onclick="return confirm('Uninstall?')">Uninstall</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <?php inline_message('info', 'No plugins installed yet.'); ?>
<?php endif; ?>

