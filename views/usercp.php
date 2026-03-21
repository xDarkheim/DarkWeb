<?php
/**
 * UserCP dashboard view.
 *
 * Variables provided by UsercpController:
 * - string $username
 * - string $subtitle
 * - string $statusClass
 * - string $statusText
 * - string $onlineClass
 * - string $onlineText
 * - string|null $firstCharAvatar
 * - int $charCount
 * - int $charsOnline
 * - string $creditLabel
 * - string $creditAmount
 * - array<int,array{link:string,title:string,icon:string,newTab:bool,biIcon:string,accentClass:string}> $tiles
 */
?>

<div class="ucp-dashboard-banner">
    <div class="ucp-db-left">
        <div class="ucp-db-avatar">
            <?php if ($firstCharAvatar): ?>
                <img src="<?php echo $firstCharAvatar; ?>" alt="">
            <?php else: ?>
                <i class="bi bi-person-fill"></i>
            <?php endif; ?>
        </div>
        <div class="ucp-db-identity">
            <div class="ucp-db-username"><?php echo $username; ?></div>
            <div class="ucp-db-subtitle"><?php echo $subtitle; ?></div>
            <div class="ucp-db-pills">
                <span class="ma-status-pill <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                <span class="ma-online-pill <?php echo $onlineClass; ?>"><?php echo $onlineText; ?></span>
            </div>
        </div>
    </div>

    <div class="ucp-db-stats">
        <div class="ucp-db-stat">
            <span class="ucp-db-stat-val"><?php echo $charCount; ?></span>
            <span class="ucp-db-stat-lbl"><i class="bi bi-person-badge-fill"></i> Characters</span>
        </div>

        <?php if ($charsOnline > 0): ?>
        <div class="ucp-db-stat ucp-db-stat-online">
            <span class="ucp-db-stat-val"><?php echo $charsOnline; ?></span>
            <span class="ucp-db-stat-lbl"><i class="bi bi-circle-fill"></i> Online</span>
        </div>
        <?php endif; ?>

        <?php if ($creditAmount !== ''): ?>
        <div class="ucp-db-stat ucp-db-stat-credits">
            <span class="ucp-db-stat-val"><?php echo $creditAmount; ?></span>
            <span class="ucp-db-stat-lbl"><i class="bi bi-coin"></i> <?php echo $creditLabel; ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="ucp-tiles-grid">
    <?php foreach ($tiles as $tile): ?>
    <a href="<?php echo $tile['link']; ?>"<?php echo $tile['newTab'] ? ' target="_blank"' : ''; ?> class="ucp-tile <?php echo $tile['accentClass']; ?>">
        <div class="ucp-tile-icon-wrap">
            <img src="<?php echo $tile['icon']; ?>" alt="<?php echo htmlspecialchars($tile['title'], ENT_QUOTES, 'UTF-8'); ?>" class="ucp-tile-img">
            <i class="bi <?php echo $tile['biIcon']; ?> ucp-tile-bi"></i>
        </div>
        <span class="ucp-tile-title"><?php echo $tile['title']; ?></span>
        <i class="bi bi-chevron-right ucp-tile-arrow"></i>
    </a>
    <?php endforeach; ?>
</div>

