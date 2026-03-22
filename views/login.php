<div class="page-title"><span><?php echo \Darkheim\Application\Language\Translator::phrase('module_titles_txt_2', true); ?></span></div>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-header-icon">🔑</div>
            <div class="auth-header-title"><?php echo \Darkheim\Application\Language\Translator::phrase('module_titles_txt_2', true); ?></div>
            <div class="auth-header-sub">Enter your credentials to access your account</div>
        </div>

        <form action="" method="post" class="auth-form">
            <div class="auth-field">
                <label for="darkheimLogin1"><?php echo \Darkheim\Application\Language\Translator::phrase('login_txt_1', true); ?></label>
                <input type="text" id="darkheimLogin1" name="darkheimLogin_user" required autocomplete="username">
            </div>

            <div class="auth-field">
                <label for="darkheimLogin2"><?php echo \Darkheim\Application\Language\Translator::phrase('login_txt_2', true); ?></label>
                <input type="password" id="darkheimLogin2" name="darkheimLogin_pwd" required autocomplete="current-password">
                <span class="auth-hint"><a href="<?php echo $forgotPassUrl; ?>"><?php echo \Darkheim\Application\Language\Translator::phrase('login_txt_4', true); ?></a></span>
            </div>

            <button type="submit" name="darkheimLogin_submit" value="submit" class="auth-btn"><?php echo \Darkheim\Application\Language\Translator::phrase('login_txt_3', true); ?></button>
        </form>

        <div class="auth-footer">Don't have an account? <a href="<?php echo $registerUrl; ?>"><?php echo \Darkheim\Application\Language\Translator::phrase('menu_txt_3', true); ?></a></div>
    </div>
</div>

