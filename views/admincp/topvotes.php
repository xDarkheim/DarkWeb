<?php
/**
 * AdminCP top voters view.
 *
 * Variables:
 * - array       $rows   – array of ['rank','username','totalVotes']
 * - string      $month  – e.g. "March 2026"
 * - string|null $error
 */
?>
<h1 class="page-header"><i class="bi bi-trophy me-2"></i>Top Voters</h1>

<?php if ($error !== null): ?>
    <?php message('error', $error); ?>
<?php else: ?>
<div class="acp-card">
    <div class="acp-card-header">Top Voters — <?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8'); ?></div>
    <table class="table table-hover mb-0">
        <thead><tr><th>#</th><th>Account</th><th>Votes</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td><?php echo htmlspecialchars((string) $row['rank'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><strong><?php echo htmlspecialchars($row['totalVotes'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

