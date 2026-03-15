<?php
/**
 * DarkWeb
 *
 * @version 0.0.1
 * @author      Dmytro Hovenko <dmytro.hovenko@gmail.com>
 */

if(!defined('access') or !access or access != 'install') die();
?>
<h3 class="inst-title"><i class="bi bi-display me-2"></i>Web Server Requirements</h3>
<?php
if(isset($_POST['install_step_1_submit'])) {
    try {
        $_SESSION['install_cstep']++;
        header('Location: install.php');
        die();
    } catch (Exception $ex) {
        echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>'.$ex->getMessage().'</div>';
    }
}

function reqRow($label, $ok, $note = '') {
    $badge = $ok
        ? '<span class="label label-success"><i class="bi bi-check-lg"></i> OK</span>'
        : '<span class="label label-danger"><i class="bi bi-x-lg"></i> Fix</span>';
    echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
    echo '<span>'.htmlspecialchars($label).($note ? ' <small style="color:var(--text-muted)">('.$note.')</small>' : '').'</span>';
    echo $badge;
    echo '</div>';
}
function optRow($label, $ok, $optNote = 'Optional') {
    $badge = $ok
        ? '<span class="label label-success"><i class="bi bi-check-lg"></i> OK</span>'
        : '<span class="label label-warning"><i class="bi bi-dash-lg"></i> '.$optNote.'</span>';
    echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
    echo '<span>'.htmlspecialchars($label).'</span>';
    echo $badge;
    echo '</div>';
}
?>

<div class="inst-card mb-3">
    <div class="inst-card-header"><i class="bi bi-cpu me-1"></i>PHP &amp; Extensions</div>
    <div class="list-group list-group-flush">
        <?php
        reqRow('PHP 8.4 or higher',       version_compare(PHP_VERSION, '8.4', '>='), 'PHP '.PHP_VERSION);
        reqRow('PDO',                      extension_loaded('pdo'));
        reqRow('PDO dblib (FreeTDS/Linux)', extension_loaded('pdo_dblib'));
        reqRow('OpenSSL',                  extension_loaded('openssl'));
        reqRow('cURL',                     extension_loaded('curl'));
        reqRow('GD',                       extension_loaded('gd'));
        reqRow('mbstring',                 extension_loaded('mbstring'));
        reqRow('JSON',                     extension_loaded('json'));
        reqRow('XML',                      extension_loaded('xml'));
        reqRow('ZIP',                      extension_loaded('zip'));
        reqRow('BCMath',                   extension_loaded('bcmath'));
        optRow('Xdebug (dev only)',        extension_loaded('xdebug'));
        ?>
    </div>
</div>

<div class="inst-card mb-3">
    <div class="inst-card-header"><i class="bi bi-folder2-open me-1"></i>Writable Paths</div>
    <div class="list-group list-group-flush">
        <?php
        if(is_array($writablePaths)) {
            foreach($writablePaths as $filepath) {
                $fullPath = __PATH_INCLUDES__ . $filepath;
                if(!file_exists($fullPath)) {
                    $badge = '<span class="label label-danger"><i class="bi bi-x-lg"></i> Missing</span>';
                } elseif(!is_writable($fullPath)) {
                    $badge = '<span class="label label-warning"><i class="bi bi-lock-fill"></i> Not Writable</span>';
                } else {
                    $badge = '<span class="label label-success"><i class="bi bi-check-lg"></i> OK</span>';
                }
                echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                echo '<code style="font-size:12px;color:#888;">'.htmlspecialchars($filepath).'</code>';
                echo $badge;
                echo '</div>';
            }
        }
        ?>
    </div>
</div>

<div class="alert alert-warning mb-3">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Fix any issues above before continuing. You can re-check by clicking <strong>Re-Check</strong>.
</div>

<form method="post" class="d-flex gap-2">
    <a href="<?php echo __INSTALL_URL__; ?>install.php" class="btn btn-secondary">
        <i class="bi bi-arrow-repeat me-1"></i>Re-Check
    </a>
    <button type="submit" name="install_step_1_submit" value="ok" class="btn btn-success">
        Continue <i class="bi bi-arrow-right ms-1"></i>
    </button>
</form>
