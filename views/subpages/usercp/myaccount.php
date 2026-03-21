<?php
/**
 * UserCP account overview view.
 *
 * Variables provided by MyAccountSubpageController:
 * - string $username
 * - string $statusPillClass
 * - string $statusPillText
 * - string $onlinePillClass
 * - string $onlinePillText
 * - string $email
 * - array<int,array{title:string,amount:string}> $creditRows
 * - string $myEmailUrl
 * - string $myPasswordUrl
 * - array<int,array{isOnline:bool,profileUrl:string,avatarUrl:string,nameHtml:string,className:string,level:int,location:string}> $characterCards
 * - bool   $hasCharacters
 * - string $emptyCharactersMessage
 * - bool   $hasConnectionHistory
 * - array<int,array{date:string,server:string,ip:string,state:string}> $connectionHistoryRows
 */
?>

<div class="ma-banner">
    <div class="ma-banner-inner">
        <div class="ma-avatar"><i class="bi bi-person-fill"></i></div>
        <div class="ma-banner-info">
            <div class="ma-username"><?php echo $username; ?></div>
            <div class="ma-pills">
                <span class="ma-status-pill <?php echo $statusPillClass; ?>"><?php echo $statusPillText; ?></span>
                <span class="ma-online-pill <?php echo $onlinePillClass; ?>"><?php echo $onlinePillText; ?></span>
            </div>
        </div>
        <div class="ma-banner-actions">
            <a href="<?php echo $myEmailUrl; ?>" class="ma-action-btn"><i class="bi bi-envelope-fill"></i> <?php echo lang('myaccount_txt_3'); ?></a>
            <a href="<?php echo $myPasswordUrl; ?>" class="ma-action-btn"><i class="bi bi-key-fill"></i> <?php echo lang('myaccount_txt_4'); ?></a>
        </div>
    </div>
</div>

<div class="ma-info-strip">
    <div class="ma-info-cell">
        <span class="ma-info-label"><i class="bi bi-envelope"></i> <?php echo lang('myaccount_txt_3'); ?></span>
        <span class="ma-info-value"><?php echo $email; ?></span>
    </div>
    <div class="ma-info-cell">
        <span class="ma-info-label"><i class="bi bi-shield-lock"></i> <?php echo lang('myaccount_txt_4'); ?></span>
        <span class="ma-info-value ma-dots">&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;</span>
    </div>
    <?php foreach ($creditRows as $row): ?>
    <div class="ma-info-cell">
        <span class="ma-info-label"><i class="bi bi-coin"></i> <?php echo $row['title']; ?></span>
        <span class="ma-info-value ma-credits"><?php echo $row['amount']; ?></span>
    </div>
    <?php endforeach; ?>
</div>

<div class="ma-section-title"><i class="bi bi-person-badge-fill"></i><?php echo lang('myaccount_txt_15'); ?></div>

<?php if ($hasCharacters): ?>
<div class="ma-chars-grid">
    <?php foreach ($characterCards as $card): ?>
    <div class="ma-char-card<?php echo $card['isOnline'] ? ' ma-char-online' : ''; ?>">
        <span class="ma-char-dot<?php echo $card['isOnline'] ? ' dot-online' : ' dot-offline'; ?>"></span>
        <a href="<?php echo $card['profileUrl']; ?>" target="_blank" class="ma-char-avatar-wrap">
            <img src="<?php echo $card['avatarUrl']; ?>" alt="" class="ma-char-avatar">
        </a>
        <div class="ma-char-name"><?php echo $card['nameHtml']; ?></div>
        <?php if ($card['className'] !== ''): ?><div class="ma-char-class"><?php echo htmlspecialchars($card['className'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        <div class="ma-char-lvl">LVL <strong><?php echo $card['level']; ?></strong></div>
        <div class="ma-char-loc"><i class="bi bi-geo-alt-fill"></i><?php echo htmlspecialchars($card['location'], ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="module-requirements text-center"><p><?php echo htmlspecialchars($emptyCharactersMessage, ENT_QUOTES, 'UTF-8'); ?></p></div>
<?php endif; ?>

<?php if ($hasConnectionHistory): ?>
<div class="ma-section-title" style="margin-top:24px;"><i class="bi bi-clock-history"></i><?php echo lang('myaccount_txt_16'); ?></div>
<div class="ucp-card">
    <div class="ucp-card-body" style="padding:0;">
        <table class="table general-table-ui" style="margin-bottom:0;">
            <thead>
                <tr>
                    <th><?php echo lang('myaccount_txt_13'); ?></th>
                    <th><?php echo lang('myaccount_txt_17'); ?></th>
                    <th><?php echo lang('myaccount_txt_18'); ?></th>
                    <th><?php echo lang('myaccount_txt_19'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($connectionHistoryRows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['server'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['ip'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['state'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

