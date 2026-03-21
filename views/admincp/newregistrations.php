<?php
/**
 * AdminCP new registrations view.
 *
 * Variables provided by Darkheim\Application\Admincp\NewRegistrationsController:
 * - string $pageTitle
 * - string $cardTitle
 * - array<int,array{id:string,username:string,email:string,accountInfoUrl:string}> $rows
 * - string $emptyStateText
 */
?>

<h1 class="page-header"><i class="bi bi-person-plus-fill me-2"></i><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>

<?php if ($rows !== []): ?>
<div class="acp-card">
    <div class="acp-card-header"><?php echo htmlspecialchars($cardTitle, ENT_QUOTES, 'UTF-8'); ?></div>
    <table id="new_registrations" class="table table-hover mb-0">
        <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="text-end"><a href="<?php echo htmlspecialchars($row['accountInfoUrl'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-default">Account Info</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="p-3">
    <div class="alert alert-info mb-0"><?php echo htmlspecialchars($emptyStateText, ENT_QUOTES, 'UTF-8'); ?></div>
</div>
<?php endif; ?>

