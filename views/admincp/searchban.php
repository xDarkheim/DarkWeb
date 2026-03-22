<?php
/**
 * AdminCP search ban view.
 *
 * Variables:
 * - string      $searchRequest
 * - array|null  $results  – array of ['account','accountInfoUrl','bannedBy','banType','banDate','banDays','liftBanUrl']
 * - string|null $error
 */
?>
<h1 class="page-header"><i class="bi bi-search me-2"></i>Search Ban</h1>

<div class="acp-card mb-4">
    <form class="d-flex gap-2" role="form" method="post">
        <label>
            <input type="text" class="form-control" name="search_request"
                   placeholder="Account username" style="max-width:300px;"
                   value="<?php echo htmlspecialchars($searchRequest, ENT_QUOTES, 'UTF-8'); ?>"/>
        </label>
        <button type="submit" class="btn btn-primary" name="search_ban" value="ok">
            <i class="bi bi-search me-1"></i>Search
        </button>
    </form>
</div>

<?php if ($error !== null): ?>
    <?php \Darkheim\Application\View\MessageRenderer::toast('error', $error); ?>
<?php elseif ($results !== null): ?>
    <div class="acp-card">
        <div class="acp-card-header">
            Results for: <strong><?php echo htmlspecialchars($searchRequest, ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Account</th><th>Banned By</th><th>Type</th><th>Date</th><th>Days</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($results as $row): ?>
            <tr>
                <td>
                    <a href="<?php echo htmlspecialchars($row['accountInfoUrl'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($row['account'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($row['bannedBy'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <?php if ($row['banType'] === 'temporal'): ?>
                        <span class="label label-warning">Temporal</span>
                    <?php else: ?>
                        <span class="label label-danger">Permanent</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['banDate'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['banDays'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-end">
                    <a href="<?php echo htmlspecialchars($row['liftBanUrl'], ENT_QUOTES, 'UTF-8'); ?>"
                       class="btn btn-sm btn-danger">Lift Ban</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

