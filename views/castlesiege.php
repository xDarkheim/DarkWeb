<div class="page-title"><span><?php echo lang('module_titles_txt_29', true); ?></span></div>

<?php if ($showCastleOwner && is_array($owner)): ?>
<div class="info-section-title"><i class="bi bi-shield-fill"></i> <?php echo lang('castlesiege_txt_2', true); ?></div>
<div class="cs-owner-block">
    <div class="cs-owner-logo"><?php echo $owner['logo']; ?></div>
    <div class="cs-owner-info">
        <div class="cs-owner-name"><?php echo $owner['name']; ?></div>
        <div class="cs-owner-master-label"><?php echo lang('castlesiege_txt_12', true); ?></div>
        <div class="cs-owner-master"><?php echo $owner['master']; ?></div>
    </div>

    <?php if (!empty($ownerAllianceRows)): ?>
    <div class="cs-alliance-block">
        <div class="cs-alliance-title"><?php echo lang('castlesiege_txt_13', true); ?></div>
        <div class="cs-alliance-table">
            <div class="cs-alliance-head">
                <span><?php echo lang('castlesiege_txt_16', true); ?></span>
                <span><?php echo lang('castlesiege_txt_14', true); ?></span>
                <span><?php echo lang('castlesiege_txt_15', true); ?></span>
            </div>
            <?php foreach ($ownerAllianceRows as $row): ?>
            <div class="cs-alliance-row">
                <span class="cs-alliance-logo"><?php echo $row['logo']; ?></span>
                <span><?php echo $row['name']; ?></span>
                <span><?php echo $row['master']; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($showBattleCountdown): ?>
<div class="info-section-title"><i class="bi bi-hourglass-split"></i> <?php echo lang('castlesiege_txt_1', true); ?></div>
<div class="cs-timer-block">
    <div class="cs-timer-icon"><i class="bi bi-hourglass-split"></i></div>
    <div id="siegeTimer" class="cs-timer-value">—</div>
    <div class="cs-timer-label"><?php echo lang('castlesiege_txt_1', true); ?></div>
</div>
<?php endif; ?>

<?php if ($showCastleInformation): ?>
<div class="info-section-title"><i class="bi bi-info-circle-fill"></i> <?php echo lang('castlesiege_txt_7', true); ?></div>
<div class="cs-info-grid">
    <?php if ($showCurrentStage): ?>
    <div class="cs-info-card">
        <div class="cs-info-card-label"><?php echo lang('castlesiege_txt_9', true); ?></div>
        <div class="cs-info-card-val"><?php echo htmlspecialchars($currentStageTitle, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
    <?php endif; ?>

    <?php if ($showNextStage): ?>
    <div class="cs-info-card">
        <div class="cs-info-card-label"><?php echo lang('castlesiege_txt_10', true); ?></div>
        <div class="cs-info-card-val">
            <?php echo htmlspecialchars($nextStageTitle, ENT_QUOTES, 'UTF-8'); ?>
            <span class="cs-info-badge"><?php echo $nextStageCountdown; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($showBattleDuration): ?>
    <div class="cs-info-card">
        <div class="cs-info-card-label"><?php echo lang('castlesiege_txt_11', true); ?></div>
        <div class="cs-info-card-val"><?php echo htmlspecialchars($battleDuration, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
    <?php endif; ?>

    <div class="cs-info-card"><div class="cs-info-card-label"><?php echo lang('castlesiege_txt_5', true); ?></div><div class="cs-info-card-val cs-val-gold"><?php echo $castleTaxRateStore; ?>%</div></div>
    <div class="cs-info-card"><div class="cs-info-card-label"><?php echo lang('castlesiege_txt_4', true); ?></div><div class="cs-info-card-val cs-val-gold"><?php echo $castleTaxRateChaos; ?>%</div></div>
    <div class="cs-info-card"><div class="cs-info-card-label"><?php echo lang('castlesiege_txt_6', true); ?></div><div class="cs-info-card-val cs-val-gold"><?php echo $castleTaxRateHunt; ?>%</div></div>
    <div class="cs-info-card"><div class="cs-info-card-label"><?php echo lang('castlesiege_txt_3', true); ?></div><div class="cs-info-card-val cs-val-gold"><?php echo $castleMoney; ?><span class="cs-info-unit"><?php echo lang('castlesiege_txt_8', true); ?></span></div></div>
</div>
<?php endif; ?>

<?php if ($showRegisteredGuilds): ?>
<div class="info-section-title"><i class="bi bi-people-fill"></i> <?php echo lang('castlesiege_txt_19', true); ?></div>
<div class="cs-guilds-table">
    <div class="cs-guilds-head">
        <span><?php echo lang('castlesiege_txt_16', true); ?></span>
        <span><?php echo lang('castlesiege_txt_14', true); ?></span>
        <span><?php echo lang('castlesiege_txt_15', true); ?></span>
        <span><?php echo lang('castlesiege_txt_17', true); ?></span>
        <span><?php echo lang('castlesiege_txt_18', true); ?></span>
    </div>
    <?php foreach ($registeredGuildRows as $row): ?>
    <div class="cs-guilds-row">
        <span class="cs-guilds-num"><?php echo $row['num']; ?></span>
        <span class="cs-guilds-logo"><?php echo $row['logo']; ?></span>
        <span><?php echo $row['name']; ?></span>
        <span><?php echo $row['master']; ?></span>
        <span class="cs-guilds-score"><?php echo $row['score']; ?></span>
        <span class="cs-guilds-members"><?php echo $row['members']; ?></span>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($showSchedule): ?>
<div class="info-section-title"><i class="bi bi-calendar3"></i> <?php echo lang('castlesiege_txt_20', true); ?></div>
<div class="cs-schedule-table">
    <div class="cs-schedule-head">
        <span><?php echo lang('castlesiege_txt_21', true); ?></span>
        <span><?php echo lang('castlesiege_txt_22', true); ?></span>
        <span><?php echo lang('castlesiege_txt_23', true); ?></span>
    </div>
    <?php foreach ($scheduleRows as $stage): ?>
    <div class="cs-schedule-row <?php echo $stage['isCurrent'] ? 'cs-schedule-current' : ''; ?>">
        <span class="cs-schedule-stage"><?php echo htmlspecialchars($stage['title'], ENT_QUOTES, 'UTF-8'); ?></span>
        <span><?php echo htmlspecialchars($stage['start'], ENT_QUOTES, 'UTF-8'); ?></span>
        <span><?php echo htmlspecialchars($stage['end'], ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
