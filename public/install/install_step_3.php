<?php
/**
 * DarkCore
 *
 * @version 0.0.1
 * @author      Dmytro Hovenko <dmytro.hovenko@gmail.com>
 */

if(!defined('access') or !access or access != 'install') die();
?>
<h3 class="inst-title"><i class="bi bi-table me-2"></i>Create CMS Tables</h3>
<?php
try {
    if(isset($_POST['install_step_3_submit'])) {
        if(!isset($_POST['install_step_3_error'])) {
            $_SESSION['install_cstep']++;
            header('Location: install.php');
            die();
        } else {
            echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>One or more errors occurred. Fix them before continuing.</div>';
        }
    }

    if(!isset($_SESSION['install_sql_db1'])) throw new Exception('Database connection info missing. Please restart the installation.');

    $mudb = new dB(
        $_SESSION['install_sql_host'],
        $_SESSION['install_sql_port'],
        $_SESSION['install_sql_db1'],
        $_SESSION['install_sql_user'],
        $_SESSION['install_sql_pass']
    );

    if($mudb->dead) throw new Exception('Could not connect to database: ' . htmlspecialchars($_SESSION['install_sql_db1']));
    if(!is_array($install['sql_list'])) throw new Exception('Could not load CMS SQL tables list.');

    foreach($install['sql_list'] as $sqlFileName => $sqlTableName) {
        if(!file_exists('sql/' . $sqlFileName . '.txt'))
            throw new Exception('Missing SQL file: sql/' . $sqlFileName . '.txt');
    }

    $error = false;
    $force = (isset($_GET['force']) && $_GET['force'] == 1);

    echo '<div class="alert alert-info mb-3">';
    echo '<i class="bi bi-database-fill me-2"></i>Creating CMS tables in database: <strong>' . htmlspecialchars($_SESSION['install_sql_db1']) . '</strong>';
    echo '</div>';

    echo '<div class="inst-card mb-3">';
    echo '<div class="inst-card-header"><i class="bi bi-list-check me-1"></i>Table Status</div>';
    echo '<div class="list-group list-group-flush">';

    foreach($install['sql_list'] as $sqlFileName => $sqlTableName) {
        $sqlFileContents = file_get_contents('sql/' . $sqlFileName . '.txt');
        if(!$sqlFileContents) continue;

        $query = str_replace('{TABLE_NAME}', $sqlTableName, $sqlFileContents);

        if($force) {
            $mudb->query("IF OBJECT_ID('[" . $sqlTableName . "]','U') IS NOT NULL DROP TABLE [" . $sqlTableName . "]");
        }

        $tableExists = $mudb->query_fetch_single("SELECT * FROM sysobjects WHERE xtype = 'U' AND name = ?", array($sqlTableName));

        echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
        echo '<code style="color:var(--accent);font-size:12px;">'.htmlspecialchars($sqlTableName).'</code>';

        if(!$tableExists) {
            $create = $mudb->query($query);
            if($create) {
                echo '<span class="label label-success"><i class="bi bi-check-lg"></i> Created</span>';
            } else {
                echo '<span class="label label-danger"><i class="bi bi-x-lg"></i> Error</span>';
                $error = true;
            }
        } else {
            echo '<span class="label label-default"><i class="bi bi-dash-lg"></i> Already Exists</span>';
        }

        echo '</div>';
    }

    echo '</div></div>';

    echo '<form method="post" class="d-flex gap-2 flex-wrap">';
    if($error) echo '<input type="hidden" name="install_step_3_error" value="1"/>';
    echo '<a href="' . __INSTALL_URL__ . 'install.php" class="btn btn-secondary"><i class="bi bi-arrow-repeat me-1"></i>Re-Check</a>';
    echo '<button type="submit" name="install_step_3_submit" value="continue" class="btn btn-success">Continue <i class="bi bi-arrow-right ms-1"></i></button>';
    echo '<a href="' . __INSTALL_URL__ . 'install.php?force=1" class="btn btn-danger ms-auto"><i class="bi bi-trash3 me-1"></i>Drop &amp; Recreate</a>';
    echo '</form>';

} catch (Exception $ex) {
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>' . $ex->getMessage() . '</div>';
    echo '<a href="' . __INSTALL_URL__ . 'install.php" class="btn btn-secondary"><i class="bi bi-arrow-repeat me-1"></i>Re-Check</a>';
}
