<?php
/**
 * AdminCP ban account view.
 * No data variables — form only.
 */
?>
<h1 class="page-header"><i class="bi bi-slash-circle-fill me-2"></i>Ban Account</h1>

<div class="acp-card" style="max-width:520px;">
    <div class="acp-card-header"><i class="bi bi-slash-circle me-2"></i>Ban Account</div>
    <form action="" method="post" class="p-3">
        <div class="form-group">
            <label>Account Username</label>
            <input type="text" name="ban_account" class="form-control" placeholder="username" required>
        </div>
        <div class="form-group">
            <label>Days <small style="color:#666;">(0 = permanent)</small></label>
            <input type="number" name="ban_days" class="form-control" value="0" min="0" required>
        </div>
        <div class="form-group">
            <label>Reason <small style="color:#666;">(optional)</small></label>
            <input type="text" name="ban_reason" class="form-control" placeholder="Reason for ban">
        </div>
        <button type="submit" name="submit_ban" class="btn btn-danger w-100">
            <i class="bi bi-slash-circle me-1"></i>Ban Account
        </button>
    </form>
</div>

