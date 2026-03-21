<?php if (!empty($sidebarData['showLoginPanel'])): ?>
<div class="panel panel-sidebar">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo htmlspecialchars((string) ($sidebarData['loginTitle'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> <a href="<?php echo htmlspecialchars((string) ($sidebarData['forgotPasswordUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-xs pull-right"><?php echo htmlspecialchars((string) ($sidebarData['forgotPasswordLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></a></h3>
    </div>
    <div class="panel-body">
        <form action="<?php echo htmlspecialchars((string) ($sidebarData['loginActionUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" method="post">
            <div class="form-group">
                <label class="sr-only" for="loginBox1"><?php echo htmlspecialchars((string) ($sidebarData['loginUsernameLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></label>
                <input type="text" class="form-control" id="loginBox1" name="darkheimLogin_user" required>
            </div>
            <div class="form-group">
                <label class="sr-only" for="loginBox2"><?php echo htmlspecialchars((string) ($sidebarData['loginPasswordLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></label>
                <input type="password" class="form-control" id="loginBox2" name="darkheimLogin_pwd" required>
            </div>
            <button type="submit" name="darkheimLogin_submit" value="submit" class="btn btn-primary"><?php echo htmlspecialchars((string) ($sidebarData['loginButtonLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></button>
        </form>
    </div>
</div>

<div class="sidebar-banner"><a href="<?php echo htmlspecialchars((string) ($sidebarData['joinBannerUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><img src="<?php echo htmlspecialchars((string) ($sidebarData['joinBannerImageUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) ($sidebarData['joinBannerAlt'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"/></a></div>
<?php endif; ?>

<?php if (!empty($sidebarData['showUsercpPanel'])): ?>
<div class="panel panel-sidebar panel-usercp">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo htmlspecialchars((string) ($sidebarData['usercpTitle'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> <a href="<?php echo htmlspecialchars((string) ($sidebarData['sidebarLogoutUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-xs pull-right"><?php echo htmlspecialchars((string) ($sidebarData['sidebarLogoutLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></a></h3>
    </div>
    <div class="panel-body">
        <?php echo $sidebarData['usercpMenuHtml'] ?? ''; ?>
    </div>
</div>
<?php endif; ?>

<div class="sidebar-banner"><a href="<?php echo htmlspecialchars((string) ($sidebarData['downloadBannerUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><img src="<?php echo htmlspecialchars((string) ($sidebarData['downloadBannerImageUrl'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) ($sidebarData['downloadBannerAlt'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"/></a></div>

<?php if (!empty($sidebarData['showServerInfoPanel'])): ?>
<div class="panel panel-sidebar">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo htmlspecialchars((string) ($sidebarData['serverInfoTitle'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h3>
    </div>
    <div class="panel-body">
        <table class="table">
            <?php foreach (($sidebarData['serverInfoRows'] ?? []) as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars((string) ($row['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                <td<?php if (($row['valueStyle'] ?? '') !== ''): ?> style="<?php echo htmlspecialchars((string) $row['valueStyle'], ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>><?php echo htmlspecialchars((string) ($row['value'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php endif; ?>

<?php echo $sidebarData['castleSiegeWidgetHtml'] ?? ''; ?>
