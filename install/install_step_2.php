<?php
/**
 * DarkWeb
 *
 * @version 0.0.1
 * @author      Dmytro Hovenko <dmytro.hovenko@gmail.com>
 */
if(!defined('access') or !access or access != 'install') die();
?>
<h3 class="inst-title"><i class="bi bi-database-gear me-2"></i>Database Connection</h3>
<?php
if(isset($_POST['install_step_2_submit'])) {
    try {
        if(empty($_POST['install_step_2_1'])) throw new Exception('Database host is required.');
        if(empty($_POST['install_step_2_7'])) throw new Exception('Database port is required.');
        if(empty($_POST['install_step_2_2'])) throw new Exception('Database username is required.');
        if(!isset($_POST['install_step_2_3'])) throw new Exception('Database password field is required.');
        if(empty($_POST['install_step_2_4'])) throw new Exception('Database name is required.');
        if(!isset($_POST['install_step_2_6']) || !in_array(strtolower($_POST['install_step_2_6']), $install['PDO_PWD_ENCRYPT'])) throw new Exception('You must select a password encryption method.');
        $_SESSION['install_sql_host']           = trim($_POST['install_step_2_1']);
        $_SESSION['install_sql_port']           = trim($_POST['install_step_2_7']);
        $_SESSION['install_sql_user']           = trim($_POST['install_step_2_2']);
        $_SESSION['install_sql_pass']           = $_POST['install_step_2_3'];
        $_SESSION['install_sql_db1']            = trim($_POST['install_step_2_4']);
        $_SESSION['install_sql_passwd_encrypt'] = strtolower($_POST['install_step_2_6']);
        $_SESSION['install_sql_sha256_salt']    = isset($_POST['install_step_2_9']) ? $_POST['install_step_2_9'] : '';
        $db1 = new dB($_SESSION['install_sql_host'], $_SESSION['install_sql_port'], $_SESSION['install_sql_db1'], $_SESSION['install_sql_user'], $_SESSION['install_sql_pass']);
        if($db1->dead) throw new Exception('Could not connect to database (' . htmlspecialchars($_SESSION['install_sql_db1']) . '). Check your settings.');
        $_SESSION['install_cstep']++;
        header('Location: install.php');
        die();
    } catch (Exception $ex) {
        echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>'.$ex->getMessage().'</div>';
    }
}
?>
<form method="post">
    <div class="inst-card mb-3">
        <div class="inst-card-header"><i class="bi bi-hdd-network me-1"></i>Connection Settings</div>
        <div class="inst-card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Host</label>
                    <input type="text" name="install_step_2_1" class="form-control" value="<?php echo htmlspecialchars($_SESSION['install_sql_host'] ?? '192.168.1.56'); ?>">
                    <div class="form-text">IP address or hostname of your MSSQL server.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Port</label>
                    <input type="text" name="install_step_2_7" class="form-control" value="<?php echo htmlspecialchars($_SESSION['install_sql_port'] ?? '1433'); ?>">
                    <div class="form-text">Default: 1433</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="install_step_2_2" class="form-control" value="<?php echo htmlspecialchars($_SESSION['install_sql_user'] ?? 'sa'); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="text" name="install_step_2_3" class="form-control" value="<?php echo htmlspecialchars($_SESSION['install_sql_pass'] ?? ''); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Database <span style="color:var(--red)">*</span></label>
                    <input type="text" name="install_step_2_4" class="form-control" value="<?php echo htmlspecialchars($_SESSION['install_sql_db1'] ?? 'MuOnline'); ?>">
                    <div class="form-text">Your game database. Usually <code>MuOnline</code>. All CMS tables will be created here.</div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="inst-card h-100">
                <div class="inst-card-header"><i class="bi bi-shield-lock me-1"></i>Password Encryption</div>
                <div class="inst-card-body">
                    <?php
                    $enc = $_SESSION['install_sql_passwd_encrypt'] ?? 'none';
                    foreach(['none'=>'None (plain text)', 'wzmd5'=>'MD5 (WZ)', 'phpmd5'=>'MD5 (PHP)', 'sha256'=>'SHA-256'] as $v => $l):
                    ?>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="install_step_2_6" id="enc_<?php echo $v; ?>" value="<?php echo $v; ?>" <?php echo ($enc === $v) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="enc_<?php echo $v; ?>"><?php echo $l; ?></label>
                    </div>
                    <?php endforeach; ?>
                    <div class="mt-2">
                        <label class="form-label">SHA-256 Salt</label>
                        <input type="text" name="install_step_2_9" class="form-control" value="<?php echo htmlspecialchars($_SESSION['install_sql_sha256_salt'] ?? '1234567890'); ?>">
                        <div class="form-text">Only required when using SHA-256. Must match server config.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button type="submit" name="install_step_2_submit" value="continue" class="btn btn-success">
        <i class="bi bi-plug-fill me-1"></i>Test Connection &amp; Continue
    </button>
</form>
