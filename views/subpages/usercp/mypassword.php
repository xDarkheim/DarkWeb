<?php
/**
 * UserCP change password view.
 *
 * Variables provided by MyPasswordSubpageController:
 * - string $pageTitle
 * - string $cardTitle
 * - string $currentLabel
 * - string $newLabel
 * - string $confirmLabel
 * - string $submitLabel
 */
?>

<div class="page-title"><span><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></span></div>

<div class="ucp-card">
    <div class="ucp-card-header"><i class="bi bi-key-fill"></i><?php echo htmlspecialchars($cardTitle, ENT_QUOTES, 'UTF-8'); ?></div>
    <div class="ucp-card-body">
        <form class="ucp-form" action="" method="post">
            <div class="ucp-form-group">
                <label for="darkheimPassword_current"><?php echo htmlspecialchars($currentLabel, ENT_QUOTES, 'UTF-8'); ?></label>
                <input type="password" class="form-control" id="darkheimPassword_current" name="darkheimPassword_current">
            </div>
            <div class="ucp-form-group">
                <label for="darkheimPassword_new"><?php echo htmlspecialchars($newLabel, ENT_QUOTES, 'UTF-8'); ?></label>
                <input type="password" class="form-control" id="darkheimPassword_new" name="darkheimPassword_new">
            </div>
            <div class="ucp-form-group">
                <label for="darkheimPassword_newconfirm"><?php echo htmlspecialchars($confirmLabel, ENT_QUOTES, 'UTF-8'); ?></label>
                <input type="password" class="form-control" id="darkheimPassword_newconfirm" name="darkheimPassword_newconfirm">
            </div>
            <div class="ucp-form-submit">
                <button type="submit" name="darkheimPassword_submit" value="submit" class="btn btn-primary"><?php echo htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
        </form>
    </div>
</div>

