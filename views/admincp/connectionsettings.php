<?php
/**
 * AdminCP connection settings view.
 *
 * Variables:
 * - string $host, $database, $user, $password, $port, $encryption
 */
?>
<h1 class="page-header"><i class="bi bi-database-fill me-2"></i>Connection Settings</h1>

<div class="acp-card">
    <div class="acp-card-header">Database Configuration</div>
    <form action="" method="post">
        <table class="table table-hover module_config_tables" style="table-layout:fixed;">
            <tr>
                <td><strong>Host</strong><p class="setting-description">Hostname/IP address of your MSSQL server.</p></td>
                <td><input type="text" class="form-control" name="SQL_DB_HOST" value="<?php echo htmlspecialchars($host, ENT_QUOTES, 'UTF-8'); ?>" required></td>
            </tr>
            <tr>
                <td><strong>Database</strong><p class="setting-description">Usually "MuOnline".</p></td>
                <td><input type="text" class="form-control" name="SQL_DB_NAME" value="<?php echo htmlspecialchars($database, ENT_QUOTES, 'UTF-8'); ?>" required></td>
            </tr>
            <tr>
                <td><strong>User</strong><p class="setting-description">Usually "sa".</p></td>
                <td><input type="text" class="form-control" name="SQL_DB_USER" value="<?php echo htmlspecialchars($user, ENT_QUOTES, 'UTF-8'); ?>" required></td>
            </tr>
            <tr>
                <td><strong>Password</strong><p class="setting-description">User's password.</p></td>
                <td><input type="text" class="form-control" name="SQL_DB_PASS" value="<?php echo htmlspecialchars($password, ENT_QUOTES, 'UTF-8'); ?>" required></td>
            </tr>
            <tr>
                <td><strong>Port</strong><p class="setting-description">Port number to connect to your MSSQL server. Usually 1433.</p></td>
                <td><input type="number" class="form-control" name="SQL_DB_PORT" value="<?php echo htmlspecialchars($port, ENT_QUOTES, 'UTF-8'); ?>" required></td>
            </tr>
            <tr>
                <td><strong>Password Encryption</strong><p class="setting-description">Select the type of password encryption used for your accounts table.</p></td>
                <td>
                    <?php foreach (['none' => 'None', 'wzmd5' => 'MD5 (WZ)', 'phpmd5' => 'MD5 (PHP)', 'sha256' => 'SHA-256'] as $val => $label): ?>
                    <div class="radio">
                        <label>
                            <input type="radio" name="SQL_PASSWORD_ENCRYPTION" value="<?php echo htmlspecialchars($val, ENT_QUOTES, 'UTF-8'); ?>"
                                   <?php echo $encryption === $val ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>
        <div class="p-3">
            <button type="submit" name="settings_submit" value="ok" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Save Settings
            </button>
        </div>
    </form>
</div>

