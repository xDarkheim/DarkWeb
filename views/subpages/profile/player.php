<?php
/**
 * Player profile subpage view.
 *
 * Variables provided by ProfilePlayerSubpageController:
 * - string $classCss
 * - string $classAvatar
 * - string $playerName
 * - string $className
 * - string $onlineLabel
 * - string $level
 * - string $masterLevel
 * - bool   $hasResets
 * - string $resets
 * - bool   $hasGrandResets
 * - string $grandResets
 * - string $pkKills
 * - bool   $hasGuild
 * - string $guildHtml
 * - array<int,array{label:string,icon:string,barClass:string,percent:int|float,value:string}> $baseStats
 */
?>

<div class="<?php echo $classCss; ?>">
    <div class="pf-layout-wrap">
        <div class="pf-banner">
            <div class="pf-banner-overlay"></div>
            <div class="pf-banner-content">
                <div class="pf-avatar">
                    <img src="<?php echo $classAvatar; ?>" alt="<?php echo $playerName; ?>">
                    <?php echo $onlineLabel; ?>
                </div>
                <div class="pf-identity">
                    <h1 class="pf-name"><?php echo $playerName; ?></h1>
                    <div class="pf-class"><?php echo $className; ?></div>
                </div>
            </div>
        </div>

        <div class="pf-layout">
            <div class="pf-panel-left">
                <div class="pf-panel-section">
                    <div class="pf-panel-title"><i class="bi bi-bar-chart-fill me-1"></i> Progress</div>
                    <div class="pf-stat-list">
                        <div class="pf-stat-row"><span class="pf-stat-key">Level</span><span class="pf-stat-val"><?php echo $level; ?></span></div>
                        <div class="pf-stat-row"><span class="pf-stat-key">Master Lvl</span><span class="pf-stat-val pf-val-gold"><?php echo $masterLevel; ?></span></div>
                        <?php if ($hasResets): ?><div class="pf-stat-row"><span class="pf-stat-key">Resets</span><span class="pf-stat-val pf-val-gold"><?php echo $resets; ?></span></div><?php endif; ?>
                        <?php if ($hasGrandResets): ?><div class="pf-stat-row"><span class="pf-stat-key">GR</span><span class="pf-stat-val pf-val-gold"><?php echo $grandResets; ?></span></div><?php endif; ?>
                        <div class="pf-stat-row"><span class="pf-stat-key">PK Kills</span><span class="pf-stat-val pf-val-red"><?php echo $pkKills; ?></span></div>
                    </div>
                </div>

                <?php if ($hasGuild): ?>
                <div class="pf-panel-section">
                    <div class="pf-panel-title"><i class="bi bi-shield-fill me-1"></i> Guild</div>
                    <div class="pf-stat-list">
                        <div class="pf-stat-row"><span class="pf-stat-key">Guild</span><span class="pf-stat-val pf-val-gold"><?php echo $guildHtml; ?></span></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="pf-panel-right">
                <div class="pf-section-title"><i class="bi bi-person-fill"></i> Character Info</div>

                <div class="pf-info-grid">
                    <div class="pf-info-card"><span class="pf-info-card-icon bi bi-chevron-double-up"></span><div class="pf-info-card-body"><div class="pf-info-card-label">Level</div><div class="pf-info-card-val"><?php echo $level; ?></div></div></div>
                    <div class="pf-info-card"><span class="pf-info-card-icon pf-icon-gold bi bi-stars"></span><div class="pf-info-card-body"><div class="pf-info-card-label">Master Level</div><div class="pf-info-card-val pf-val-gold"><?php echo $masterLevel; ?></div></div></div>
                    <?php if ($hasResets): ?><div class="pf-info-card"><span class="pf-info-card-icon pf-icon-gold bi bi-arrow-repeat"></span><div class="pf-info-card-body"><div class="pf-info-card-label">Resets</div><div class="pf-info-card-val pf-val-gold"><?php echo $resets; ?></div></div></div><?php endif; ?>
                    <?php if ($hasGrandResets): ?><div class="pf-info-card"><span class="pf-info-card-icon pf-icon-gold bi bi-infinity"></span><div class="pf-info-card-body"><div class="pf-info-card-label">Grand Resets</div><div class="pf-info-card-val pf-val-gold"><?php echo $grandResets; ?></div></div></div><?php endif; ?>
                    <div class="pf-info-card"><span class="pf-info-card-icon pf-icon-red bi bi-crosshair"></span><div class="pf-info-card-body"><div class="pf-info-card-label">PK Kills</div><div class="pf-info-card-val pf-val-red"><?php echo $pkKills; ?></div></div></div>
                    <?php if ($hasGuild): ?><div class="pf-info-card"><span class="pf-info-card-icon pf-icon-gold bi bi-shield-fill"></span><div class="pf-info-card-body"><div class="pf-info-card-label">Guild</div><div class="pf-info-card-val pf-val-gold"><?php echo $guildHtml; ?></div></div></div><?php endif; ?>
                </div>

                <div class="pf-section-title pf-section-title-sub"><i class="bi bi-bar-chart-fill"></i> Base Stats</div>
                <div class="pf-stats-table">
                    <?php foreach ($baseStats as $stat): ?>
                    <div class="pf-stats-row">
                        <span class="pf-stats-name"><i class="<?php echo $stat['icon']; ?>"></i> <?php echo $stat['label']; ?></span>
                        <span class="pf-stats-bar-wrap"><span class="<?php echo $stat['barClass']; ?>" style="width:<?php echo $stat['percent']; ?>%"></span></span>
                        <span class="pf-stats-num"><?php echo $stat['value']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

