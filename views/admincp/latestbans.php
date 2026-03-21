<?php
/**
 * AdminCP latest bans view.
 *
 * Variables:
 * - array $temporalBans   – array of ['account','bannedBy','banDate','banDays','banReason','liftBanUrl']
 * - array $permanentBans  – same shape
 */
?>
<h1 class="page-header"><i class="bi bi-person-fill-slash me-2"></i>Latest Bans</h1>

<div class="acp-tabs-wrap">
    <div class="acp-tabs">
        <button class="acp-tab active" onclick="switchTab('tab-temp',this)">Temporal Bans</button>
        <button class="acp-tab" onclick="switchTab('tab-perm',this)">Permanent Bans</button>
    </div>

    <div class="acp-tab-content active" id="tab-temp">
        <?php if ($temporalBans !== []): ?>
        <table class="table table-hover mb-0">
            <thead><tr><th>Account</th><th>Banned By</th><th>Date</th><th>Days</th><th>Reason</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($temporalBans as $ban): ?>
            <tr>
                <td><?php echo htmlspecialchars($ban['account'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($ban['bannedBy'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($ban['banDate'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($ban['banDays'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($ban['banReason'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-end">
                    <a href="<?php echo htmlspecialchars($ban['liftBanUrl'], ENT_QUOTES, 'UTF-8'); ?>"
                       class="btn btn-sm btn-danger">Lift Ban</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="p-3"><?php inline_message('info', 'No temporal bans logged.'); ?></div>
        <?php endif; ?>
    </div>

    <div class="acp-tab-content" id="tab-perm" style="display:none">
        <?php if ($permanentBans !== []): ?>
        <table class="table table-hover mb-0">
            <thead><tr><th>Account</th><th>Banned By</th><th>Date</th><th>Reason</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($permanentBans as $ban): ?>
            <tr>
                <td><?php echo htmlspecialchars($ban['account'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($ban['bannedBy'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($ban['banDate'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($ban['banReason'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-end">
                    <a href="<?php echo htmlspecialchars($ban['liftBanUrl'], ENT_QUOTES, 'UTF-8'); ?>"
                       class="btn btn-sm btn-danger">Lift Ban</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="p-3"><?php inline_message('info', 'No permanent bans logged.'); ?></div>
        <?php endif; ?>
    </div>
</div>

