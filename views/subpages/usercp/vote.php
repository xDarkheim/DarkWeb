<?php
/**
 * UserCP vote view.
 *
 * Variables provided by VoteSubpageController:
 * - string $pageTitle
 * - string $cardTitle
 * - string $headerTitle
 * - string $headerReward
 * - string $buttonLabel
 * - array<int,array{id:string,title:string,reward:string}> $siteRows
 */
?>

<div class="page-title"><span><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></span></div>

<div class="ucp-card">
    <div class="ucp-card-header"><i class="bi bi-star-fill"></i><?php echo htmlspecialchars($cardTitle, ENT_QUOTES, 'UTF-8'); ?></div>
    <div class="ucp-card-body" style="padding:0;">
        <table class="table general-table-ui" style="margin-bottom:0;">
            <thead>
                <tr>
                    <th><?php echo htmlspecialchars($headerTitle, ENT_QUOTES, 'UTF-8'); ?></th>
                    <th><?php echo htmlspecialchars($headerReward, ENT_QUOTES, 'UTF-8'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($siteRows as $row): ?>
                <tr>
                    <td><?php echo $row['title']; ?></td>
                    <td><span class="ucp-character-level"><?php echo $row['reward']; ?></span></td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="voting_site_id" value="<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <button name="submit" value="submit" class="btn btn-primary btn-sm"><?php echo htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8'); ?></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

