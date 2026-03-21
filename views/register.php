<div class="page-title"><span><?php echo lang('module_titles_txt_1', true); ?></span></div>

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
                <span class="auth-hint"><?php echo langf('register_txt_6', [$userMinLen, $userMaxLen]); ?></span>
            </div>

            <div class="auth-field">
                <label for="darkheimRegistration2"><?php echo lang('register_txt_2', true); ?></label>
                <input type="password" id="darkheimRegistration2" name="darkheimRegister_pwd" required autocomplete="new-password">
                <span class="auth-hint"><?php echo langf('register_txt_7', [$pwdMinLen, $pwdMaxLen]); ?></span>
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

            <?php if ($recaptchaEnabled): ?>
            <div class="auth-field"><div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptchaSiteKey, ENT_QUOTES, 'UTF-8'); ?>"></div></div>
            <script src="https://www.google.com/recaptcha/api.js"></script>
            <?php endif; ?>

            <div class="auth-tos"><?php echo langf('register_txt_10', [$tosUrl]); ?></div>
            <button type="submit" name="darkheimRegister_submit" value="submit" class="auth-btn"><?php echo lang('register_txt_5', true); ?></button>
        </form>

        <div class="auth-footer">Already have an account? <a href="<?php echo $loginUrl; ?>"><?php echo lang('menu_txt_4', true); ?></a></div>
    </div>
</div>

