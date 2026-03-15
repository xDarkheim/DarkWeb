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
	$weProfiles->setType("guild");
	$weProfiles->setRequest($_GET['req']);
	$guildData = $weProfiles->data();

	$allMembers     = array_values(array_filter(array_map('trim', explode(",", $guildData[5]))));
	$guildLogo      = returnGuildLogo($guildData[2], 80);
	$guildMaster    = trim($guildData[4]);
	$memberCount    = count($allMembers);
	$regularMembers = array_values(array_filter($allMembers, static function($m) use ($guildMaster) {
		return trim($m) !== $guildMaster;
	}));
?>

<div>


	<div class="pf-layout-wrap">

		<!-- BANNER -->
		<div class="pf-banner pf-banner-guild">
			<div class="pf-banner-overlay"></div>
			<div class="pf-banner-content">
				<div class="pf-guild-logo"><?php echo $guildLogo; ?></div>
				<div class="pf-identity">
					<h1 class="pf-name"><?php echo htmlspecialchars($guildData[1]); ?></h1>
					<div class="pf-guild-meta">
						<span class="pf-guild-meta-item">
							<span class="pf-guild-meta-label">Master</span>
							<span class="pf-guild-meta-val"><?php echo playerProfile($guildMaster); ?></span>
						</span>
						<span class="pf-guild-meta-sep">|</span>
						<span class="pf-guild-meta-item">
							<span class="pf-guild-meta-label">Members</span>
							<span class="pf-guild-meta-val"><?php echo $memberCount; ?></span>
						</span>
						<span class="pf-guild-meta-sep">|</span>
						<span class="pf-guild-meta-item">
							<span class="pf-guild-meta-label">Score</span>
							<span class="pf-guild-meta-val"><?php echo number_format($guildData[3]); ?></span>
						</span>
					</div>
				</div>
			</div>
		</div>

		<!-- BODY -->
		<div class="pf-layout">

			<!-- LEFT — sidebar like ucp-sidebar -->
			<div class="pf-panel-left">

				<div class="pf-panel-section">
					<div class="pf-panel-title">Guild Info</div>
					<div class="pf-stat-list">
						<div class="pf-stat-row">
							<span class="pf-stat-key">Name</span>
							<span class="pf-stat-val"><?php echo htmlspecialchars($guildData[1]); ?></span>
						</div>
						<div class="pf-stat-row">
							<span class="pf-stat-key">Score</span>
							<span class="pf-stat-val pf-val-gold"><?php echo number_format($guildData[3]); ?></span>
						</div>
						<div class="pf-stat-row">
							<span class="pf-stat-key">Members</span>
							<span class="pf-stat-val"><?php echo $memberCount; ?></span>
						</div>
						<div class="pf-stat-row">
							<span class="pf-stat-key">Alliance</span>
							<span class="pf-stat-val pf-val-muted">— <span class="pf-stub-tag">Soon</span></span>
						</div>
						<div class="pf-stat-row">
							<span class="pf-stat-key">Hostility</span>
							<span class="pf-stat-val pf-val-muted">— <span class="pf-stub-tag">Soon</span></span>
						</div>
						<div class="pf-stat-row">
							<span class="pf-stat-key">Castle</span>
							<span class="pf-stat-val pf-val-muted">None <span class="pf-stub-tag">Soon</span></span>
						</div>
					</div>
				</div>

				<div class="pf-panel-section">
					<div class="pf-panel-title">Guild Master</div>
					<div class="pf-guild-master-block">
						<span class="pf-gm-crown">👑</span>
						<span class="pf-gm-name"><?php echo playerProfile($guildMaster); ?></span>
						<span class="pf-gm-badge">Master</span>
					</div>
				</div>

				<div class="pf-panel-section">
					<div class="pf-panel-title">Guild Notice</div>
					<div class="pf-stub-notice">
						<span>📋</span>
						<span>No notice has been set by the guild master.</span>
					</div>
				</div>

			</div><!-- /.pf-panel-left -->

			<!-- RIGHT — main like ucp-main -->
			<div class="pf-panel-right">

				<div class="pf-section-title">
					Members
					<span class="pf-panel-title-count"><?php echo count($regularMembers); ?></span>
				</div>

				<?php if(count($regularMembers) > 0): ?>
				<div class="pf-members-grid">
					<?php foreach($regularMembers as $i => $memberName): ?>
					<div class="pf-member-item">
						<span class="pf-member-num"><?php echo str_pad($i+1, 2, '0', STR_PAD_LEFT); ?></span>
						<span class="pf-member-name"><?php echo playerProfile(trim($memberName)); ?></span>
						<span class="pf-member-role">Member</span>
					</div>
					<?php endforeach; ?>
				</div>
				<?php else: ?>
				<div class="pf-stub-notice">
					<span>👥</span>
					<span>No additional members in this guild.</span>
				</div>
				<?php endif; ?>

			</div><!-- /.pf-panel-right -->

		</div><!-- /.pf-layout -->

	</div><!-- /.pf-layout-wrap -->

</div>

<?php

} catch(Exception $e) {
	inline_message('error', $e->getMessage());
}
