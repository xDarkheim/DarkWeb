<?php
/**
 * DarkCore
 *
 * @version 0.0.1
 * @author      Dmytro Hovenko <dmytro.hovenko@gmail.com>
 */

if(!defined('access') or !access or access != 'install') die();
?>
<h3 class="inst-title"><i class="bi bi-clock-history me-2"></i>Add Cron Jobs</h3>
<?php
try {
    if(isset($_POST['install_step_4_submit'])) {
        if(!isset($_POST['install_step_4_error'])) {
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

    foreach($install['cron_jobs'] as $key => $cron) {
        $cronPath = __PATH_CRON__ . $cron[2];
        if(!file_exists($cronPath)) throw new Exception('Missing cron file: ' . $cron[2]);
        array_push($install['cron_jobs'][$key], md5_file($cronPath));
    }

    $error = false;

    echo '<div class="inst-card mb-3">';
    echo '<div class="inst-card-header"><i class="bi bi-list-check me-1"></i>Cron Job Status</div>';
    echo '<div class="list-group list-group-flush">';

    foreach($install['cron_jobs'] as $cron) {
        $cronExists = $mudb->query_fetch_single(
            "SELECT * FROM " . Cron . " WHERE cron_file_run = ?",
            array($cron[2])
        );

        echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
        echo '<div>';
        echo '<span style="color:#ddd;font-size:13px;">'.htmlspecialchars($cron[0]).'</span> ';
        echo '<code style="font-size:11px;color:var(--text-muted);">'.htmlspecialchars($cron[2]).'</code>';
        echo '</div>';

        if(!$cronExists) {
            $addCron = $mudb->query(
                "INSERT INTO " . Cron . " (cron_name,cron_description,cron_file_run,cron_run_time,cron_status,cron_protected,cron_file_md5) VALUES (?, ?, ?, ?, ?, ?, ?)",
                $cron
            );
            if($addCron) {
                echo '<span class="label label-success"><i class="bi bi-check-lg"></i> Added</span>';
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

    echo '<form method="post" class="d-flex gap-2">';
    if($error) echo '<input type="hidden" name="install_step_4_error" value="1"/>';
    echo '<a href="' . __INSTALL_URL__ . 'install.php" class="btn btn-secondary"><i class="bi bi-arrow-repeat me-1"></i>Re-Check</a>';
    echo '<button type="submit" name="install_step_4_submit" value="continue" class="btn btn-success">Continue <i class="bi bi-arrow-right ms-1"></i></button>';
    echo '</form>';

} catch (Exception $ex) {
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>' . $ex->getMessage() . '</div>';
    echo '<a href="' . __INSTALL_URL__ . 'install.php" class="btn btn-secondary"><i class="bi bi-arrow-repeat me-1"></i>Re-Check</a>';
}