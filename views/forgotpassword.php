<div class="page-title"><span><?php echo lang('module_titles_txt_15', true); ?></span></div>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-header-icon">🔒</div>
            <div class="auth-header-title"><?php echo lang('module_titles_txt_15', true); ?></div>
            <div class="auth-header-sub">Enter your registered email address and we will send you a password reset link</div>
        </div>

        <form action="" method="post" class="auth-form">
            <div class="auth-field">
                <label for="darkheimEmail"><?php echo lang('forgotpass_txt_1', true); ?></label>
                <input type="text" id="darkheimEmail" name="darkheimEmail_current" required autocomplete="email">
            </div>
            <button type="submit" name="darkheimEmail_submit" value="submit" class="auth-btn"><?php echo lang('forgotpass_txt_2', true); ?></button>
        </form>

        <div class="auth-footer">Remembered your password? <a href="<?php echo $loginUrl; ?>"><?php echo lang('menu_txt_4', true); ?></a></div>
    </div>
</div>

