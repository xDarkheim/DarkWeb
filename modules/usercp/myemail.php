<?php

use Darkheim\Application\Account\Account;

if(!isLoggedIn()) redirect(1,'login');

echo '<div class="page-title"><span>'.lang('module_titles_txt_5',true).'</span></div>';

try {

	if(!mconfig('active')) throw new Exception(lang('error_47',true));

	if(isset($_POST['darkheimEmail_submit'])) {
		try {
			$Account = new Account();
			$Account->changeEmailAddress($_SESSION['userid'], $_POST['darkheimEmail_newemail'], $_SERVER['REMOTE_ADDR']);
			if(mconfig('require_verification')) {
				message('success', lang('success_19',true));
			} else {
				message('success', lang('success_20',true));
			}
		} catch (Exception $ex) {
			message('error', $ex->getMessage());
		}
	}

	echo '<div class="ucp-card">';
		echo '<div class="ucp-card-header"><i class="bi bi-envelope-fill"></i>'.lang('module_titles_txt_5',true).'</div>';
		echo '<div class="ucp-card-body">';
			echo '<form class="ucp-form" action="" method="post">';

				echo '<div class="ucp-form-group">';
					echo '<label>'.lang('changemail_txt_1',true).'</label>';
					echo '<input type="text" class="form-control" name="darkheimEmail_newemail">';
				echo '</div>';

				echo '<div class="ucp-form-submit">';
					echo '<button type="submit" name="darkheimEmail_submit" value="submit" class="btn btn-primary">'.lang('changemail_txt_1',true).'</button>';
				echo '</div>';

			echo '</form>';
		echo '</div>';
	echo '</div>';

} catch(Exception $ex) {
	inline_message('error', $ex->getMessage());
}