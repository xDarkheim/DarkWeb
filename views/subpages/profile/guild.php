<?php
/**
 * Guild profile subpage view.
 *
 * Variables provided by ProfileGuildSubpageController:
 * - string $guildLogoHtml
 * - string $guildName
 * - string $guildMasterHtml
 * - int    $memberCount
 * - string $guildScore
 * - bool   $hasMembers
 * - array<int, array{num:string,name:string,role:string}> $memberRows
 * - int    $memberRowsCount
 */
?>

<div>
    <div class="pf-layout-wrap">
        <div class="pf-banner pf-banner-guild">
            <div class="pf-banner-overlay"></div>
            <div class="pf-banner-content">
                <div class="pf-guild-logo"><?php echo $guildLogoHtml; ?></div>
                <div class="pf-identity">
                    <h1 class="pf-name"><?php echo $guildName; ?></h1>
                    <div class="pf-guild-meta">
                        <span class="pf-guild-meta-item">
                            <span class="pf-guild-meta-label">Master</span>
                            <span class="pf-guild-meta-val"><?php echo $guildMasterHtml; ?></span>
                        </span>
                        <span class="pf-guild-meta-sep">|</span>
                        <span class="pf-guild-meta-item">
                            <span class="pf-guild-meta-label">Members</span>
                            <span class="pf-guild-meta-val"><?php echo $memberCount; ?></span>
                        </span>
                        <span class="pf-guild-meta-sep">|</span>
                        <span class="pf-guild-meta-item">
                            <span class="pf-guild-meta-label">Score</span>
                            <span class="pf-guild-meta-val"><?php echo $guildScore; ?></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pf-layout">
            <div class="pf-panel-left">
                <div class="pf-panel-section">
                    <div class="pf-panel-title">Guild Info</div>
                    <div class="pf-stat-list">
                        <div class="pf-stat-row"><span class="pf-stat-key">Name</span><span class="pf-stat-val"><?php echo $guildName; ?></span></div>
                        <div class="pf-stat-row"><span class="pf-stat-key">Score</span><span class="pf-stat-val pf-val-gold"><?php echo $guildScore; ?></span></div>
                        <div class="pf-stat-row"><span class="pf-stat-key">Members</span><span class="pf-stat-val"><?php echo $memberCount; ?></span></div>
                        <div class="pf-stat-row"><span class="pf-stat-key">Alliance</span><span class="pf-stat-val pf-val-muted">- <span class="pf-stub-tag">Soon</span></span></div>
                        <div class="pf-stat-row"><span class="pf-stat-key">Hostility</span><span class="pf-stat-val pf-val-muted">- <span class="pf-stub-tag">Soon</span></span></div>
                        <div class="pf-stat-row"><span class="pf-stat-key">Castle</span><span class="pf-stat-val pf-val-muted">None <span class="pf-stub-tag">Soon</span></span></div>
                    </div>
                </div>

                <div class="pf-panel-section">
                    <div class="pf-panel-title">Guild Master</div>
                    <div class="pf-guild-master-block">
                        <span class="pf-gm-crown">👑</span>
                        <span class="pf-gm-name"><?php echo $guildMasterHtml; ?></span>
                        <span class="pf-gm-badge">Master</span>
                    </div>
                </div>

                <div class="pf-panel-section">
                    <div class="pf-panel-title">Guild Notice</div>
                    <div class="pf-stub-notice"><span>📋</span><span>No notice has been set by the guild master.</span></div>
                </div>
            </div>

            <div class="pf-panel-right">
                <div class="pf-section-title">Members <span class="pf-panel-title-count"><?php echo $memberRowsCount; ?></span></div>

                <?php if ($hasMembers): ?>
                <div class="pf-members-grid">
                    <?php foreach ($memberRows as $row): ?>
                    <div class="pf-member-item">
                        <span class="pf-member-num"><?php echo $row['num']; ?></span>
                        <span class="pf-member-name"><?php echo $row['name']; ?></span>
                        <span class="pf-member-role"><?php echo $row['role']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="pf-stub-notice"><span>👥</span><span>No additional members in this guild.</span></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

