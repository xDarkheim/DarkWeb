<?php

use Darkheim\Application\Auth\AuthService;

if(isLoggedIn()) redirect();

echo '<div class="page-title"><span>'.lang('module_titles_txt_2',true).'</span></div>';

try {

	if(!mconfig('active')) throw new Exception(lang('error_47',true));

	if(isset($_POST['darkheimLogin_submit'])) {
		try {
			$auth = new AuthService();
			$auth->login($_POST['darkheimLogin_user'], $_POST['darkheimLogin_pwd']);
		} catch (Exception $ex) {
			message('error', $ex->getMessage());
		}
	}

?>
<div class="auth-wrap">
	<div class="auth-card">

		<div class="auth-header">
			<div class="auth-header-icon">🔑</div>
			<div class="auth-header-title"><?php echo lang('module_titles_txt_2', true); ?></div>
			<div class="auth-header-sub">Enter your credentials to access your account</div>
		</div>

		<form action="" method="post" class="auth-form">

			<div class="auth-field">
				<label for="darkheimLogin1"><?php echo lang('login_txt_1', true); ?></label>
				<input type="text" id="darkheimLogin1" name="darkheimLogin_user" required autocomplete="username">
			</div>

			<div class="auth-field">
				<label for="darkheimLogin2"><?php echo lang('login_txt_2', true); ?></label>
				<input type="password" id="darkheimLogin2" name="darkheimLogin_pwd" required autocomplete="current-password">
				<span class="auth-hint">
					<a href="<?php echo __BASE_URL__; ?>forgotpassword/"><?php echo lang('login_txt_4', true); ?></a>
				</span>
			</div>

			<button type="submit" name="darkheimLogin_submit" value="submit" class="auth-btn">
				<?php echo lang('login_txt_3', true); ?>
			</button>

		</form>


		<div class="auth-footer">
			Don't have an account? <a href="<?php echo __BASE_URL__; ?>register"><?php echo lang('menu_txt_3', true); ?></a>
		</div>

	</div>

	<div class="auth-security">
		<div class="auth-security-title">
			<span class="auth-security-icon">🛡️</span>
			Security Notice
		</div>
		<ul class="auth-security-list">
			<li>
				<span class="auth-sec-bullet">⚠️</span>
				<span>The administration will <strong>never</strong> ask for your password under any circumstances.</span>
			</li>
			<li>
				<span class="auth-sec-bullet">🚫</span>
				<span>Never share your password with anyone — including players claiming to be staff or GMs.</span>
			</li>
			<li>
				<span class="auth-sec-bullet">🔗</span>
				<span>Always make sure you are on the official website before entering your credentials.</span>
			</li>
			<li>
				<span class="auth-sec-bullet">📧</span>
				<span>We will never ask for your account details via Discord, in-game chat or email.</span>
			</li>
			<li>
				<span class="auth-sec-bullet">🔒</span>
				<span>Use a unique password that you do not use on any other website or service.</span>
			</li>
		</ul>
		<div class="auth-security-footer">
			If someone asked for your password, <strong>report it immediately</strong> via our support channels.
		</div>
	</div>

</div>
<?php

} catch(Exception $ex) {
	inline_message('error', $ex->getMessage());
}