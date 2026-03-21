<?php
/**
 * AdminCP find accounts from IP view.
 *
 * Variables:
 * - string      $ipAddress  – submitted IP address
 * - array|null  $results    – array of ['account','accountInfoUrl'], or null when no search yet
 * - string|null $error      – error message, or null
 */
?>
<h1 class="page-header"><i class="bi bi-search me-2"></i>Find Accounts from IP</h1>

<div class="acp-card mb-4">
    <form class="d-flex gap-2" role="form" method="post">
        <label>
            <input type="text" class="form-control" name="ip_address"
                   placeholder="0.0.0.0" style="max-width:300px;"
                   value="<?php echo htmlspecialchars($ipAddress, ENT_QUOTES, 'UTF-8'); ?>"/>
        </label>
        <button type="submit" class="btn btn-primary" name="search_ip" value="ok">
            <i class="bi bi-search me-1"></i>Search
        </button>
    </form>
</div>

<?php if ($error !== null): ?>
    <?php message('error', $error); ?>
<?php elseif ($results !== null): ?>
    <div class="acp-card">
        <div class="acp-card-header">
            Results for IP: <strong><?php echo htmlspecialchars($ipAddress, ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>
        <?php if ($results !== []): ?>
        <table class="table table-hover mb-0">
            <thead><tr><th>Account</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['account'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-end">
                    <a href="<?php echo htmlspecialchars($row['accountInfoUrl'], ENT_QUOTES, 'UTF-8'); ?>"
                       class="btn btn-sm btn-default">Account Info</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="p-3"><?php inline_message('info', 'No accounts found linked to this IP.'); ?></div>
        <?php endif; ?>
    </div>
<?php endif; ?>

