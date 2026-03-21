<?php
/**
 * Verify email result view.
 *
 * Variables provided by VerifyEmailController:
 * - string $pageTitle
 * - string $resultHtml
 */
?>

<div class="page-container">
    <div class="page-title"><span><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></span></div>
    <div class="page-content">
        <?php echo $resultHtml; ?>
    </div>
</div>

