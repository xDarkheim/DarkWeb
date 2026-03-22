<?php
/**
 * AdminCP search account view.
 *
 * Variables:
 * - string       $searchRequest  – submitted username query
 * - array|null   $results        – array of ['id','username','accountInfoUrl'], or null when no search yet
 * - string|null  $error          – error message, or null
 */
?>
<h1 class="page-header"><i class="bi bi-search me-2"></i>Search Account</h1>

<div class="acp-card mb-4">
    <form class="d-flex gap-2" role="form" method="post">
        <label>
            <input type="text" class="form-control" name="search_request"
                   placeholder="Account username" style="max-width:300px;"
                   value="<?php echo htmlspecialchars($searchRequest, ENT_QUOTES, 'UTF-8'); ?>"/>
        </label>
        <button type="submit" class="btn btn-primary" name="search_account" value="ok">
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
            <thead><tr><th>Username</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-end">
                    <a href="<?php echo htmlspecialchars($row['accountInfoUrl'], ENT_QUOTES, 'UTF-8'); ?>"
                       class="btn btn-sm btn-default">Account Info</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

