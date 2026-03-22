<?php
/**
 * AdminCP PayPal donations view.
 *
 * Variables:
 * - array       $rows  – array of ['transactionId','username','accountInfoUrl','amount','paypalEmail','date','statusOk']
 * - string|null $error
 */
?>
<h1 class="page-header"><i class="bi bi-paypal me-2"></i>PayPal Donations</h1>

<?php if ($error !== null): ?>
    <?php \Darkheim\Application\View\MessageRenderer::toast('error', $error); ?>
<?php else: ?>
<div class="acp-card">
    <div class="acp-card-header">Transactions</div>
    <table id="paypal_donations" class="table table-hover mb-0">
        <thead>
            <tr><th>Transaction ID</th><th>Account</th><th>Amount</th><th>PayPal Email</th><th>Date</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td><code><?php echo htmlspecialchars($row['transactionId'], ENT_QUOTES, 'UTF-8'); ?></code></td>
            <td>
                <a href="<?php echo htmlspecialchars($row['accountInfoUrl'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </td>
            <td>$<?php echo htmlspecialchars($row['amount'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($row['paypalEmail'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
                <?php if ($row['statusOk']): ?>
                    <span class="badge-status on">OK</span>
                <?php else: ?>
                    <span class="badge-status off">Reversed</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

