<?php
/**
 * DarkCore CMS
 *
 *
 * @version 1.1.1
 * @author Dmytro Hovenko <dmytro.hovenko@gmail.com>
 * @copyright (c) 2026 DarkCore CMS. All Rights Reserved.
 *
 * Licensed under the MIT license
 * http://opensource.org/licenses/MIT
 */

if (! defined('access') or ! access) {
    die();
}

$sidebarData     = is_array($themeLayout['sidebarData'] ?? null) ? $themeLayout['sidebarData'] : [];
$footerData      = is_array($themeLayout['footerData'] ?? null) ? $themeLayout['footerData'] : [];
$stylesheetHrefs = is_array($themeLayout['stylesheetHrefs'] ?? null) ? $themeLayout['stylesheetHrefs'] : [];
?>
<!DOCTYPE html>
	<html lang="<?php echo $themeLayout['htmlLang'] ?? 'en'; ?>">
	<head>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		<title><?php echo $themeLayout['seoTitle'] ?? ''; ?></title>

		<!-- SEO -->
		<meta name="description"        content="<?php echo $themeLayout['seoDescription'] ?? ''; ?>"/>
		<meta name="keywords"           content="<?php echo $themeLayout['seoKeywords']    ?? ''; ?>"/>
		<meta name="robots"             content="index, follow"/>
		<link rel="canonical"           href="<?php echo $themeLayout['seoUrl'] ?? ''; ?>"/>

		<!-- Open Graph -->
		<meta property="og:type"        content="website"/>
		<meta property="og:locale"      content="en_US"/>
		<meta property="og:site_name"   content="<?php echo $themeLayout['seoSiteName']    ?? ''; ?>"/>
		<meta property="og:title"       content="<?php echo $themeLayout['seoTitle']       ?? ''; ?>"/>
		<meta property="og:description" content="<?php echo $themeLayout['seoDescription'] ?? ''; ?>"/>
		<meta property="og:url"         content="<?php echo $themeLayout['seoUrl']         ?? ''; ?>"/>
		<meta property="og:image"       content="<?php echo $themeLayout['seoImage']       ?? ''; ?>"/>
		<meta property="og:image:width"  content="1200"/>
		<meta property="og:image:height" content="630"/>

		<!-- Twitter Card -->
		<meta name="twitter:card"        content="summary_large_image"/>
		<meta name="twitter:title"       content="<?php echo $themeLayout['seoTitle']       ?? ''; ?>"/>
		<meta name="twitter:description" content="<?php echo $themeLayout['seoDescription'] ?? ''; ?>"/>
		<meta name="twitter:image"       content="<?php echo $themeLayout['seoImage']       ?? ''; ?>"/>
		<link rel="shortcut icon" href="<?php echo __PATH_THEME__; ?>favicon.ico"/>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
		<link href="https://fonts.googleapis.com/css?family=PT+Sans:400,400i,700" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css?family=Cinzel" rel="stylesheet">
		<?php foreach ($stylesheetHrefs as $stylesheetHref): ?>
		<link href="<?php echo htmlspecialchars((string) $stylesheetHref, ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet" media="screen">
		<?php endforeach; ?>
		<script>
			var baseUrl = '<?php echo htmlspecialchars((string) ($themeLayout['relativeRoot'] ?? __RELATIVE_ROOT__), ENT_QUOTES, 'UTF-8'); ?>';
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
					<?php if (! empty($themeLayout['showLanguageSwitcher'])) {
					    echo $themeLayout['languageSwitcherHtml'] ?? '';
					} ?>
					</div>
					<div class="col-xs-6 text-right global-top-bar-nopadding">
					<?php if (! empty($themeLayout['topBarIsLoggedIn'])) { ?>
						<a href="<?php echo htmlspecialchars((string) ($themeLayout['usercpUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) ($themeLayout['topBarUsercpLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></a>
						<?php if (! empty($themeLayout['topBarShowAdmincp'])): ?>
						<span class="global-top-bar-separator">|</span>
						<a href="<?php echo htmlspecialchars((string) ($themeLayout['admincpUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="global-top-bar-admincp"><i class="bi bi-shield-fill"></i> AdminCP</a>
						<?php endif; ?>
						<span class="global-top-bar-separator">|</span>
						<a href="<?php echo htmlspecialchars((string) ($themeLayout['logoutUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="logout"><?php echo htmlspecialchars((string) ($themeLayout['topBarLogoutLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></a>
					<?php } else { ?>
						<a href="<?php echo htmlspecialchars((string) ($themeLayout['registerUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) ($themeLayout['topBarRegisterLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></a>
						<span class="global-top-bar-separator">|</span>
						<a href="<?php echo htmlspecialchars((string) ($themeLayout['loginUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) ($themeLayout['topBarLoginLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></a>
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
			<?php echo $themeLayout['navbarHtml'] ?? ''; ?>
			</div>
		</div>
		<div id="header">
			<a href="<?php echo htmlspecialchars((string) ($themeLayout['brandHomeUrl'] ?? __BASE_URL__), ENT_QUOTES, 'UTF-8'); ?>">
				<img class="dh-logo" src="<?php echo htmlspecialchars((string) ($themeLayout['brandLogoUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo $themeLayout['brandTitleAttr'] ?? ''; ?>" alt="<?php echo $themeLayout['brandAlt'] ?? ''; ?>"/>
			</a>
		</div>
		<div class="header-info-container">
		<div class="header-info">
			<div class="row">
				<div class="col-xs-12">
					<div class="col-xs-12 header-info-block">
						<?php if (! empty($themeLayout['showOnlineCounter'])) { ?>
						<div class="row">
							<div class="col-xs-6 text-left">
								<?php echo htmlspecialchars((string) ($themeLayout['onlineLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>:
							</div>
							<div class="col-xs-6 text-right online-count">
								<?php echo htmlspecialchars((string) ($themeLayout['onlinePlayersFormatted'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="dh-online-bar">
									<div class="dh-online-bar-progress" style="width:<?php echo htmlspecialchars((string) ($themeLayout['onlinePlayersPercent'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>%;"></div>
								</div>
							</div>
						</div>
						<?php } ?>
						<div class="row">
							<div class="col-xs-6 text-left">
								<?php echo htmlspecialchars((string) ($themeLayout['serverTimeLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>:
							</div>
							<div class="col-xs-6 text-right">
								<time id="tServerTime">&nbsp;</time> <span id="tServerDate">&nbsp;</span>
							</div>
							
							<div class="col-xs-6 text-left">
								<?php echo htmlspecialchars((string) ($themeLayout['userTimeLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>:
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
				<?php if (! empty($themeLayout['showSidebarLayout'])) { ?>
				<div class="<?php echo htmlspecialchars((string) ($themeLayout['moduleColumnClass'] ?? 'col-xs-8'), ENT_QUOTES, 'UTF-8'); ?>">
					<?php echo $moduleHtml ?? ''; ?>
				</div>
				<div class="<?php echo htmlspecialchars((string) ($themeLayout['sidebarColumnClass'] ?? 'col-xs-4'), ENT_QUOTES, 'UTF-8'); ?>">
					<?php include(__PATH_THEME_ROOT__ . 'inc/modules/sidebar.php'); ?>
				</div>
				<?php } else { ?>
				<div class="<?php echo htmlspecialchars((string) ($themeLayout['moduleColumnClass'] ?? 'col-xs-12'), ENT_QUOTES, 'UTF-8'); ?>">
					<?php echo $moduleHtml ?? ''; ?>
				</div>
				<?php } ?>
			</div>
		</div>
		<footer class="footer">
			<?php include(__PATH_THEME_ROOT__ . 'inc/modules/footer.php'); ?>
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
		<script src="<?php echo htmlspecialchars((string) ($themeLayout['mainJsUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></script>
		<script src="<?php echo htmlspecialchars((string) ($themeLayout['eventsJsUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
		<script src="<?php echo htmlspecialchars((string) ($themeLayout['componentsJsUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></script>
	</body>
</html>
