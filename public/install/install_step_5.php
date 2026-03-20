<?php
/**
 * DarkCore
 *
 * @version 0.0.1
 * @author      Dmytro Hovenko <dmytro.hovenko@gmail.com>
 */

if(!defined('access') or !access or access != 'install') die();
?>
<h3 class="inst-title"><i class="bi bi-gear-fill me-2"></i>Website Configuration</h3>
<?php
if(isset($_POST['install_step_5_submit'])) {
    try {
        if(empty($_POST['install_step_5_1'])) throw new Exception('Admin account username is required.');
        if(!Validator::AlphaNumeric($_POST['install_step_5_1'])) throw new Exception('Admin account username can only contain alphanumeric characters.');
        if(!isset($_SESSION['install_sql_host'])) throw new Exception('Database connection info missing. Restart the installation.');
        if(!isset($_SESSION['install_sql_db1']))  throw new Exception('Database connection info missing. Restart the installation.');


        $cmsDefaultConfig['admins']                  = array($_POST['install_step_5_1'] => 100);
        $cmsDefaultConfig['SQL_DB_HOST']             = $_SESSION['install_sql_host'];
        $cmsDefaultConfig['SQL_DB_NAME']             = $_SESSION['install_sql_db1'];
        $cmsDefaultConfig['SQL_DB_USER']             = $_SESSION['install_sql_user'];
        $cmsDefaultConfig['SQL_DB_PASS']             = $_SESSION['install_sql_pass'];
        $cmsDefaultConfig['SQL_DB_PORT']             = $_SESSION['install_sql_port'];
        $cmsDefaultConfig['SQL_PDO_DRIVER']          = $_SESSION['install_sql_dsn'];
        $cmsDefaultConfig['SQL_PASSWORD_ENCRYPTION'] = $_SESSION['install_sql_passwd_encrypt'];
        $cmsDefaultConfig['SQL_SHA256_SALT']         = $_SESSION['install_sql_sha256_salt'];
        $cmsDefaultConfig['cms_installed']           = true;

        $newCmsConfigs = json_encode($cmsDefaultConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if(!$newCmsConfigs) throw new Exception('Could not encode DarkCore configurations.');

        $cfgFile = fopen($cmsConfigsPath, 'w');
        if(!$cfgFile) throw new Exception('Could not open configuration file for writing. Check file permissions.');
        if(!fwrite($cfgFile, $newCmsConfigs)) throw new Exception('Could not save configuration file.');
        fclose($cfgFile);

        $_SESSION = array();
        session_destroy();

        header('Location: ' . __BASE_URL__);
        die();

    } catch (Exception $ex) {
        echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>'.$ex->getMessage().'</div>';
    }
}
?>

<div class="alert alert-success mb-3">
    <i class="bi bi-check-circle-fill me-2"></i>
    <strong>Almost done!</strong> Set your admin account to complete the installation.
</div>

<form method="post">
    <div class="inst-card mb-3">
        <div class="inst-card-header"><i class="bi bi-person-fill-gear me-1"></i>Admin Account</div>
        <div class="inst-card-body">
            <label class="form-label">Username <span style="color:var(--red)">*</span></label>
            <input type="text" name="install_step_5_1" class="form-control" placeholder="e.g. admin" required>
            <div class="form-text">In-game account username that will have full AdminCP access. Alphanumeric only.</div>
        </div>
    </div>

    <div class="inst-card mb-3">
        <div class="inst-card-header"><i class="bi bi-clipboard-data me-1"></i>Configuration Summary</div>
        <div class="list-group list-group-flush" style="font-size:13px;">
            <div class="list-group-item d-flex justify-content-between">
                <span style="color:var(--text-muted)">Host</span>
                <code><?php echo htmlspecialchars($_SESSION['install_sql_host'] ?? '—'); ?></code>
            </div>
            <div class="list-group-item d-flex justify-content-between">
                <span style="color:var(--text-muted)">Database</span>
                <code><?php echo htmlspecialchars($_SESSION['install_sql_db1'] ?? '—'); ?></code>
            </div>
            <div class="list-group-item d-flex justify-content-between">
                <span style="color:var(--text-muted)">PDO Driver</span>
                <code><?php $dsnMap = [1=>'dblib',2=>'sqlsrv',3=>'odbc']; echo $dsnMap[$_SESSION['install_sql_dsn'] ?? 1] ?? '—'; ?></code>
            </div>
            <div class="list-group-item d-flex justify-content-between">
                <span style="color:var(--text-muted)">Password Encryption</span>
                <code><?php echo htmlspecialchars(strtoupper($_SESSION['install_sql_passwd_encrypt'] ?? '—')); ?></code>
            </div>
        </div>
    </div>

    <button type="submit" name="install_step_5_submit" value="continue" class="btn btn-success btn-lg">
        <i class="bi bi-check2-circle me-2"></i>Complete Installation
    </button>
</form>

