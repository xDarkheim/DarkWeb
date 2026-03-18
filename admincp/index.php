<?php
// access
define('access', 'admincp');

try {
	if(!@include('../includes/bootstrap/boot.php')) {
		throw new RuntimeException('Could not load CMS.');
	}
	if(!isLoggedIn()) { redirect(); }
	if(!canAccessAdminCP($_SESSION['username'])) { redirect(); }
	if(!@include(__PATH_ADMINCP_INC__ . 'functions.php')) {
		throw new RuntimeException('Could not load AdminCP functions.');
	}
	if(!@include(__PATH_ADMINCP_INC__ . 'check.php')) {
		throw new RuntimeException('Could not load AdminCP configuration check.');
	}
} catch (Exception $ex) {
	$errorPage = file_get_contents('../includes/error.html');
	echo str_replace("{ERROR_MESSAGE}", $ex->getMessage(), $errorPage);
	die();
}

$admincpSidebar = array(
	array("News Management", array(
		"addnews" => "Publish",
		"managenews" => "Edit / Delete",
	), "bi-newspaper"),
	array("Account", array(
		"searchaccount" => "Search",
		"accountsfromip" => "Find Accounts from IP",
		"onlineaccounts" => "Online Accounts",
		"newregistrations" => "New Registrations",
		"accountinfo" => "",
	), "bi-people-fill"),
	array("Character", array(
		"searchcharacter" => "Search",
		"editcharacter" => "",
	), "bi-person-fill"),
	array("Bans", array(
		"searchban" => "Search",
		"banaccount" => "Ban Account",
		"latestbans" => "Latest Bans",
		"blockedips" => "Block IP (web)",
	), "bi-slash-circle-fill"),
	array("Credits", array(
		"creditsconfigs" => "Credit Configurations",
		"creditsmanager" => "Credit Manager",
		"latestpaypal" => "PayPal Donations",
		"topvotes" => "Top Voters",
	), "bi-cash-coin"),
	array("Website Configuration", array(
		"admincp_access" => "AdminCP Access",
		"connection_settings" => "Connection Settings",
		"website_settings" => "Website Settings",
		"modules_manager" => "Modules Manager",
		"navbar" => "Navigation Menu",
		"usercp" => "UserCP Menu",
	), "bi-toggles"),
	array("Tools", array(
		"cachemanager" => "Cache Manager",
		"cronmanager" => "Cron Job Manager",
	), "bi-wrench-adjustable"),
	array("Languages", array(
		"phrases" => "Phrase List",
	), "bi-translate"),
	array("Plugins", array(
		"plugins" => "Plugins Manager",
		"plugin_install" => "Import Plugin",
	), "bi-plug-fill"),
);

$currentModule = $_REQUEST['module'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>AdminCP</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
	<link rel="stylesheet" href="<?php echo __PATH_ADMINCP_HOME__; ?>css/admin.css?v=<?php echo @filemtime(__DIR__.'/css/admin.css'); ?>">
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	<script>
	function toggleMenu(id, btn) {
		const sub = document.getElementById(id);
		if (!sub) return;
		const group = btn.closest('.acp-nav-group');
		const isOpen = sub.style.display === 'block';
		sub.style.display = isOpen ? 'none' : 'block';
		if (group) group.classList.toggle('open', !isOpen);
	}
	function switchTab(id, btn) {
		const wrap = btn.closest('.acp-tabs-wrap');
		wrap.querySelectorAll('.acp-tab-content').forEach(function(el){ el.style.display='none'; el.classList.remove('active'); });
		wrap.querySelectorAll('.acp-tab').forEach(function(el){ el.classList.remove('active'); });
		document.getElementById(id).style.display = 'block';
		document.getElementById(id).classList.add('active');
		btn.classList.add('active');
	}
	</script>
</head>
<body>

<!-- Page transition overlay -->
<div id="acp-page-transition"></div>
<div id="acp-progress-bar"></div>

<!-- Mobile sidebar backdrop -->
<div class="acp-sidebar-backdrop" id="acp-backdrop"></div>

<!-- Top navbar -->
<nav class="acp-topbar">
	<div class="acp-topbar-left">
		<button class="acp-menu-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
		<a href="<?php echo admincp_base(); ?>" class="acp-brand">
                  Dark<span>Core</span>
			<small>Admin Panel</small>
		</a>
	</div>
	<div class="acp-topbar-right">
		<a href="<?php echo __BASE_URL__; ?>" target="_blank"><i class="bi bi-house-fill"></i> <span class="acp-topbar-label">Website</span></a>
		<a href="<?php echo __BASE_URL__; ?>logout/" class="acp-logout"><i class="bi bi-power"></i> <span class="acp-topbar-label">Log Out</span></a>
		<span class="acp-user"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></span>
	</div>
</nav>

<div class="acp-layout">

	<!-- Sidebar -->
	<aside class="acp-sidebar" id="acp-sidebar">
		<nav class="acp-nav">
			<?php foreach($admincpSidebar as $sidebarItem):
				$isActive = isset($_GET['module']) && array_key_exists($_GET['module'], $sidebarItem[1]);
				$uid = 'sm_' . preg_replace('/\W/', '', $sidebarItem[0]);
			?>
			<div class="acp-nav-group <?php echo $isActive ? 'open' : ''; ?>">
				<button class="acp-nav-title" onclick="toggleMenu('<?php echo $uid; ?>', this)">
					<i class="bi <?php echo $sidebarItem[2]; ?>"></i>
					<span><?php echo $sidebarItem[0]; ?></span>
					<i class="bi bi-chevron-right acp-arrow"></i>
				</button>
				<div class="acp-nav-sub" id="<?php echo $uid; ?>" <?php echo $isActive ? 'style="display:block"' : ''; ?>>
					<?php foreach($sidebarItem[1] as $mod => $title):
						if(!check_value($title)) {
							continue;
						}
						$isSubActive = ($currentModule === $mod);
					?>
					<a href="<?php echo admincp_base($mod); ?>" class="acp-nav-link <?php echo $isSubActive ? 'active' : ''; ?>">
						<?php echo $title; ?>
					</a>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endforeach; ?>

			<?php if(isset($extra_admincp_sidebar) && is_array($extra_admincp_sidebar)): ?>
			<div class="acp-nav-group">
				<button class="acp-nav-title" onclick="toggleMenu('sm_plugins_active', this)">
					<i class="bi bi-grid-fill"></i>
					<span>Active Plugins</span>
					<i class="bi bi-chevron-right acp-arrow"></i>
				</button>
				<div class="acp-nav-sub" id="sm_plugins_active">
					<?php foreach($extra_admincp_sidebar as $p):
						if(!is_array($p) || !is_array($p[1])) {
							continue;
						}
						foreach($p[1] as $sub):
					?>
					<a href="<?php echo admincp_base($sub[1]); ?>" class="acp-nav-link"><?php echo $sub[0]; ?></a>
					<?php endforeach; endforeach; ?>
				</div>
			</div>
			<?php endif; ?>
		</nav>
	</aside>

	<!-- Main content -->
	<main class="acp-main">
		<?php $handler->loadAdminCPModule($currentModule); ?>
	</main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo __PATH_ADMINCP_HOME__; ?>js/toast.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tinymce@7.7.0/tinymce.min.js"></script>
<script>
(function() {
	const sidebar = document.getElementById('acp-sidebar');
	const toggleBtn = document.getElementById('sidebarToggle');
	const backdrop = document.getElementById('acp-backdrop');
	const MOBILE_BP = 768;

	function isMobile() { return window.innerWidth <= MOBILE_BP; }

	function closeMobile() {
		sidebar.classList.remove('mobile-open');
		backdrop.classList.remove('active');
		document.body.style.overflow = '';
	}

	toggleBtn.addEventListener('click', function() {
		if (isMobile()) {
			const open = sidebar.classList.toggle('mobile-open');
			backdrop.classList.toggle('active', open);
			document.body.style.overflow = open ? 'hidden' : '';
		} else {
			sidebar.classList.toggle('collapsed');
		}
	});

	backdrop.addEventListener('click', closeMobile);

	// Close on resize if switching to desktop
	window.addEventListener('resize', function() {
		if (!isMobile()) {
			closeMobile();
			document.body.style.overflow = '';
		}
	});

	// Close sidebar when a nav link clicked on mobile
	document.querySelectorAll('.acp-nav-link').forEach(function(link) {
		link.addEventListener('click', function() {
			if (isMobile()) closeMobile();
		});
	});
})();

$(document).ready(function() {
	const dtOpts = {
		searching: false,
		ordering: false,
		lengthChange: false,
		pageLength: 10,
		info: false
	};
	if ($('#new_registrations').length)        $('#new_registrations').DataTable(dtOpts);
	if ($('#blocked_ips').length)              $('#blocked_ips').DataTable(dtOpts);
	if ($('#paypal_donations').length)         $('#paypal_donations').DataTable($.extend({}, dtOpts, { searching: true, info: true }));
	if ($('#superrewards_donations').length)   $('#superrewards_donations').DataTable($.extend({}, dtOpts, { searching: true, info: true }));
	if ($('#credits_logs').length)             $('#credits_logs').DataTable($.extend({}, dtOpts, { searching: true, info: true }));
});
// Page transition — intercept internal AdminCP links
(function() {
	var overlay  = document.getElementById('acp-page-transition');
	var progress = document.getElementById('acp-progress-bar');
	var leaving  = false;

	function startProgress() {
		progress.className = '';
		progress.style.width = '0';
		// Force reflow so transition fires
		progress.offsetWidth; // eslint-disable-line
		progress.classList.add('running');
	}

	function finishProgress() {
		progress.classList.remove('running');
		progress.classList.add('done');
	}

	function navigate(href) {
		if (leaving) return;
		leaving = true;

		startProgress();
		overlay.classList.add('fade-out');

		setTimeout(function() {
			finishProgress();
			window.location.href = href;
		}, 200);
	}

	document.addEventListener('click', function(e) {
		// Find closest anchor
		var el = e.target.closest('a[href]');
		if (!el) return;

		var href = el.getAttribute('href');
		if (!href) return;

		// Skip: external links, hash-only, target=_blank, non-admincp
		if (el.target === '_blank') return;
		if (href.startsWith('#')) return;
		if (href.startsWith('http') && !href.startsWith(window.location.origin)) return;
		// Skip logout (let it navigate immediately)
		if (href.indexOf('logout') !== -1) return;

		e.preventDefault();
		navigate(href);
	});

	// Fade-in on load: reset overlay immediately
	overlay.classList.remove('fade-out');
})();
</script>
</body>
</html>
