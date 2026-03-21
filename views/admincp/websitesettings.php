<?php
/**
 * AdminCP website settings view.
 *
 * Variables:
 * - array<int,array{key:string,label:string,description:string,type:string,value:string}> $rows
 */
?>
<h1 class="page-header"><i class="bi bi-gear-fill me-2"></i>Website Settings</h1>

<div class="acp-card">
    <div class="acp-card-header">Configuration</div>
    <form action="" method="post">
        <table class="table table-hover module_config_tables" style="table-layout:fixed;">
            <?php foreach ($rows as $row): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    <p class="setting-description"><?php echo htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                </td>
                <td>
                    <?php if ($row['type'] === 'bool'): ?>
                        <div class="radio">
                            <label>
                                <input type="radio" name="<?php echo htmlspecialchars($row['key'], ENT_QUOTES, 'UTF-8'); ?>" value="1" <?php echo $row['value'] === '1' ? 'checked' : ''; ?>>
                                Enabled
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="<?php echo htmlspecialchars($row['key'], ENT_QUOTES, 'UTF-8'); ?>" value="0" <?php echo $row['value'] === '0' ? 'checked' : ''; ?>>
                                Disabled
                            </label>
                        </div>
                    <?php else: ?>
                        <?php $isRequired = ($row['required'] ?? true) ? 'required' : ''; ?>
                        <input type="text" class="form-control"
                               name="<?php echo htmlspecialchars($row['key'], ENT_QUOTES, 'UTF-8'); ?>"
                               aria-label="<?php echo htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8'); ?>"
                               value="<?php echo htmlspecialchars($row['value'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isRequired; ?>>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div class="p-3">
            <button type="submit" name="settings_submit" value="ok" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Save Settings
            </button>
        </div>
    </form>
</div>

