<?php

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Account\Account;

if(!isLoggedIn()) redirect(1,'login');

echo '<div class="page-title"><span>'.lang('module_titles_txt_6',true).'</span></div>';

try {

	if(!mconfig('active')) throw new Exception(lang('error_47',true));

	$common = new Common();

	if(mconfig('change_password_email_verification') && $common->hasActivePasswordChangeRequest($_SESSION['userid'])) {
		throw new Exception(lang('error_19',true));
	}

	if(isset($_POST['darkheimPassword_submit'])) {
		try {
			$Account = new Account();
			if(mconfig('change_password_email_verification')) {
				$Account->changePasswordProcess_verifyEmail($_SESSION['userid'], $_SESSION['username'], $_POST['darkheimPassword_current'], $_POST['darkheimPassword_new'], $_POST['darkheimPassword_newconfirm'], $_SERVER['REMOTE_ADDR']);
			} else {
				$Account->changePasswordProcess($_SESSION['userid'], $_SESSION['username'], $_POST['darkheimPassword_current'], $_POST['darkheimPassword_new'], $_POST['darkheimPassword_newconfirm']);
			}
		} catch (Exception $ex) {
			message('error', $ex->getMessage());
		}
	}

	echo '<div class="ucp-card">';
		echo '<div class="ucp-card-header"><i class="bi bi-key-fill"></i>'.lang('module_titles_txt_6',true).'</div>';
		echo '<div class="ucp-card-body">';
			echo '<form class="ucp-form" action="" method="post">';

				echo '<div class="ucp-form-group">';
					echo '<label>'.lang('changepassword_txt_1',true).'</label>';
					echo '<input type="password" class="form-control" name="darkheimPassword_current">';
				echo '</div>';

				echo '<div class="ucp-form-group">';
					echo '<label>'.lang('changepassword_txt_2',true).'</label>';
					echo '<input type="password" class="form-control" name="darkheimPassword_new">';
				echo '</div>';

				echo '<div class="ucp-form-group">';
					echo '<label>'.lang('changepassword_txt_3',true).'</label>';
					echo '<input type="password" class="form-control" name="darkheimPassword_newconfirm">';
				echo '</div>';

				echo '<div class="ucp-form-submit">';
					echo '<button type="submit" name="darkheimPassword_submit" value="submit" class="btn btn-primary">'.lang('changepassword_txt_4',true).'</button>';
				echo '</div>';

			echo '</form>';
		echo '</div>';
	echo '</div>';

} catch(Exception $ex) {
	inline_message('error', $ex->getMessage());
}