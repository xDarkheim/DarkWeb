<?php
/**
 * AdminCP dashboard view.
 *
 * Variables provided by Darkheim\Application\Admincp\HomeController:
 * - bool $showInstallWarning
 * - string $installWarningHtml
 * - array<int,array{iconClass:string,iconStyle:string,backgroundStyle:string,value:string,label:string}> $statCards
 * - array<int,array{label:string,value:string,valueClass:string}> $systemRows
 * - array<int,array{url:string,iconClass:string,label:string}> $quickActions
 * - array<int,array{name:string,level:string}> $admins
 */
?>

<?php if ($showInstallWarning): ?>
<div class="p-3">
    <?php echo $installWarningHtml; ?>
</div>
<?php endif; ?>

<h1 class="page-header"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>

<div class="dash-cards">
    <?php foreach ($statCards as $card): ?>
    <div class="dash-card">
        <div class="dash-card-icon" style="<?php echo htmlspecialchars($card['backgroundStyle'], ENT_QUOTES, 'UTF-8'); ?>">
            <i class="<?php echo htmlspecialchars($card['iconClass'], ENT_QUOTES, 'UTF-8'); ?>" style="<?php echo htmlspecialchars($card['iconStyle'], ENT_QUOTES, 'UTF-8'); ?>"></i>
        </div>
        <div class="dash-card-body">
            <div class="dash-card-value"><?php echo htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="dash-card-label"><?php echo htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="dash-row">
    <div class="dash-block">
        <div class="dash-block-header"><i class="bi bi-cpu me-2"></i>System</div>
        <table class="dash-table">
            <?php foreach ($systemRows as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php if ($row['valueClass'] !== ''): ?><span class="<?php echo htmlspecialchars($row['valueClass'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($row['value'], ENT_QUOTES, 'UTF-8'); ?></span><?php else: ?><?php echo htmlspecialchars($row['value'], ENT_QUOTES, 'UTF-8'); ?><?php endif; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="dash-block">
        <div class="dash-block-header"><i class="bi bi-lightning-fill me-2"></i>Quick Actions</div>
        <div class="dash-actions">
            <?php foreach ($quickActions as $action): ?>
            <a href="<?php echo htmlspecialchars($action['url'], ENT_QUOTES, 'UTF-8'); ?>" class="dash-action-btn">
                <i class="<?php echo htmlspecialchars($action['iconClass'], ENT_QUOTES, 'UTF-8'); ?>"></i> <?php echo htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="dash-block">
        <div class="dash-block-header"><i class="bi bi-shield-fill me-2"></i>Administrators</div>
        <table class="dash-table">
            <?php foreach ($admins as $admin): ?>
            <tr>
                <td><i class="bi bi-person-fill me-1" style="color:var(--accent);"></i><?php echo htmlspecialchars($admin['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><span class="badge-level"><?php echo htmlspecialchars($admin['level'], ENT_QUOTES, 'UTF-8'); ?></span></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

