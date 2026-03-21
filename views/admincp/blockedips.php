<?php
/**
 * AdminCP blocked IPs view.
 *
 * Variables:
 * - array $rows – array of ['ip','blockedBy','date','unblockUrl']
 */
?>
<h1 class="page-header"><i class="bi bi-slash-circle me-2"></i>Block IP Address <small style="font-size:13px;color:#666;">(web)</small></h1>

<div class="acp-card mb-4">
    <form class="d-flex gap-2" role="form" method="post">
        <label>
            <input type="text" class="form-control" name="ip_address" placeholder="0.0.0.0" style="max-width:300px;"/>
        </label>
        <button type="submit" class="btn btn-danger" name="submit_block" value="ok">
            <i class="bi bi-slash-circle me-1"></i>Block IP
        </button>
    </form>
</div>

<?php if ($rows !== []): ?>
<div class="acp-card">
    <div class="acp-card-header">Blocked IPs</div>
    <table id="blocked_ips" class="table table-hover mb-0">
        <thead><tr><th>IP Address</th><th>Blocked By</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td><code><?php echo htmlspecialchars($row['ip'], ENT_QUOTES, 'UTF-8'); ?></code></td>
            <td><?php echo htmlspecialchars($row['blockedBy'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="text-end">
                <a href="<?php echo htmlspecialchars($row['unblockUrl'], ENT_QUOTES, 'UTF-8'); ?>"
                   class="btn btn-sm btn-danger">Unblock</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

