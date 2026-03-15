<?php

use Darkheim\Application\Profile\ProfileRepository as weProfiles;

loadModuleConfigs('profiles');

if(!mconfig('active')) {
	inline_message('error', lang('error_47', true));
	return;
}

if(!isset($_GET['req'])) {
	inline_message('error', lang('error_25', true));
	return;
}

try {
	$weProfiles = new weProfiles();
	$weProfiles->setType("player");
	$weProfiles->setRequest($_GET['req']);
	$cData = $weProfiles->data();

	$onlineStatus = 0;
	$onlineCharactersCache = loadCache('online_characters.cache');
	if(is_array($onlineCharactersCache) && in_array(
                    $cData[1],
                    $onlineCharactersCache,
                    true
            )
    ) {
		$onlineStatus = 1;
	}

	$className   = $custom['character_class'][$cData[2]][0] ?? '—';
	$classCss    = $custom['character_class'][$cData[2]][1] ?? '';
	$classAvatar = getPlayerClassAvatar($cData[2], false);
	$hasCmd      = isset($custom['character_class'][$cData[2]]['base_stats']['cmd'])
	               && $custom['character_class'][$cData[2]]['base_stats']['cmd'] > 0;

	$onlineLabel = $onlineStatus
		? '<span class="profile-badge online">'.lang('profiles_txt_18', true).'</span>'
		: '<span class="profile-badge offline">'.lang('profiles_txt_19', true).'</span>';
?>

<div class="<?php echo htmlspecialchars($classCss); ?>">


	<div class="pf-layout-wrap">

		<!-- BANNER -->
		<div class="pf-banner">
			<div class="pf-banner-overlay"></div>
			<div class="pf-banner-content">
				<div class="pf-avatar">
					<img src="<?php echo $classAvatar; ?>" alt="<?php echo htmlspecialchars($cData[1]); ?>">
					<?php echo $onlineLabel; ?>
				</div>
				<div class="pf-identity">
					<h1 class="pf-name"><?php echo htmlspecialchars($cData[1]); ?></h1>
					<div class="pf-class"><?php echo htmlspecialchars($className); ?></div>
				</div>
			</div>
		</div>

		<!-- BODY -->
		<div class="pf-layout">

			<!-- LEFT — sidebar -->
			<div class="pf-panel-left">

				<div class="pf-panel-section">
					<div class="pf-panel-title"><i class="bi bi-bar-chart-fill me-1"></i> Progress</div>
					<div class="pf-stat-list">
						<div class="pf-stat-row">
							<span class="pf-stat-key">Level</span>
							<span class="pf-stat-val"><?php echo number_format($cData[3]); ?></span>
						</div>
						<div class="pf-stat-row">
							<span class="pf-stat-key">Master Lvl</span>
							<span class="pf-stat-val pf-val-gold"><?php echo number_format($cData[14]); ?></span>
						</div>
						<?php if(check_value($cData[4])): ?>
						<div class="pf-stat-row">
							<span class="pf-stat-key">Resets</span>
							<span class="pf-stat-val pf-val-gold"><?php echo number_format($cData[4]); ?></span>
						</div>
						<?php endif; ?>
						<?php if(check_value($cData[11])): ?>
						<div class="pf-stat-row">
							<span class="pf-stat-key">GR</span>
							<span class="pf-stat-val pf-val-gold"><?php echo number_format($cData[11]); ?></span>
						</div>
						<?php endif; ?>
						<div class="pf-stat-row">
							<span class="pf-stat-key">PK Kills</span>
							<span class="pf-stat-val pf-val-red"><?php echo number_format($cData[10]); ?></span>
						</div>
					</div>
				</div>

				<?php if(check_value($cData[12])): ?>
				<div class="pf-panel-section">
					<div class="pf-panel-title"><i class="bi bi-shield-fill me-1"></i> Guild</div>
					<div class="pf-stat-list">
						<div class="pf-stat-row">
							<span class="pf-stat-key">Guild</span>
							<span class="pf-stat-val pf-val-gold"><?php echo guildProfile($cData[12]); ?></span>
						</div>
					</div>
				</div>
				<?php endif; ?>

			</div><!-- /.pf-panel-left -->

			<!-- RIGHT — main like ucp-main -->
			<div class="pf-panel-right">

				<div class="pf-section-title">
					<i class="bi bi-person-fill"></i>
					Character Info
				</div>

				<!-- Character Info Cards -->
				<div class="pf-info-grid">

					<div class="pf-info-card">
						<span class="pf-info-card-icon bi bi-chevron-double-up"></span>
						<div class="pf-info-card-body">
							<div class="pf-info-card-label">Level</div>
							<div class="pf-info-card-val"><?php echo number_format($cData[3]); ?></div>
						</div>
					</div>

					<div class="pf-info-card">
						<span class="pf-info-card-icon pf-icon-gold bi bi-stars"></span>
						<div class="pf-info-card-body">
							<div class="pf-info-card-label">Master Level</div>
							<div class="pf-info-card-val pf-val-gold"><?php echo number_format($cData[14]); ?></div>
						</div>
					</div>

					<?php if(check_value($cData[4])): ?>
					<div class="pf-info-card">
						<span class="pf-info-card-icon pf-icon-gold bi bi-arrow-repeat"></span>
						<div class="pf-info-card-body">
							<div class="pf-info-card-label">Resets</div>
							<div class="pf-info-card-val pf-val-gold"><?php echo number_format($cData[4]); ?></div>
						</div>
					</div>
					<?php endif; ?>

					<?php if(check_value($cData[11])): ?>
					<div class="pf-info-card">
						<span class="pf-info-card-icon pf-icon-gold bi bi-infinity"></span>
						<div class="pf-info-card-body">
							<div class="pf-info-card-label">Grand Resets</div>
							<div class="pf-info-card-val pf-val-gold"><?php echo number_format($cData[11]); ?></div>
						</div>
					</div>
					<?php endif; ?>

					<div class="pf-info-card">
						<span class="pf-info-card-icon pf-icon-red bi bi-crosshair"></span>
						<div class="pf-info-card-body">
							<div class="pf-info-card-label">PK Kills</div>
							<div class="pf-info-card-val pf-val-red"><?php echo number_format($cData[10]); ?></div>
						</div>
					</div>

					<?php if(check_value($cData[12])): ?>
					<div class="pf-info-card">
						<span class="pf-info-card-icon pf-icon-gold bi bi-shield-fill"></span>
						<div class="pf-info-card-body">
							<div class="pf-info-card-label">Guild</div>
							<div class="pf-info-card-val pf-val-gold"><?php echo guildProfile($cData[12]); ?></div>
						</div>
					</div>
					<?php endif; ?>

				</div><!-- /.pf-info-grid -->

				<!-- Base Stats -->
				<div class="pf-section-title pf-section-title-sub">
					<i class="bi bi-bar-chart-fill"></i>
					Base Stats
				</div>

				<div class="pf-stats-table">
					<div class="pf-stats-row">
						<span class="pf-stats-name"><i class="bi bi-lightning-charge-fill"></i> Strength</span>
						<span class="pf-stats-bar-wrap"><span class="pf-stats-bar" style="width:<?php echo min(100, round($cData[5] / 32767 * 100)); ?>%"></span></span>
						<span class="pf-stats-num"><?php echo number_format($cData[5]); ?></span>
					</div>
					<div class="pf-stats-row">
						<span class="pf-stats-name"><i class="bi bi-wind"></i> Agility</span>
						<span class="pf-stats-bar-wrap"><span class="pf-stats-bar" style="width:<?php echo min(100, round($cData[6] / 32767 * 100)); ?>%"></span></span>
						<span class="pf-stats-num"><?php echo number_format($cData[6]); ?></span>
					</div>
					<div class="pf-stats-row">
						<span class="pf-stats-name"><i class="bi bi-heart-fill"></i> Vitality</span>
						<span class="pf-stats-bar-wrap"><span class="pf-stats-bar pf-bar-red" style="width:<?php echo min(100, round($cData[7] / 32767 * 100)); ?>%"></span></span>
						<span class="pf-stats-num"><?php echo number_format($cData[7]); ?></span>
					</div>
					<div class="pf-stats-row">
						<span class="pf-stats-name"><i class="bi bi-magic"></i> Energy</span>
						<span class="pf-stats-bar-wrap"><span class="pf-stats-bar pf-bar-blue" style="width:<?php echo min(100, round($cData[8] / 32767 * 100)); ?>%"></span></span>
						<span class="pf-stats-num"><?php echo number_format($cData[8]); ?></span>
					</div>
					<?php if($hasCmd): ?>
					<div class="pf-stats-row">
						<span class="pf-stats-name"><i class="bi bi-person-fill-up"></i> Command</span>
						<span class="pf-stats-bar-wrap"><span class="pf-stats-bar pf-bar-purple" style="width:<?php echo min(100, round($cData[9] / 32767 * 100)); ?>%"></span></span>
						<span class="pf-stats-num"><?php echo number_format($cData[9]); ?></span>
					</div>
					<?php endif; ?>
				</div><!-- /.pf-stats-table -->

			</div><!-- /.pf-panel-right -->

		</div><!-- /.pf-layout -->

	</div><!-- /.pf-layout-wrap -->

</div>

<?php

} catch(Exception $e) {
	inline_message('error', $e->getMessage());
}
