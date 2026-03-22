<?php
/**
 * AdminCP account info view.
 *
 * Variables:
 * - array       $account            – ['id','username','email','isBanned']
 * - array|null  $status             – ['isOnline','server'] or null
 * - array       $characters         – array of ['name','editUrl']
 * - array|null  $muLogExIps         – array of IP strings or null (feature unavailable)
 * - array|null  $connectionIps      – array of IP strings or null
 * - array|null  $connectionHistory  – array of ['date','server','ip','hwid'] or null
 * - bool        $hasMuLogEx
 * - bool        $hasConnectionHistory
 */
?>
<h1 class="page-header">
    Account Information: <small><?php echo htmlspecialchars($account['username'], ENT_QUOTES, 'UTF-8'); ?></small>
</h1>

<div class="row">
    <div class="col-md-6">

        <!-- General Information -->
        <div class="panel panel-primary">
            <div class="panel-heading">General Information</div>
            <div class="panel-body">
                <table class="table table-no-border table-hover">
                    <tr><th>ID:</th><td><?php echo htmlspecialchars($account['id'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><th>Username:</th><td><?php echo htmlspecialchars($account['username'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><th>Email:</th><td><?php echo htmlspecialchars($account['email'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr>
                        <th>Banned:</th>
                        <td>
                            <?php if ($account['isBanned']): ?>
                                <span class="label label-danger">Banned</span>
                            <?php else: ?>
                                <span class="label label-success">Active</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Status Information -->
        <div class="panel panel-info">
            <div class="panel-heading">Status Information</div>
            <div class="panel-body">
                <?php if ($status !== null): ?>
                <table class="table table-no-border table-hover">
                    <tr>
                        <td>Status:</td>
                        <td><?php echo $status['isOnline'] ? '<span class="label label-success">Online</span>' : '<span class="label label-danger">Offline</span>'; ?></td>
                    </tr>
                    <tr><td>Server:</td><td><?php echo htmlspecialchars($status['server'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                </table>
                <?php else: ?>
                    <?php \Darkheim\Application\View\MessageRenderer::inline('info', 'No data found in ' . _TBL_MS_ . ' for this account.'); ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Characters -->
        <div class="panel panel-default">
            <div class="panel-heading">Characters</div>
            <div class="panel-body">
                <?php if ($characters !== []): ?>
                <table class="table table-no-border table-hover">
                    <?php foreach ($characters as $char): ?>
                    <tr>
                        <td>
                            <a href="<?php echo htmlspecialchars($char['editUrl'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($char['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                    <?php \Darkheim\Application\View\MessageRenderer::inline('info', 'No characters found.'); ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Change Password -->
        <div class="panel panel-default">
            <div class="panel-heading">Change Account's Password</div>
            <div class="panel-body">
                <form role="form" method="post">
                    <input type="hidden" name="action" value="changepassword"/>
                    <div class="form-group">
                        <label for="input_1">New Password:</label>
                        <input type="text" class="form-control" id="input_1" name="changepassword_newpw" placeholder="New password">
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="editaccount_sendmail" value="1" checked> Email the user about this change.</label>
                    </div>
                    <button type="submit" name="editaccount_submit" class="btn btn-success" value="ok">Change Password</button>
                </form>
            </div>
        </div>

        <!-- Change Email -->
        <div class="panel panel-default">
            <div class="panel-heading">Change Account's Email</div>
            <div class="panel-body">
                <form role="form" method="post">
                    <input type="hidden" name="action" value="changeemail"/>
                    <div class="form-group">
                        <label for="input_2">New Email:</label>
                        <input type="email" class="form-control" id="input_2" name="changeemail_newemail" placeholder="New email address">
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="editaccount_sendmail" value="1" checked> Email the user about this change.</label>
                    </div>
                    <button type="submit" name="editaccount_submit" class="btn btn-success" value="ok">Change Email</button>
                </form>
            </div>
        </div>

    </div>

    <div class="col-md-6">

        <!-- MuLogEx IPs -->
        <?php if ($hasMuLogEx): ?>
        <div class="panel panel-default">
            <div class="panel-heading">Account's IP Address (MuEngine)</div>
            <div class="panel-body">
                <?php if ($muLogExIps !== null && $muLogExIps !== []): ?>
                <table class="table table-no-border table-hover">
                    <?php foreach ($muLogExIps as $ip): ?>
                    <tr>
                        <td>
                            <a href="https://whatismyipaddress.com/ip/<?php echo urlencode($ip); ?>" target="_blank">
                                <?php echo htmlspecialchars($ip, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                    <?php \Darkheim\Application\View\MessageRenderer::inline('info', 'No IP address found.'); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Connection IPs -->
        <?php if ($hasConnectionHistory): ?>
        <div class="panel panel-default">
            <div class="panel-heading">Account's IP Address</div>
            <div class="panel-body">
                <?php if ($connectionIps !== null && $connectionIps !== []): ?>
                <table class="table table-no-border table-hover">
                    <?php foreach ($connectionIps as $ip): ?>
                    <tr>
                        <td>
                            <a href="https://whatismyipaddress.com/ip/<?php echo urlencode($ip); ?>" target="_blank">
                                <?php echo htmlspecialchars($ip, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                    <?php \Darkheim\Application\View\MessageRenderer::inline('info', 'No IP addresses found in the database.'); ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Connection History -->
        <div class="panel panel-default">
            <div class="panel-heading">Account Connection History (last 25)</div>
            <div class="panel-body">
                <?php if ($connectionHistory !== null && $connectionHistory !== []): ?>
                <table class="table table-no-border table-hover">
                    <tr><th>Date</th><th class="hidden-xs">Server</th><th>IP</th><th>HWID</th></tr>
                    <?php foreach ($connectionHistory as $conn): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($conn['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="hidden-xs"><?php echo htmlspecialchars($conn['server'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($conn['ip'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($conn['hwid'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                    <?php \Darkheim\Application\View\MessageRenderer::inline('info', 'No connection history found for account.'); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

