<?php

use Darkheim\Application\CastleSiege\CastleSiege;

/**
 * Castle Siege Module
 *
 * @package    MuOnline CMS
 * @author     Dmytro Hovenko
 * @copyright  2024 Darkheim Development Studio
 */

$castleSiege = new CastleSiege();
$siegeData   = $castleSiege->siegeData();

if (!is_array($siegeData))          throw new Exception(lang('error_103'));
if (!$castleSiege->moduleEnabled()) throw new Exception(lang('error_47'));
?>

<div class="page-title"><span><?php echo lang('module_titles_txt_29', true); ?></span></div>

<!-- ══════════════════════════════════════════
     CASTLE OWNER
══════════════════════════════════════════ -->
<?php if ($castleSiege->showCastleOwner() && $siegeData['castle_data'][_CLMN_MCD_OCCUPY_] == 1): ?>

	<div class="info-section-title"><i class="bi bi-shield-fill"></i> <?php echo lang('castlesiege_txt_2', true); ?></div>

	<div class="cs-owner-block">
		<div class="cs-owner-logo">
			<?php echo returnGuildLogo($siegeData['castle_owner_alliance'][0]['G_Mark'], 80); ?>
		</div>
		<div class="cs-owner-info">
			<div class="cs-owner-name"><?php echo guildProfile($siegeData['castle_owner_alliance'][0]['G_Name']); ?></div>
			<div class="cs-owner-master-label"><?php echo lang('castlesiege_txt_12', true); ?></div>
			<div class="cs-owner-master"><?php echo playerProfile($siegeData['castle_owner_alliance'][0]['G_Master']); ?></div>
		</div>

		<?php if ($castleSiege->showCastleOwnerAlliance() && is_array($siegeData['castle_owner_alliance']) && count($siegeData['castle_owner_alliance']) > 1): ?>
		<div class="cs-alliance-block">
			<div class="cs-alliance-title"><?php echo lang('castlesiege_txt_13', true); ?></div>
			<div class="cs-alliance-table">
				<div class="cs-alliance-head">
					<span><?php echo lang('castlesiege_txt_16', true); ?></span>
					<span><?php echo lang('castlesiege_txt_14', true); ?></span>
					<span><?php echo lang('castlesiege_txt_15', true); ?></span>
				</div>
				<?php foreach ($siegeData['castle_owner_alliance'] as $key => $alliedGuild): ?>
					<?php if ($key === 0) continue; ?>
					<div class="cs-alliance-row">
						<span class="cs-alliance-logo"><?php echo returnGuildLogo($alliedGuild['G_Mark'], 22); ?></span>
						<span><?php echo guildProfile($alliedGuild['G_Name']); ?></span>
						<span><?php echo playerProfile($alliedGuild['G_Master']); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
	</div>

<?php endif; ?>

<!-- ══════════════════════════════════════════
     BATTLE COUNTDOWN
══════════════════════════════════════════ -->
<?php if ($castleSiege->showBattleCountdown()): ?>

	<div class="info-section-title"><i class="bi bi-hourglass-split"></i> <?php echo lang('castlesiege_txt_1', true); ?></div>

	<div class="cs-timer-block">
		<div class="cs-timer-icon"><i class="bi bi-hourglass-split"></i></div>
		<div id="siegeTimer" class="cs-timer-value">—</div>
		<div class="cs-timer-label"><?php echo lang('castlesiege_txt_1', true); ?></div>
	</div>

<?php endif; ?>

<!-- ══════════════════════════════════════════
     CASTLE INFORMATION
══════════════════════════════════════════ -->
<?php if ($castleSiege->showCastleInformation()): ?>

	<div class="info-section-title"><i class="bi bi-info-circle-fill"></i> <?php echo lang('castlesiege_txt_7', true); ?></div>

	<div class="cs-info-grid">

		<?php if ($castleSiege->showCurrentStage()): ?>
		<div class="cs-info-card">
			<div class="cs-info-card-label"><?php echo lang('castlesiege_txt_9', true); ?></div>
			<div class="cs-info-card-val"><?php echo htmlspecialchars($siegeData['current_stage']['title']); ?></div>
		</div>
		<?php endif; ?>

		<?php if ($castleSiege->showNextStage()): ?>
		<div class="cs-info-card">
			<div class="cs-info-card-label"><?php echo lang('castlesiege_txt_10', true); ?></div>
			<div class="cs-info-card-val">
				<?php echo htmlspecialchars($siegeData['next_stage']['title']); ?>
				<span class="cs-info-badge"><?php echo $siegeData['next_stage_countdown']; ?></span>
			</div>
		</div>
		<?php endif; ?>

		<?php if ($castleSiege->showBattleDuration()): ?>
		<div class="cs-info-card">
			<div class="cs-info-card-label"><?php echo lang('castlesiege_txt_11', true); ?></div>
			<div class="cs-info-card-val"><?php echo htmlspecialchars($castleSiege->getWarfareDuration()); ?></div>
		</div>
		<?php endif; ?>

		<div class="cs-info-card">
			<div class="cs-info-card-label"><?php echo lang('castlesiege_txt_5', true); ?></div>
			<div class="cs-info-card-val cs-val-gold"><?php echo (int)$siegeData['castle_data'][_CLMN_MCD_TRS_]; ?>%</div>
		</div>

		<div class="cs-info-card">
			<div class="cs-info-card-label"><?php echo lang('castlesiege_txt_4', true); ?></div>
			<div class="cs-info-card-val cs-val-gold"><?php echo (int)$siegeData['castle_data'][_CLMN_MCD_TRC_]; ?>%</div>
		</div>

		<div class="cs-info-card">
			<div class="cs-info-card-label"><?php echo lang('castlesiege_txt_6', true); ?></div>
			<div class="cs-info-card-val cs-val-gold"><?php echo (int)$siegeData['castle_data'][_CLMN_MCD_THZ_]; ?>%</div>
		</div>

		<div class="cs-info-card">
			<div class="cs-info-card-label"><?php echo lang('castlesiege_txt_3', true); ?></div>
			<div class="cs-info-card-val cs-val-gold">
				<?php echo number_format($siegeData['castle_data'][_CLMN_MCD_MONEY_]); ?>
				<span class="cs-info-unit"><?php echo lang('castlesiege_txt_8', true); ?></span>
			</div>
		</div>

	</div><!-- /.cs-info-grid -->

<?php endif; ?>

<!-- ══════════════════════════════════════════
     REGISTERED GUILDS
══════════════════════════════════════════ -->
<?php if ($castleSiege->showRegisteredGuilds() && is_array($siegeData['registered_guilds'])): ?>

	<div class="info-section-title"><i class="bi bi-people-fill"></i> <?php echo lang('castlesiege_txt_19', true); ?></div>

	<div class="cs-guilds-table">
		<div class="cs-guilds-head">
			<span><?php echo lang('castlesiege_txt_16', true); ?></span>
			<span><?php echo lang('castlesiege_txt_14', true); ?></span>
			<span><?php echo lang('castlesiege_txt_15', true); ?></span>
			<span><?php echo lang('castlesiege_txt_17', true); ?></span>
			<span><?php echo lang('castlesiege_txt_18', true); ?></span>
		</div>
		<?php foreach ($siegeData['registered_guilds'] as $i => $reg): ?>
		<div class="cs-guilds-row">
			<span class="cs-guilds-num"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></span>
			<span class="cs-guilds-logo"><?php echo returnGuildLogo($reg['G_Mark'], 22); ?></span>
			<span><?php echo guildProfile($reg['G_Name']); ?></span>
			<span><?php echo playerProfile($reg['G_Master']); ?></span>
			<span class="cs-guilds-score"><?php echo number_format($reg['G_Score']); ?></span>
			<span class="cs-guilds-members"><?php echo (int)$reg['member_count']; ?></span>
		</div>
		<?php endforeach; ?>
	</div><!-- /.cs-guilds-table -->

<?php endif; ?>

<!-- ══════════════════════════════════════════
     SCHEDULE
══════════════════════════════════════════ -->
<?php if ($castleSiege->showSchedule() && is_array($siegeData['schedule'])): ?>

	<div class="info-section-title"><i class="bi bi-calendar3"></i> <?php echo lang('castlesiege_txt_20', true); ?></div>

	<div class="cs-schedule-table">
		<div class="cs-schedule-head">
			<span><?php echo lang('castlesiege_txt_21', true); ?></span>
			<span><?php echo lang('castlesiege_txt_22', true); ?></span>
			<span><?php echo lang('castlesiege_txt_23', true); ?></span>
		</div>
		<?php foreach ($siegeData['schedule'] as $stage): ?>
		<div class="cs-schedule-row <?php echo ($siegeData['current_stage']['title'] === $stage['title']) ? 'cs-schedule-current' : ''; ?>">
			<span class="cs-schedule-stage"><?php echo htmlspecialchars($stage['title']); ?></span>
			<span><?php echo $castleSiege->friendlyDateFormat($stage['start_timestamp']); ?></span>
			<span><?php echo $castleSiege->friendlyDateFormat($stage['end_timestamp']); ?></span>
		</div>
		<?php endforeach; ?>
	</div><!-- /.cs-schedule-table -->

<?php endif; ?>

