<?php
/**
 * DarkWeb
 *
 * @version 0.0.1
 * @author      Dmytro Hovenko <dmytro.hovenko@gmail.com>
 */

define('access', 'install');
if(!@include_once('loader.php')) die('Could not load DarkWeb Installer.');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DarkWeb <?php echo INSTALLER_VERSION; ?> — Installer</title>
    <link rel="icon" type="image/x-icon" href="<?php echo __BASE_URL__; ?>templates/default/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
    /* ── Root variables (same as admin.css) ── */
    :root {
        --accent:     #c8a96e;
        --accent2:    #e0c080;
        --bg:         #111111;
        --bg2:        #161616;
        --bg3:        #1c1c1c;
        --border:     #222222;
        --border2:    #2e2e2e;
        --text:       #cccccc;
        --text-muted: #666666;
        --green:      #4caf50;
        --red:        #ef5350;
        --blue:       #42a5f5;
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
        background: var(--bg);
        color: var(--text);
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        font-size: 14px;
        min-height: 100vh;
        --bs-body-bg: #111111;
        --bs-body-color: #cccccc;
        --bs-border-color: #222222;
    }

    a { color: var(--accent); text-decoration: none; }
    a:hover { color: var(--accent2); }
    hr { border-color: var(--border); opacity: 1; }
    code { background: var(--bg3); color: var(--accent); padding: 2px 6px; border-radius: 3px; font-size: 12px; }
    strong { color: #ddd; }

    /* ── Scrollbar ── */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #0d0d0d; }
    ::-webkit-scrollbar-thumb { background: #2a2a2a; border-radius: 4px; }

    /* ── Top bar ── */
    .inst-topbar {
        position: fixed; top: 0; left: 0; right: 0; height: 52px;
        background: #0d0d0d; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; padding: 0 24px;
        z-index: 100;
    }
    .inst-brand {
        font-size: 17px; font-weight: 800; letter-spacing: 1px;
        color: #fff !important; text-transform: uppercase;
        text-decoration: none !important;
    }
    .inst-brand span { color: var(--accent); }
    .inst-brand small {
        font-size: 9px; font-weight: 400; letter-spacing: 3px;
        color: #444; text-transform: uppercase; margin-left: 6px;
    }

    /* ── Layout ── */
    .inst-layout {
        display: flex; margin-top: 52px;
        min-height: calc(100vh - 52px);
    }

    /* ── Sidebar ── */
    .inst-sidebar {
        width: 210px; flex-shrink: 0;
        background: #0d0d0d; border-right: 1px solid var(--border);
        padding: 24px 0 20px;
    }
    .inst-sidebar-title {
        font-size: 10px; font-weight: 700; letter-spacing: 2px;
        color: #444; text-transform: uppercase;
        padding: 0 18px 10px;
    }
    .inst-step-item {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 18px; font-size: 13px; color: #555;
        border-left: 2px solid transparent;
        transition: background .15s, color .15s;
    }
    .inst-step-item.done   { color: #556; }
    .inst-step-item.done .inst-step-num { background: #1a3a1a; color: var(--green); }
    .inst-step-item.active {
        color: var(--accent); background: #130f00;
        border-left-color: var(--accent);
    }
    .inst-step-item.active .inst-step-num { background: #2a1e00; color: var(--accent); }
    .inst-step-num {
        width: 22px; height: 22px; border-radius: 50%;
        background: #1a1a1a; color: #444;
        font-size: 11px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }

    /* ── Main ── */
    .inst-main {
        flex: 1; padding: 32px 36px;
        background: var(--bg); overflow-x: auto;
    }

    /* ── Card ── */
    .inst-card {
        background: var(--bg2); border: 1px solid var(--border);
        border-radius: 7px; overflow: hidden; margin-bottom: 20px;
    }
    .inst-card-header {
        background: #1a1a1a; border-bottom: 1px solid var(--border);
        color: var(--accent); font-size: 11px; font-weight: 700;
        letter-spacing: 1.5px; text-transform: uppercase; padding: 10px 16px;
    }
    .inst-card-body { padding: 18px; }

    /* ── Page title ── */
    h3.inst-title {
        color: var(--accent); font-size: 18px; font-weight: 700;
        border-bottom: 1px solid var(--border);
        padding-bottom: 12px; margin-bottom: 22px; margin-top: 0;
    }

    /* ── Forms ── */
    .form-label, label, .col-form-label { color: #aaa !important; font-size: 13px; }
    .form-control, .form-select,
    input[type=text], input[type=password],
    input[type=email], input[type=number], select, textarea {
        background: var(--bg3) !important;
        border: 1px solid var(--border2) !important;
        color: #d0d0d0 !important; border-radius: 5px; font-size: 13px;
    }
    .form-control:focus {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 2px rgba(200,169,110,.15) !important;
        outline: none;
    }
    .form-text, .form-text.text-muted { color: var(--text-muted) !important; font-size: 12px; }
    .form-check-input {
        background-color: var(--bg3) !important;
        border-color: var(--border2) !important;
    }
    .form-check-input:checked {
        background-color: var(--accent) !important;
        border-color: var(--accent) !important;
    }
    .form-check-label { color: #aaa !important; }

    /* ── Buttons ── */
    .btn-success {
        background: #1a5a1a !important; border-color: #1a5a1a !important;
        color: #7cfc00 !important; font-weight: 700;
    }
    .btn-success:hover { background: #1e6e1e !important; }
    .btn-danger {
        background: #7a1a1a !important; border-color: #7a1a1a !important;
        color: #ff6b6b !important; font-weight: 700;
    }
    .btn-danger:hover { background: #8a2020 !important; }
    .btn-secondary {
        background: var(--bg3) !important; border-color: var(--border2) !important;
        color: #aaa !important;
    }
    .btn-secondary:hover { background: #252525 !important; color: #fff !important; }

    /* ── Alerts ── */
    .alert-danger  { background: #2a0a0a !important; border-color: #5a1a1a !important; color: #ff8080 !important; }
    .alert-success { background: #0a2a0a !important; border-color: #1a5a1a !important; color: #80ff80 !important; }
    .alert-warning { background: #2a1e00 !important; border-color: #5a4a00 !important; color: #ffd060 !important; }
    .alert-info    { background: #0a1a2a !important; border-color: #1a3a5a !important; color: #80c0ff !important; }
    .alert { border-radius: 5px; font-size: 13px; }

    /* ── List groups ── */
    .list-group-item {
        background: #181818 !important; border-color: var(--border) !important;
        color: var(--text) !important; font-size: 13px;
    }

    /* ── Labels / badges ── */
    .badge, .label {
        display: inline-block; padding: 2px 8px; border-radius: 3px;
        font-size: 11px; font-weight: 700;
    }
    .label-success, .badge-success { background: #1a5a1a; color: #7cfc00; }
    .label-danger,  .badge-danger  { background: #7a1a1a; color: #ff8080; }
    .label-warning, .badge-warning { background: #5a4a00; color: #ffd060; }
    .label-default, .badge-secondary { background: #2a2a2a; color: #888; }

    /* ── Radio labels ── */
    .radio label { color: #aaa; font-size: 13px; cursor: pointer; }
    .form-group  { margin-bottom: 16px; }

    /* ── Footer ── */
    .inst-footer {
        border-top: 1px solid var(--border);
        padding: 14px 36px;
        background: #0d0d0d;
        font-size: 12px; color: #444;
    }

    /* ── Responsive ── */
    @media (max-width: 700px) {
        .inst-sidebar { display: none; }
        .inst-main { padding: 18px 14px; }
    }
    </style>
</head>
<body>

<!-- Top bar -->
<nav class="inst-topbar">
    <a href="install.php" class="inst-brand">
        Dark<span>Web</span>
        <small>Installer v<?php echo INSTALLER_VERSION; ?></small>
    </a>
</nav>

<div class="inst-layout">

    <!-- Sidebar / step list -->
    <aside class="inst-sidebar">
        <div class="inst-sidebar-title">Setup Steps</div>
        <?php
        foreach($install['step_list'] as $key => $row) {
            $state = '';
            if($key < $_SESSION['install_cstep'])  $state = 'done';
            if($key == $_SESSION['install_cstep']) $state = 'active';
            echo '<div class="inst-step-item '.$state.'">';
            echo '<span class="inst-step-num">';
            if($key < $_SESSION['install_cstep']) echo '<i class="bi bi-check-lg" style="font-size:12px"></i>';
            else echo ($key + 1);
            echo '</span>';
            echo '<span>'.htmlspecialchars($row[1]).'</span>';
            echo '</div>';
        }
        if($_SESSION['install_cstep'] > 0) {
            echo '<div style="padding:20px 18px 0;">';
            echo '<a href="?action=restart" class="btn btn-danger btn-sm w-100"><i class="bi bi-arrow-counterclockwise"></i> Start Over</a>';
            echo '</div>';
        }
        ?>
    </aside>

    <!-- Main content -->
    <main class="inst-main">
        <?php
        try {
            if(array_key_exists($_SESSION['install_cstep'], $install['step_list'])) {
                $fileName = $install['step_list'][$_SESSION['install_cstep']][0];
                if(file_exists($fileName)) {
                    if(!@include_once($fileName)) throw new Exception('Bad step file.');
                }
            }
        } catch (Exception $ex) {
            echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>'.$ex->getMessage().'</div>';
        }
        ?>
    </main>

</div>

<footer class="inst-footer">
    &copy; DarkWeb <?php echo date('Y'); ?> &mdash; All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

