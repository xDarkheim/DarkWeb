<div class="page-title"><span><?php
        use Darkheim\Application\Language\Translator;

echo Translator::phrase('module_titles_txt_15', true); ?></span></div>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-header-icon">🔒</div>
            <div class="auth-header-title"><?php echo Translator::phrase('module_titles_txt_15', true); ?></div>
            <div class="auth-header-sub">Enter your registered email address and we will send you a password reset link</div>
        </div>

        <form action="" method="post" class="auth-form">
            <div class="auth-field">
                <label for="darkheimEmail"><?php echo Translator::phrase('forgotpass_txt_1', true); ?></label>
                <input type="text" id="darkheimEmail" name="darkheimEmail_current" required autocomplete="email">
            </div>
            <button type="submit" name="darkheimEmail_submit" value="submit" class="auth-btn"><?php echo Translator::phrase('forgotpass_txt_2', true); ?></button>
        </form>

        <div class="auth-footer">Remembered your password? <a href="<?php echo $loginUrl; ?>"><?php echo Translator::phrase('menu_txt_4', true); ?></a></div>
    </div>

    <div class="auth-security">
        <div class="auth-security-title">
            <span class="auth-security-icon">🛡️</span>
            Security Notice
        </div>
        <ul class="auth-security-list">
            <li>
                <span class="auth-sec-bullet">⚠️</span>
                <span>The administration will <strong>never</strong> ask for your password — not by email, Discord or in-game.</span>
            </li>
            <li>
                <span class="auth-sec-bullet">🚫</span>
                <span>If someone sent you a "password reset" link you did not request, do not click it.</span>
            </li>
            <li>
                <span class="auth-sec-bullet">📧</span>
                <span>Password reset emails come only from our official domain. Check the sender address carefully.</span>
            </li>
            <li>
                <span class="auth-sec-bullet">🔗</span>
                <span>Always verify the URL in your browser before entering any account information.</span>
            </li>
            <li>
                <span class="auth-sec-bullet">🔒</span>
                <span>After recovering access, set a new strong password and do not reuse old ones.</span>
            </li>
        </ul>
        <div class="auth-security-footer">
            Suspect a phishing attempt? <strong>Contact support immediately</strong> through official channels.
        </div>
    </div>

</div>

