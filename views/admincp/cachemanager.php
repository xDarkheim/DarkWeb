<?php
/**
 * AdminCP cache manager view.
 *
 * Variables provided by Darkheim\Application\Admincp\CacheManagerController:
 * - string $pageTitle
 * - array<int,array{file:string,size:string,lastModified:string,writableLabel:string,writableClass:string,clearUrl:string}> $cacheRows
 * - array<int,array{label:string,fileCount:string,totalSize:string,deleteUrl:string,showDelete:bool,deleteLabel:string}> $profileCards
 */
?>

<h1 class="page-header"><i class="bi bi-arrow-clockwise me-2"></i><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>

<div class="acp-card mb-4">
    <div class="acp-card-header">Cache Files</div>
    <table class="table table-hover mb-0">
        <thead>
        <tr>
            <th>File</th>
            <th>Size</th>
            <th>Last Modified</th>
            <th>Writable</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($cacheRows as $row): ?>
        <tr>
            <td><code><?php echo htmlspecialchars($row['file'], ENT_QUOTES, 'UTF-8'); ?></code></td>
            <td><?php echo htmlspecialchars($row['size'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($row['lastModified'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><span class="<?php echo htmlspecialchars($row['writableClass'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($row['writableLabel'], ENT_QUOTES, 'UTF-8'); ?></span></td>
            <td class="text-end"><a href="<?php echo htmlspecialchars($row['clearUrl'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-danger">Clear</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="row g-3">
    <?php foreach ($profileCards as $card): ?>
    <div class="col-md-6">
        <div class="acp-card">
            <div class="acp-card-header"><?php echo htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></div>
            <table class="dash-table">
                <tr><td>Cache Files</td><td><?php echo htmlspecialchars($card['fileCount'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><td>Total Size</td><td><?php echo htmlspecialchars($card['totalSize'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
            </table>
            <?php if ($card['showDelete']): ?>
            <div class="p-3"><a href="<?php echo htmlspecialchars($card['deleteUrl'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-danger"><?php echo htmlspecialchars($card['deleteLabel'], ENT_QUOTES, 'UTF-8'); ?></a></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

