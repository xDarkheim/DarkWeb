<?php
/**
 * AdminCP credit configurations view.
 *
 * Variables:
 * - bool        $isEditing   – true when editing an existing config
 * - array|null  $editConfig  – current config data when editing
 * - string      $dbName      – SQL_DB_NAME config value
 * - array       $configs     – list of all configs
 */

/** @param string $name @param array<string,string> $options @param string $checked */
function _radios(string $name, array $options, string $checked): void {
    echo '<div class="d-flex flex-wrap gap-3 mt-1">';
    foreach ($options as $val => $lbl) {
        $sel = ((string)$checked === (string)$val) ? 'checked' : '';
        echo '<label style="color:#aaa;font-size:13px;">'
            . '<input type="radio" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '" ' . $sel . '> '
            . htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8')
            . '</label>';
    }
    echo '</div>';
}
$idOptions    = ['userid' => 'User ID', 'username' => 'Username', 'email' => 'Email', 'character' => 'Character'];
$yesNoOptions = ['1' => 'Yes', '0' => 'No'];
?>
<h1 class="page-header"><i class="bi bi-sliders me-2"></i>Credit Configurations</h1>

<div class="row g-3">
    <!-- ── Form ── -->
    <div class="col-lg-4">
        <div class="acp-card">
            <?php if (!$isEditing): ?>
            <div class="acp-card-header">New Configuration</div>
            <div class="p-3">
                <form method="post">
                    <div class="form-group"><label>Title</label><input type="text" class="form-control" name="new_title" required></div>
                    <div class="form-group"><label>Database</label><?php _radios('new_database', [$dbName => $dbName], $dbName); ?></div>
                    <div class="form-group"><label>Table</label><input type="text" class="form-control" name="new_table" required></div>
                    <div class="form-group"><label>Credits Column</label><input type="text" class="form-control" name="new_credits_column" required></div>
                    <div class="form-group"><label>User Column</label><input type="text" class="form-control" name="new_user_column" required></div>
                    <div class="form-group"><label>User Identifier</label><?php _radios('new_user_column_id', $idOptions, 'userid'); ?></div>
                    <div class="form-group"><label>Check Online Status</label><?php _radios('new_checkonline', $yesNoOptions, '1'); ?></div>
                    <div class="form-group"><label>Display in My Account</label><?php _radios('new_display', $yesNoOptions, '1'); ?></div>
                    <button type="submit" name="new_submit" value="1" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i>Save</button>
                </form>
            </div>
            <?php else: ?>
            <div class="acp-card-header">Edit Configuration</div>
            <div class="p-3">
                <form method="post">
                    <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars((string)($editConfig['config_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="form-group"><label>Title</label><input type="text" class="form-control" name="edit_title" value="<?php echo htmlspecialchars((string)($editConfig['config_title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required></div>
                    <div class="form-group"><label>Database</label><?php _radios('edit_database', [$dbName => $dbName], (string)($editConfig['config_database'] ?? '')); ?></div>
                    <div class="form-group"><label>Table</label><input type="text" class="form-control" name="edit_table" value="<?php echo htmlspecialchars((string)($editConfig['config_table'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required></div>
                    <div class="form-group"><label>Credits Column</label><input type="text" class="form-control" name="edit_credits_column" value="<?php echo htmlspecialchars((string)($editConfig['config_credits_col'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required></div>
                    <div class="form-group"><label>User Column</label><input type="text" class="form-control" name="edit_user_column" value="<?php echo htmlspecialchars((string)($editConfig['config_user_col'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required></div>
                    <div class="form-group"><label>User Identifier</label><?php _radios('edit_user_column_id', $idOptions, (string)($editConfig['config_user_col_id'] ?? 'userid')); ?></div>
                    <div class="form-group"><label>Check Online Status</label><?php _radios('edit_checkonline', $yesNoOptions, (string)($editConfig['config_checkonline'] ?? '1')); ?></div>
                    <div class="form-group"><label>Display in My Account</label><?php _radios('edit_display', $yesNoOptions, (string)($editConfig['config_display'] ?? '1')); ?></div>
                    <button type="submit" name="edit_submit" value="1" class="btn btn-warning w-100"><i class="bi bi-save me-1"></i>Update</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Config List ── -->
    <div class="col-lg-8">
        <?php if ($configs !== []): ?>
            <?php foreach ($configs as $cfg): ?>
            <div class="acp-card mb-3">
                <div class="acp-card-header">
                    <span><?php echo htmlspecialchars($cfg['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <div class="d-flex gap-1">
                        <a href="<?php echo htmlspecialchars($cfg['editUrl'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-default"><i class="bi bi-pencil"></i></a>
                        <a href="<?php echo htmlspecialchars($cfg['deleteUrl'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></a>
                    </div>
                </div>
                <table class="dash-table">
                    <tr><td>Config ID</td><td><?php echo htmlspecialchars($cfg['id'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td>Database</td><td><?php echo htmlspecialchars($cfg['dbDisplay'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td>Table</td><td><code><?php echo htmlspecialchars($cfg['table'], ENT_QUOTES, 'UTF-8'); ?></code></td></tr>
                    <tr><td>Credits Column</td><td><code><?php echo htmlspecialchars($cfg['creditsCol'], ENT_QUOTES, 'UTF-8'); ?></code></td></tr>
                    <tr><td>User Column</td><td><code><?php echo htmlspecialchars($cfg['userCol'], ENT_QUOTES, 'UTF-8'); ?></code></td></tr>
                    <tr><td>User Identifier</td><td><?php echo htmlspecialchars($cfg['userColId'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><td>Online Check</td><td><?php echo $cfg['checkOnline'] ? '<span class="badge-status on">Yes</span>' : '<span class="badge-status off">No</span>'; ?></td></tr>
                    <tr><td>Display in Account</td><td><?php echo $cfg['display'] ? '<span class="badge-status on">Yes</span>' : '<span class="badge-status off">No</span>'; ?></td></tr>
                </table>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="p-3"><?php inline_message('info', 'No configurations created yet.'); ?></div>
        <?php endif; ?>
    </div>
</div>

