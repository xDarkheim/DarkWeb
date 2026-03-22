<?php
/**
 * AdminCP cron manager view.
 *
 * Variables:
 * - array  $rows             – cron job rows
 * - array  $intervalOptions  – array of ['value','label']
 * - string $cronFilesHtml    – pre-built <option> HTML for file select
 * - string $addUrl
 * - string $bulkEnableUrl
 * - string $bulkDisableUrl
 * - string $bulkResetUrl
 */
?>
<h1 class="page-header"><i class="bi bi-clock-history me-2"></i>Cron Job Manager</h1>

<div class="row g-4">
    <!-- Left: add form + bulk -->
    <div class="col-lg-3">
        <div class="acp-card mb-3">
            <div class="acp-card-header">Add Cron Job</div>
            <div class="p-3">
                <form action="<?php echo htmlspecialchars($addUrl, ENT_QUOTES, 'UTF-8'); ?>" method="post">
                    <div class="form-group"><label for="cron_name">Name</label><input id="cron_name" type="text" class="form-control" name="cron_name" required/></div>
                    <div class="form-group">
                        <label for="cron_file">File</label>
                        <select id="cron_file" class="form-control" name="cron_file"><?php echo $cronFilesHtml; ?></select>
                    </div>
                    <div class="form-group">
                        <label for="cron_time">Repeat</label>
                        <select id="cron_time" class="form-control" name="cron_time">
                            <?php foreach ($intervalOptions as $opt): ?>
                            <option value="<?php echo htmlspecialchars($opt['value'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($opt['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="submit" value="Add" class="btn btn-primary w-100">Add Cron</button>
                </form>
            </div>
        </div>

        <div class="acp-card mb-3">
            <div class="acp-card-header">Bulk Actions</div>
            <div class="p-3 d-flex flex-column gap-2">
                <a href="<?php echo htmlspecialchars($bulkEnableUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-default">Enable All</a>
                <a href="<?php echo htmlspecialchars($bulkDisableUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-default">Disable All</a>
                <a href="<?php echo htmlspecialchars($bulkResetUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-default">Reset All</a>
            </div>
        </div>

        <div class="acp-card">
            <div class="acp-card-header">CLI Cron</div>
            <div class="p-3">
                <code>php /var/www/html/bin/cron.php</code>
            </div>
        </div>
    </div>

    <!-- Right: cron list -->
    <div class="col-lg-9">
        <div class="acp-card">
            <div class="acp-card-header">Scheduled Tasks</div>
            <?php if ($rows !== []): ?>
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Name</th><th>File</th><th>Repeat</th><th>Last Run</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><code><?php echo htmlspecialchars($row['file'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                    <td><?php echo htmlspecialchars($row['interval'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $row['lastRun'] !== null ? htmlspecialchars($row['lastRun'], ENT_QUOTES, 'UTF-8') : '<i style="color:#555">Never</i>'; ?></td>
                    <td>
                        <?php if ($row['isOn']): ?>
                            <a href="<?php echo htmlspecialchars($row['disableUrl'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></a>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($row['enableUrl'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-default"><i class="bi bi-pause"></i></a>
                        <?php endif; ?>
                    </td>
                    <td class="text-end d-flex gap-1 justify-content-end">
                        <a href="<?php echo htmlspecialchars($row['resetUrl'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-default" title="Reset"><i class="bi bi-arrow-clockwise"></i></a>
                        <?php if (!$row['protected']): ?>
                        <a href="<?php echo htmlspecialchars($row['deleteUrl'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="p-3"><?php \Darkheim\Application\View\MessageRenderer::inline('info', 'No cron jobs found.'); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

