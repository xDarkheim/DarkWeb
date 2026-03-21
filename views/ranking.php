<?php
/**
 * Generic rankings subpage table view.
 *
 * Variables provided by RankingsSectionController:
 * - string $pageTitle
 * - array<int,array{label:string,subpage:string,isActive:bool,url:string}> $menuItems
 * - array<int,array{onclick:string,avatarHtml:string,label:string,linkClass:string}> $filterItems
 * - array<int,string> $tableHeaders
 * - array<int,array{rowClass:string,dataClassId:?int,cells:array<int,string>}> $rows
 * - string|null $updatedAtText
 */
?>

<div class="page-title"><span><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></span></div>

<div class="rankings_menu" id="rankings-anchor">
    <?php foreach ($menuItems as $item): ?>
    <a href="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $item['isActive'] ? ' active rankings-nav-link' : ' rankings-nav-link'; ?>"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></a>
    <?php endforeach; ?>
</div>

<?php if ($filterItems !== []): ?>
<div class="text-center">
    <ul class="rankings-class-filter">
        <?php foreach ($filterItems as $item): ?>
        <li>
            <a onclick="<?php echo htmlspecialchars($item['onclick'], ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo htmlspecialchars($item['linkClass'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo $item['avatarHtml']; ?>
                <br /><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<table class="table rankings-table">
    <tr>
        <?php foreach ($tableHeaders as $header): ?>
        <td style="font-weight:bold;"><?php echo htmlspecialchars($header, ENT_QUOTES, 'UTF-8'); ?></td>
        <?php endforeach; ?>
    </tr>

    <?php foreach ($rows as $row): ?>
    <tr<?php if ($row['dataClassId'] !== null): ?> data-class-id="<?php echo $row['dataClassId']; ?>"<?php endif; ?> class="<?php echo htmlspecialchars($row['rowClass'], ENT_QUOTES, 'UTF-8'); ?>">
        <?php foreach ($row['cells'] as $cell): ?>
        <td><?php echo $cell; ?></td>
        <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
</table>

<?php if ($updatedAtText !== null && $updatedAtText !== ''): ?>
<div class="rankings-update-time"><?php echo htmlspecialchars($updatedAtText, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

