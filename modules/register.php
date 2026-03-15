<?php

use Darkheim\Application\Account\Account;

if(isLoggedIn()) redirect();

echo '<div class="page-title"><span>'.lang('module_titles_txt_1',true).'</span></div>';

try {

	if(!mconfig('active')) throw new Exception(lang('error_17',true));

	if(isset($_POST['darkheimRegister_submit'])) {
		try {
			$Account = new Account();
			if(mconfig('register_enable_recaptcha')) {
				$recaptcha = new \ReCaptcha\ReCaptcha(mconfig('register_recaptcha_secret_key'));
				$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
				if(!$resp->isSuccess()) throw new Exception(lang('error_18',true));
			}
			$Account->registerAccount($_POST['darkheimRegister_user'], $_POST['darkheimRegister_pwd'], $_POST['darkheimRegister_pwdc'], $_POST['darkheimRegister_email']);
		} catch (Exception $ex) {
			message('error', $ex->getMessage());
		}
	}

?>
<div class="auth-wrap">
	<div class="auth-card">

		<div class="auth-header">
			<div class="auth-header-icon">⚔️</div>
			<div class="auth-header-title"><?php echo lang('module_titles_txt_1', true); ?></div>
			<div class="auth-header-sub">Create your account and enter the world of MU</div>
		</div>

		<form action="" method="post" class="auth-form">

			<div class="auth-field">
				<label for="darkheimRegistration1"><?php echo lang('register_txt_1', true); ?></label>
				<input type="text" id="darkheimRegistration1" name="darkheimRegister_user" required autocomplete="username">
				<span class="auth-hint"><?php echo langf('register_txt_6', [config('username_min_len', true), config('username_max_len', true)]); ?></span>
			</div>

			<div class="auth-field">
				<label for="darkheimRegistration2"><?php echo lang('register_txt_2', true); ?></label>
				<input type="password" id="darkheimRegistration2" name="darkheimRegister_pwd" required autocomplete="new-password">
				<span class="auth-hint"><?php echo langf('register_txt_7', [config('password_min_len', true), config('password_max_len', true)]); ?></span>
			</div>

			<div class="auth-field">
				<label for="darkheimRegistration3"><?php echo lang('register_txt_3', true); ?></label>
				<input type="password" id="darkheimRegistration3" name="darkheimRegister_pwdc" required autocomplete="new-password">
				<span class="auth-hint"><?php echo lang('register_txt_8', true); ?></span>
			</div>

			<div class="auth-field">
				<label for="darkheimRegistration4"><?php echo lang('register_txt_4', true); ?></label>
				<input type="email" id="darkheimRegistration4" name="darkheimRegister_email" required autocomplete="email">
				<span class="auth-hint"><?php echo lang('register_txt_9', true); ?></span>
			</div>

			<?php if(mconfig('register_enable_recaptcha')): ?>
			<div class="auth-field">
				<div class="g-recaptcha" data-sitekey="<?php echo mconfig('register_recaptcha_site_key'); ?>"></div>
			</div>
			<script src='https://www.google.com/recaptcha/api.js'></script>
			<?php endif; ?>

			<div class="auth-tos">
				<?php echo langf('register_txt_10', [__BASE_URL__.'tos']); ?>
			</div>

			<button type="submit" name="darkheimRegister_submit" value="submit" class="auth-btn">
				<?php echo lang('register_txt_5', true); ?>
			</button>

		</form>


		<div class="auth-footer">
			Already have an account? <a href="<?php echo __BASE_URL__; ?>login"><?php echo lang('menu_txt_4', true); ?></a>
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
				<span>The administration will <strong>never</strong> ask for your password after registration.</span>
			</li>
			<li>
				<span class="auth-sec-bullet">🚫</span>
				<span>Do not use passwords from other games, services or email accounts.</span>
			</li>
			<li>
				<span class="auth-sec-bullet">📧</span>
				<span>Use a valid email address — it is required to recover your account if access is lost.</span>
			</li>
			<li>
				<span class="auth-sec-bullet">🔗</span>
				<span>Only register on the official website. Fake sites steal your account data.</span>
			</li>
			<li>
				<span class="auth-sec-bullet">🔒</span>
				<span>Choose a strong, unique password with letters, numbers and special characters.</span>
			</li>
		</ul>
		<div class="auth-security-footer">
			Your account security is <strong>your responsibility</strong>. Keep your credentials private at all times.
		</div>
	</div>

</div>
<?php

} catch(Exception $ex) {
	inline_message('error', $ex->getMessage());
}

