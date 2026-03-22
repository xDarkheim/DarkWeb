<?php
/**
 * AdminCP credits manager view.
 *
 * Variables:
 * - string $configSelectHtml  – pre-built <select> HTML
 * - array  $logRows  – array of ['config','identifier','credits','transaction','date','module','inAdmincp']
 */
?>
<h1 class="page-header"><i class="bi bi-cash-coin me-2"></i>Credit Manager</h1>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="acp-card">
            <div class="acp-card-header">Add / Subtract Credits</div>
            <div class="p-3">
                <form role="form" method="post">
                    <div class="form-group">
                        <label>Configuration</label>
                        <?php echo $configSelectHtml; ?>
                    </div>
                    <div class="form-group">
                        <label>Identifier</label>
                        <input type="text" class="form-control" name="identifier" placeholder="username / email / character">
                        <p style="font-size:11px;color:#666;margin-top:4px;">Depends on the selected configuration.</p>
                    </div>
                    <div class="form-group">
                        <label>Credits</label>
                        <input type="number" class="form-control" name="credits" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label>Transaction</label>
                        <div class="d-flex gap-3">
                            <label style="color:#aaa;"><input type="radio" name="transaction" value="add" checked> Add</label>
                            <label style="color:#aaa;"><input type="radio" name="transaction" value="subtract"> Subtract</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-left-right me-1"></i>Execute
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="acp-card">
            <div class="acp-card-header">Transaction Logs</div>
            <?php if ($logRows !== []): ?>
            <table id="credits_logs" class="table table-hover mb-0">
                <thead>
                    <tr><th>Config</th><th>Identifier</th><th>Credits</th><th>Transaction</th><th>Date</th><th>Module</th><th>AdminCP</th></tr>
                </thead>
                <tbody>
                <?php foreach ($logRows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['config'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['identifier'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['credits'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php if ($row['transaction'] === 'add'): ?>
                            <span class="badge-status on">Add</span>
                        <?php else: ?>
                            <span class="badge-status off">Sub</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['module'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php if ($row['inAdmincp']): ?>
                            <span class="badge-status on">Yes</span>
                        <?php else: ?>
                            <span class="badge-status off">No</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="p-3"><?php \Darkheim\Application\View\MessageRenderer::inline('info', 'No logs found.'); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

