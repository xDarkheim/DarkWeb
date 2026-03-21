<?php
/**
 * UserCP change email view.
 *
 * Variables provided by MyEmailSubpageController:
 * - string $pageTitle
 * - string $cardTitle
 * - string $submitLabel
 */
?>

<div class="page-title"><span><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></span></div>

<div class="ucp-card">
    <div class="ucp-card-header"><i class="bi bi-envelope-fill"></i><?php echo htmlspecialchars($cardTitle, ENT_QUOTES, 'UTF-8'); ?></div>
    <div class="ucp-card-body">
        <form class="ucp-form" action="" method="post">
            <div class="ucp-form-group">
                <label for="darkheimEmail_newemail"><?php echo htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?></label>
                <input type="text" class="form-control" id="darkheimEmail_newemail" name="darkheimEmail_newemail">
            </div>
            <div class="ucp-form-submit">
                <button type="submit" name="darkheimEmail_submit" value="submit" class="btn btn-primary"><?php echo htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
        </form>
    </div>
</div>

