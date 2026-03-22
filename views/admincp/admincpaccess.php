<?php
/**
 * AdminCP access control view.
 *
 * Variables:
 * - array $adminRows – array of ['username','level']
 * - bool  $hasAdmins
 */
?>
<h1 class="page-header"><i class="bi bi-shield-fill-check me-2"></i>AdminCP Access</h1>
<p class="mb-3" style="color:#777;">Set the access level to 0 to remove an admin.</p>

<?php if ($hasAdmins): ?>
<div class="acp-card" style="max-width:600px;">
    <div class="acp-card-header">Administrators</div>
    <form action="" method="post">
        <table class="table table-hover mb-0">
            <thead><tr><th>Account</th><th>Access Level</th></tr></thead>
            <tbody>
            <?php foreach ($adminRows as $row): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                <td>
                    <input type="number" class="form-control" style="width:100px;" min="0" max="100"
                           name="<?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?>"
                           value="<?php echo htmlspecialchars($row['level'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td><input type="text" class="form-control" name="new_admin" placeholder="New admin username"></td>
                <td><input type="number" class="form-control" style="width:100px;" min="1" max="100" name="new_access" placeholder="Level"></td>
            </tr>
            </tbody>
        </table>
        <div class="p-3">
            <button type="submit" name="settings_submit" value="ok" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Save
            </button>
        </div>
    </form>
</div>
<?php else: ?>
    <?php \Darkheim\Application\View\MessageRenderer::toast('error', 'Admins list is empty.'); ?>
<?php endif; ?>

