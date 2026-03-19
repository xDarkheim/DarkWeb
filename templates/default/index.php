<?php
/**
 * DarkCore CMS
 * 
 * 
 * @version 0.0.1
 * @author Dmytro Hovenko <dmytro.hovenko@gmail.com>
 * @copyright (c) 2026 DarkCore CMS. All Rights Reserved.
 * 
 * Licensed under the MIT license
 * http://opensource.org/licenses/MIT
 */

if(!defined('access') or !access) die();
include('inc/template.functions.php');

$serverInfoCache = LoadCacheData('server_info.cache');
if(is_array($serverInfoCache)) {
	$srvInfo = explode("|", $serverInfoCache[1][0]);
}

$maxOnline = config('maximum_online', true);
$onlinePlayers = isset($srvInfo[3]) ? $srvInfo[3] : 0;
$onlinePlayersPercent = check_value($maxOnline) ? $onlinePlayers*100/$maxOnline : 0;

if(!isset($_REQUEST['page'])) {
	$_REQUEST['page'] = '';
}

if(!isset($_REQUEST['subpage'])) {
	$_REQUEST['subpage'] = '';
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		<?php
		$_seo_title       = htmlspecialchars(config('website_title', true),            ENT_QUOTES, 'UTF-8');
		$_seo_description = htmlspecialchars(config('website_meta_description', true), ENT_QUOTES, 'UTF-8');
		$_seo_keywords    = htmlspecialchars(config('website_meta_keywords', true),    ENT_QUOTES, 'UTF-8');
		$_seo_sitename    = htmlspecialchars(config('server_name', true),              ENT_QUOTES, 'UTF-8');
		$_seo_url         = htmlspecialchars(__BASE_URL__,                             ENT_QUOTES, 'UTF-8');
		$_seo_image       = htmlspecialchars(__PATH_IMG__ . 'brand.jpg',               ENT_QUOTES, 'UTF-8');
		?>
		<title><?php echo $_seo_title; ?></title>

		<!-- SEO -->
		<meta name="description"        content="<?php echo $_seo_description; ?>"/>
		<meta name="keywords"           content="<?php echo $_seo_keywords; ?>"/>
		<meta name="robots"             content="index, follow"/>
		<link rel="canonical"           href="<?php echo $_seo_url; ?>"/>

		<!-- Open Graph -->
		<meta property="og:type"        content="website"/>
		<meta property="og:locale"      content="en_US"/>
		<meta property="og:site_name"   content="<?php echo $_seo_sitename; ?>"/>
		<meta property="og:title"       content="<?php echo $_seo_title; ?>"/>
		<meta property="og:description" content="<?php echo $_seo_description; ?>"/>
		<meta property="og:url"         content="<?php echo $_seo_url; ?>"/>
		<meta property="og:image"       content="<?php echo $_seo_image; ?>"/>
		<meta property="og:image:width"  content="1200"/>
		<meta property="og:image:height" content="630"/>

		<!-- Twitter Card -->
		<meta name="twitter:card"        content="summary_large_image"/>
		<meta name="twitter:title"       content="<?php echo $_seo_title; ?>"/>
		<meta name="twitter:description" content="<?php echo $_seo_description; ?>"/>
		<meta name="twitter:image"       content="<?php echo $_seo_image; ?>"/>
		<link rel="shortcut icon" href="<?php echo __PATH_TEMPLATE__; ?>favicon.ico"/>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
		<link href="https://fonts.googleapis.com/css?family=PT+Sans:400,400i,700" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css?family=Cinzel" rel="stylesheet">

		<link href="<?php echo __PATH_TEMPLATE_CSS__; ?>style.css?v=<?php echo filemtime(__PATH_TEMPLATE_ROOT__.'css/style.css'); ?>" rel="stylesheet" media="screen">
		<link href="<?php echo __PATH_TEMPLATE_CSS__; ?>profiles.css?v=<?php echo filemtime(__PATH_TEMPLATE_ROOT__.'css/profiles.css'); ?>" rel="stylesheet" media="screen">
		<link href="<?php echo __PATH_TEMPLATE_CSS__; ?>castle-siege.css?v=<?php echo filemtime(__PATH_TEMPLATE_ROOT__.'css/castle-siege.css'); ?>" rel="stylesheet" media="screen">
		<?php
		$_assetsCss = __ROOT_DIR__.'assets/css/';
		$_assetsUrl = __PATH_ASSETS_CSS__;
		$_cssFiles  = ['variables','toast','auth','ucp','myaccount','profiles','info','tos','news','rankings','panels','paypal','downloads','castlesiege'];
		foreach($_cssFiles as $_f):
		    $_path = $_assetsCss.$_f.'.css';
		    if(!file_exists($_path)) continue;
		?>
		<link href="<?php echo $_assetsUrl.$_f; ?>.css?v=<?php echo filemtime($_path); ?>" rel="stylesheet" media="screen">
		<?php endforeach; ?>
		<link href="<?php echo __PATH_TEMPLATE_CSS__; ?>override.css?v=<?php echo filemtime(__PATH_TEMPLATE_ROOT__.'css/override.css'); ?>" rel="stylesheet" media="screen">
		<script>
			var baseUrl = '<?php echo __BASE_URL__; ?>';
		</script>
		<script>
		(function() {
		    var theme = localStorage.getItem('site-theme');
		    if (theme === 'dark') {
		        document.documentElement.classList.add('dark-mode');
		    }
		})();
		</script>
	</head>
	<body>
		<div class="global-top-bar">
			<div class="global-top-bar-content">
				<div class="row">
					<div class="col-xs-6 text-left global-top-bar-nopadding">
					<?php if(config('language_switch_active',true)) templateLanguageSelector(); ?>
					</div>
					<div class="col-xs-6 text-right global-top-bar-nopadding">
					<?php if(isLoggedIn()) { ?>
						<a href="<?php echo __BASE_URL__; ?>usercp/"><?php echo lang('module_titles_txt_3'); ?></a>
						<?php if(canAccessAdminCP($_SESSION['username'] ?? '')): ?>
						<span class="global-top-bar-separator">|</span>
						<a href="<?php echo __BASE_URL__; ?>admincp/" class="global-top-bar-admincp"><i class="bi bi-shield-fill"></i> AdminCP</a>
						<?php endif; ?>
						<span class="global-top-bar-separator">|</span>
						<a href="<?php echo __BASE_URL__; ?>logout/" class="logout"><?php echo lang('menu_txt_6'); ?></a>
					<?php } else { ?>
						<a href="<?php echo __BASE_URL__; ?>register/"><?php echo lang('menu_txt_3'); ?></a>
						<span class="global-top-bar-separator">|</span>
						<a href="<?php echo __BASE_URL__; ?>login/"><?php echo lang('menu_txt_4'); ?></a>
					<?php } ?>
					<span class="global-top-bar-separator">|</span>
					<button id="theme-toggle" class="theme-toggle" type="button" aria-label="Cambiar modo oscuro">
						<i class="bi bi-moon-stars-fill theme-icon theme-icon-dark"></i>
						<i class="bi bi-sun-fill theme-icon theme-icon-light"></i>
					</button>
					</div>
				</div>
			</div>
		</div>
		<div id="navbar">
			<button id="menu-toggle" type="button" aria-controls="main-nav" aria-expanded="false" aria-label="Toggle navigation menu">
        		<i class="bi bi-list"></i>
    		</button>
			<div id="main-nav">
			<?php templateBuildNavbar(); ?>
			</div>
		</div>
		<div id="header">
			<a href="<?php echo __BASE_URL__; ?>">
				<img class="dh-logo" src="<?php echo __PATH_TEMPLATE_IMG__; ?>logo.png" title="<?php config('server_name'); ?>"/>
			</a>
		</div>
		<div class="header-info-container">
		<div class="header-info">
			<div class="row">
				<div class="col-xs-12">
					<div class="col-xs-12 header-info-block">
						<?php if(check_value(config('maximum_online', true))) { ?>
						<div class="row">
							<div class="col-xs-6 text-left">
								<?php echo lang('sidebar_srvinfo_txt_5'); ?>:
							</div>
							<div class="col-xs-6 text-right online-count">
								<?php echo number_format($onlinePlayers); ?>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="dh-online-bar">
									<div class="dh-online-bar-progress" style="width:<?php echo $onlinePlayersPercent; ?>%;"></div>
								</div>
							</div>
						</div>
						<?php } ?>
						<div class="row">
							<div class="col-xs-6 text-left">
								<?php echo lang('server_time'); ?>:
							</div>
							<div class="col-xs-6 text-right">
								<time id="tServerTime">&nbsp;</time> <span id="tServerDate">&nbsp;</span>
							</div>
							
							<div class="col-xs-6 text-left">
								<?php echo lang('user_time'); ?>:
							</div>
							<div class="col-xs-6 text-right">
								<time id="tLocalTime">&nbsp;</time> <span id="tLocalDate">&nbsp;</span>
							</div>
						</div>
						
					</div>
				</div>
			</div>
		</div>
		</div>
		<div id="container">
			<div id="content">
				<?php if($_REQUEST['page'] == 'usercp' && $_REQUEST['subpage'] != '') { ?>
				<div class="col-xs-8">
					<?php $handler->loadModule($_REQUEST['page'],$_REQUEST['subpage']); ?>
				</div>
				<div class="col-xs-4">
					<?php include(__PATH_TEMPLATE_ROOT__ . 'inc/modules/sidebar.php'); ?>
				</div>
				<?php } else { ?>
				<div class="col-xs-12">
					<?php $handler->loadModule($_REQUEST['page'],$_REQUEST['subpage']); ?>
				</div>
				<?php } ?>
			</div>
		</div>
		<footer class="footer">
			<?php include(__PATH_TEMPLATE_ROOT__ . 'inc/modules/footer.php'); ?>
		</footer>
		<script>
			(function() {
				var toggle = document.getElementById("menu-toggle");
				var navbar = document.getElementById("navbar");
				var MOBILE_BP = 768;
				if (!toggle || !navbar) return;

				function setMenuState(open) {
					navbar.classList.toggle("active", open);
					toggle.setAttribute("aria-expanded", open ? "true" : "false");
					var icon = toggle.querySelector("i");
					if (icon) icon.className = open ? "bi bi-x-lg" : "bi bi-list";
				}

				function closeMenu() {
					setMenuState(false);
				}

				toggle.addEventListener("click", function (e) {
					e.stopPropagation();
					setMenuState(!navbar.classList.contains("active"));
				});

				document.addEventListener("click", function (e) {
					if (navbar.classList.contains("active") && !navbar.contains(e.target)) {
						closeMenu();
					}
				});

				document.addEventListener("keydown", function (e) {
					if (e.key === "Escape") {
						closeMenu();
					}
				});

				window.addEventListener("resize", function () {
					if (window.innerWidth > MOBILE_BP) {
						closeMenu();
					}
				});

				navbar.querySelectorAll("a").forEach(function (link) {
					link.addEventListener("click", function () {
						if (window.innerWidth <= MOBILE_BP) {
							closeMenu();
						}
					});
				});
			})();
		</script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
		<script src="<?php echo __PATH_TEMPLATE_JS__; ?>main.js?v=<?php echo filemtime(__PATH_TEMPLATE_ROOT__.'js/main.js'); ?>"></script>
		<script src="<?php echo __PATH_TEMPLATE_JS__; ?>events.js?v=<?php echo filemtime(__PATH_TEMPLATE_ROOT__.'js/events.js'); ?>"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
		<script src="<?php echo __PATH_ASSETS_JS__; ?>components.js?v=<?php echo file_exists(__ROOT_DIR__.'assets/js/components.js') ? filemtime(__ROOT_DIR__.'assets/js/components.js') : 1; ?>"></script>
	</body>
</html>