<?php
use Darkheim\Infrastructure\Cron\CronManager;
echo '<h1 class="page-header"><i class="bi bi-clock-history me-2"></i>Cron Job Manager</h1>';
try {
	$cronManager = new CronManager();
	$cronList = $cronManager->getCronList();

	if(isset($_GET['action'])) {
		try {
			switch($_GET['action']) {
				case 'enable':   $cronManager->setId($_GET['id']); $cronManager->enableCron(); break;
				case 'disable':  $cronManager->setId($_GET['id']); $cronManager->disableCron(); break;
				case 'delete':   $cronManager->setId($_GET['id']); $cronManager->deleteCron(); break;
				case 'reset':    $cronManager->setId($_GET['id']); $cronManager->resetCronLastRun(); break;
				case 'allenable':  $cronManager->enableAll(); break;
				case 'alldisable': $cronManager->disableAll(); break;
				case 'allreset':   $cronManager->resetAllLastRun(); break;
				default: throw new RuntimeException('Invalid action.');
			}
			redirect(3, admincp_base('cronmanager'));
		} catch(Exception $ex) { message('error', $ex->getMessage()); }
	}

	if(isset($_POST['submit'])) {
		try {
			$cronManager->_name = $_POST['cron_name'];
			$cronManager->setFile($_POST['cron_file']);
			$cronManager->_interval = $_POST['cron_time'];
			$cronManager->addCron();
			redirect(3, admincp_base('cronmanager'));
		} catch(Exception $ex) { message('error', $ex->getMessage()); }
	}

	echo '<div class="row g-4">';

	// Left: add form + info
	echo '<div class="col-lg-3">';

	echo '<div class="acp-card mb-3"><div class="acp-card-header">Add Cron Job</div><div class="p-3">';
	$cron_times = $cronManager->_commonIntervals;
	echo '<form action="'.admincp_base('cronmanager').'" method="post">';
	echo '<div class="form-group"><label>Name</label><input type="text" class="form-control" name="cron_name" required/></div>';
	echo '<div class="form-group"><label>File</label><select class="form-control" name="cron_file">'.$cronManager->listCronFiles().'</select></div>';
	echo '<div class="form-group"><label>Repeat</label><select class="form-control" name="cron_time">';
	if(is_array($cron_times)) { foreach($cron_times as $s=>$d) {
		echo '<option value="'.$s.'">'.$d.'</option>';
	}
	}
	else {
		echo '<option value="300">5 Minutes</option>';
	}
	echo '</select></div>';
	echo '<button type="submit" name="submit" value="Add" class="btn btn-primary w-100">Add Cron</button>';
	echo '</form></div></div>';

	echo '<div class="acp-card mb-3"><div class="acp-card-header">Bulk Actions</div><div class="p-3 d-flex flex-column gap-2">';
	echo '<a href="'.admincp_base('cronmanager&action=allenable').'" class="btn btn-sm btn-default">Enable All</a>';
	echo '<a href="'.admincp_base('cronmanager&action=alldisable').'" class="btn btn-sm btn-default">Disable All</a>';
	echo '<a href="'.admincp_base('cronmanager&action=allreset').'" class="btn btn-sm btn-default">Reset All</a>';
	echo '</div></div>';

	echo '<div class="acp-card"><div class="acp-card-header">Cron API URL</div><div class="p-3">';
	echo '<input type="text" class="form-control" value="'.$cronManager->getCronApiUrl().'" readonly/>';
	echo '</div></div>';
	echo '</div>';

	// Right: cron list
	echo '<div class="col-lg-9"><div class="acp-card"><div class="acp-card-header">Scheduled Tasks</div>';
	if(is_array($cronList)) {
		echo '<table class="table table-hover mb-0"><thead><tr><th>ID</th><th>Name</th><th>File</th><th>Repeat</th><th>Last Run</th><th>Status</th><th></th></tr></thead><tbody>';
		foreach($cronList as $row) {
			$interval = sec_to_hms($row['cron_run_time']);
			$lastRun  = check_value($row['cron_last_run']) ? date('Y-m-d H:i',$row['cron_last_run']) : '<i style="color:#555">Never</i>';
			$isOn     = $row['cron_status'] == 1;
			$statusBtn = $isOn
				? '<a href="'.admincp_base('cronmanager&action=disable&id='.$row['cron_id']).'" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></a>'
				: '<a href="'.admincp_base('cronmanager&action=enable&id='.$row['cron_id']).'" class="btn btn-sm btn-default"><i class="bi bi-pause"></i></a>';
			echo '<tr>';
			echo '<td>'.$row['cron_id'].'</td>';
			echo '<td>'.$row['cron_name'].'</td>';
			echo '<td><code>'.$row['cron_file_run'].'</code></td>';
			echo '<td>'.$interval[0].'h '.$interval[1].'m</td>';
			echo '<td>'.$lastRun.'</td>';
			echo '<td>'.$statusBtn.'</td>';
			echo '<td class="text-end d-flex gap-1 justify-content-end">';
			echo '<a href="'.admincp_base('cronmanager&action=reset&id='.$row['cron_id']).'" class="btn btn-sm btn-default" title="Reset"><i class="bi bi-arrow-clockwise"></i></a>';
			echo '<a href="'.$cronManager->getCronApiUrl($row['cron_id']).'" target="_blank" class="btn btn-sm btn-default"><i class="bi bi-play-fill"></i></a>';
			if(!$row['cron_protected']) {
				echo '<a href="'.admincp_base(
						'cronmanager&action=delete&id='.$row['cron_id']
					)
					.'" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>';
			}
			echo '</td></tr>';
		}
		echo '</tbody></table>';
	} else { echo '<div class="p-3">'; inline_message('info', 'No cron jobs found.'); echo '</div>'; }
	echo '</div></div>';

	echo '</div>';
} catch(Exception $ex) { message('error', $ex->getMessage()); }
