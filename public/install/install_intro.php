<?php
/**
 * DarkCore
 *
 * @version 0.0.1
 * @author      Dmytro Hovenko <dmytro.hovenko@gmail.com>
 */

if(!defined('access') or !access or access != 'install') die();

if(isset($_GET['action']) && $_GET['action'] == 'install') {
    $_SESSION['install_cstep']++;
    header('Location: install.php');
    die();
}
?>
<h3 class="inst-title"><i class="bi bi-rocket-takeoff me-2"></i>Welcome to DarkCore</h3>

<div class="inst-card mb-3">
    <div class="inst-card-header"><i class="bi bi-info-circle me-1"></i>Getting Started</div>
    <div class="inst-card-body">
        <p>This wizard will configure your <strong>DarkCore</strong> installation step by step. Make sure your web server meets all requirements before proceeding.</p>
        <p class="mb-0" style="color:var(--text-muted);font-size:13px;">If you are on shared hosting, ensure outgoing TCP connections to your MSSQL server on port <code>1433</code> are allowed.</p>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="inst-card h-100">
            <div class="inst-card-header"><i class="bi bi-server me-1"></i>Requirements</div>
            <div class="inst-card-body">
                <ul style="padding-left:18px;color:var(--text);font-size:13px;line-height:2;">
                    <li>PHP <strong>8.4</strong> or higher</li>
                    <li>PDO <code>pdo_dblib</code> via FreeTDS (Linux)</li>
                    <li>Extensions: OpenSSL, cURL, GD, mbstring, XML, JSON, ZIP, BCMath</li>
                    <li>Writable <code>includes/config/</code>, <code>includes/cache/</code>, <code>includes/logs/</code></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="inst-card h-100">
            <div class="inst-card-header"><i class="bi bi-database me-1"></i>Databases</div>
            <div class="inst-card-body" style="font-size:13px;line-height:1.8;">
                <p>DarkCore uses a <strong>single MSSQL database</strong>:</p>
                <ul style="padding-left:18px;color:var(--text);">
                    <li><code>MuOnline</code> — All game and CMS data in one database.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<a href="<?php echo __INSTALL_URL__; ?>install.php?action=install" class="btn btn-success">
    <i class="bi bi-play-fill me-1"></i>Start Installation
</a>
