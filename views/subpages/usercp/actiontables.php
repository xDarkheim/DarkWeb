<?php
/**
 * Generic UserCP character action table view.
 *
 * Variables provided by a UserCP subpage controller:
 * - string $pageTitle
 * - string $cardTitle
 * - string $cardIconClass
 * - array<int,string> $tableHeaders
 * - array<int,array{character:string,cells:array<int,string>,buttonLabel:string}> $rows
 * - array<int,string> $requirementsLines
 */
?>

<div class="page-title"><span><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></span></div>

<div class="ucp-card">
    <div class="ucp-card-header"><i class="<?php echo htmlspecialchars($cardIconClass, ENT_QUOTES, 'UTF-8'); ?>"></i><?php echo htmlspecialchars($cardTitle, ENT_QUOTES, 'UTF-8'); ?></div>
    <div class="ucp-card-body" style="padding:0;">
        <table class="table general-table-ui" style="margin-bottom:0;">
            <thead>
                <tr>
                    <?php foreach ($tableHeaders as $header): ?>
                    <th><?php echo $header; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($row['cells'] as $cell): ?>
                    <td><?php echo $cell; ?></td>
                    <?php endforeach; ?>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="character" value="<?php echo htmlspecialchars($row['character'], ENT_QUOTES, 'UTF-8'); ?>">
                            <button name="submit" value="submit" class="btn btn-primary btn-sm"><?php echo htmlspecialchars($row['buttonLabel'], ENT_QUOTES, 'UTF-8'); ?></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($requirementsLines !== []): ?>
<div class="module-requirements text-center">
    <?php foreach ($requirementsLines as $line): ?>
    <p><?php echo $line; ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>


