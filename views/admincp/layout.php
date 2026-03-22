<?php
/**
 * AdminCP shell layout view.
 *
 * Variables:
 * - array<int,array{title:string,icon:string,id:string,links:array<int,array{module:string,label:string,url:string}>}> $sidebarGroups
 * - string $currentModule
 * - string $admincpHomeUrl
 * - string $admincpModuleBaseUrl
 * - object $handler
 */

$admincpHomeUrl = (string) ($admincpHomeUrl ?? __PATH_ADMINCP_HOME__);
$admincpModuleBaseUrl = (string) ($admincpModuleBaseUrl ?? (__PATH_ADMINCP_HOME__ . '?module='));
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
    <link rel="stylesheet" href="<?php echo __PATH_ADMINCP_HOME__; ?>css/admin.css?v=<?php echo @filemtime(__PUBLIC_DIR__ . 'admincp/css/admin.css'); ?>">
    <link rel="shortcut icon" href="<?php echo __PATH_ADMINCP_HOME__; ?>favicon.ico" type="image/x-icon">
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

<div id="acp-page-transition"></div>
<div id="acp-progress-bar"></div>
<div class="acp-sidebar-backdrop" id="acp-backdrop"></div>

<nav class="acp-topbar">
    <div class="acp-topbar-left">
        <button class="acp-menu-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <a href="<?php echo htmlspecialchars($admincpHomeUrl, ENT_QUOTES, 'UTF-8'); ?>" class="acp-brand">
            Dark<span>Core</span>
            <small>Admin Panel</small>
        </a>
    </div>
    <div class="acp-topbar-right">
        <a href="<?php echo __BASE_URL__; ?>" target="_blank"><i class="bi bi-house-fill"></i> <span class="acp-topbar-label">Website</span></a>
        <a href="<?php echo __BASE_URL__; ?>logout/" class="acp-logout"><i class="bi bi-power"></i> <span class="acp-topbar-label">Log Out</span></a>
        <span class="acp-user"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars((string) $_SESSION['username']); ?></span>
    </div>
</nav>

<div class="acp-layout">
    <aside class="acp-sidebar" id="acp-sidebar">
        <nav class="acp-nav">
            <?php foreach ($sidebarGroups as $sidebarGroup):
                $groupModules = array_column($sidebarGroup['links'], 'module');
                $isActive = in_array($currentModule, $groupModules, true);
            ?>
            <div class="acp-nav-group <?php echo $isActive ? 'open' : ''; ?>">
                <button class="acp-nav-title" onclick="toggleMenu('<?php echo htmlspecialchars($sidebarGroup['id'], ENT_QUOTES, 'UTF-8'); ?>', this)">
                    <i class="bi <?php echo htmlspecialchars($sidebarGroup['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                    <span><?php echo htmlspecialchars($sidebarGroup['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <i class="bi bi-chevron-right acp-arrow"></i>
                </button>
                <div class="acp-nav-sub" id="<?php echo htmlspecialchars($sidebarGroup['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isActive ? 'style="display:block"' : ''; ?>>
                    <?php foreach ($sidebarGroup['links'] as $link):
                        $isSubActive = ($currentModule === $link['module']);
                    ?>
                    <a href="<?php echo htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8'); ?>" class="acp-nav-link <?php echo $isSubActive ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (isset($extra_admincp_sidebar) && is_array($extra_admincp_sidebar)): ?>
            <div class="acp-nav-group">
                <button class="acp-nav-title" onclick="toggleMenu('sm_plugins_active', this)">
                    <i class="bi bi-grid-fill"></i>
                    <span>Active Plugins</span>
                    <i class="bi bi-chevron-right acp-arrow"></i>
                </button>
                <div class="acp-nav-sub" id="sm_plugins_active">
                    <?php foreach ($extra_admincp_sidebar as $p):
                        if (!is_array($p) || !is_array($p[1])) {
                            continue;
                        }
                        foreach ($p[1] as $sub): ?>
                    <a href="<?php echo htmlspecialchars($admincpModuleBaseUrl . (string) $sub[1], ENT_QUOTES, 'UTF-8'); ?>" class="acp-nav-link"><?php echo $sub[0]; ?></a>
                    <?php endforeach; endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </nav>
    </aside>

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

    window.addEventListener('resize', function() {
        if (!isMobile()) {
            closeMobile();
            document.body.style.overflow = '';
        }
    });

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
    var newRegistrations = $('#new_registrations');
    if (newRegistrations.length) newRegistrations.DataTable(dtOpts);

    var blockedIps = $('#blocked_ips');
    if (blockedIps.length) blockedIps.DataTable(dtOpts);

    var paypalDonations = $('#paypal_donations');
    if (paypalDonations.length) {
        var paypalOpts = Object.assign({}, dtOpts, { searching: true, info: true });
        paypalDonations.DataTable(paypalOpts);
    }

    var superRewardsDonations = $('#superrewards_donations');
    if (superRewardsDonations.length) {
        var superRewardsOpts = Object.assign({}, dtOpts, { searching: true, info: true });
        superRewardsDonations.DataTable(superRewardsOpts);
    }

    var creditsLogs = $('#credits_logs');
    if (creditsLogs.length) {
        var creditsLogsOpts = Object.assign({}, dtOpts, { searching: true, info: true });
        creditsLogs.DataTable(creditsLogsOpts);
    }
});

(function() {
    var overlay  = document.getElementById('acp-page-transition');
    var progress = document.getElementById('acp-progress-bar');
    var leaving  = false;

    function startProgress() {
        progress.className = '';
        progress.style.width = '0';
        progress.offsetWidth;
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
        var el = e.target.closest('a[href]');
        if (!el) return;

        var href = el.getAttribute('href');
        if (!href) return;

        if (el.target === '_blank') return;
        if (href.startsWith('#')) return;
        if (href.startsWith('http') && !href.startsWith(window.location.origin)) return;
        if (href.indexOf('logout') !== -1) return;

        e.preventDefault();
        navigate(href);
    });

    overlay.classList.remove('fade-out');
})();
</script>
</body>
</html>


