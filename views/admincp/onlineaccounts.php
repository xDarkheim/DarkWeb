<?php
/**
 * AdminCP online accounts view.
 *
 * Variables provided by Darkheim\Application\Admincp\OnlineAccountsController:
 * - string $pageTitle
 * - array<int,array{value:string,label:string,accent:bool}> $statBoxes
 * - array<int,array{accountHtml:string,ipAddress:string,server:string}> $rows
 * - string $emptyStateText
 */
?>

<h1 class="page-header"><i class="bi bi-broadcast-pin me-2"></i><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>

<?php if ($statBoxes !== []): ?>
<div class="acp-stat-row mb-4">
    <?php foreach ($statBoxes as $box): ?>
    <div class="acp-stat-box<?php echo $box['accent'] ? ' accent' : ''; ?>">
        <div class="acp-stat-val"><?php echo htmlspecialchars($box['value'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="acp-stat-lbl"><?php echo htmlspecialchars($box['label'], ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="acp-card">
    <div class="acp-card-header">Account List</div>
    <?php if ($rows !== []): ?>
    <table class="table table-hover mb-0">
        <thead>
        <tr>
            <th>Account</th>
            <th>IP Address</th>
            <th>Server</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td><?php echo $row['accountHtml']; ?></td>
            <td><code><?php echo htmlspecialchars($row['ipAddress'], ENT_QUOTES, 'UTF-8'); ?></code></td>
            <td><?php echo htmlspecialchars($row['server'], ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="p-3">
        <div class="alert alert-info mb-0"><?php echo htmlspecialchars($emptyStateText, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
    <?php endif; ?>
</div>

