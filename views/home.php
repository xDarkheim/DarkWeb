<?php
/**
 * Home page view.
 *
 * Variables provided by HomeController:
 * - array<int,array{title:string,url:string,date:string}> $newsItems
 * - bool   $hasNews
 * - array<int,array{name:string,class:string,level:string}> $topLevelData
 * - array<int,array{name:string,logo:string,score:string}> $topGuildData
 * - bool   $userLoggedIn
 * - string $usercpMenuHtml
 * - string $baseUrl
 * - string $rankLevelUrl
 * - string $rankGuildsUrl
 * - string $sidebarBanner
 */
?>

<div class="row">
    <div class="col-xs-12 col-sm-8 home-news-block">
        <div class="home-news-header">
            <div class="home-news-header-left">
                <i class="bi bi-newspaper home-news-header-icon"></i>
                <span class="home-news-header-title"><?php echo lang('news_txt_4'); ?></span>
            </div>
            <a href="<?php echo $baseUrl; ?>news/" class="home-news-header-more"><?php echo lang('news_txt_5'); ?> <i class="bi bi-arrow-right"></i></a>
        </div>

        <?php if ($hasNews): ?>
        <div class="home-news-feed">
            <?php foreach ($newsItems as $news): ?>
            <a href="<?php echo $news['url']; ?>" class="home-news-item">
                <span class="home-news-item-badge"><?php echo lang('news_txt_6'); ?></span>
                <span class="home-news-item-title"><?php echo $news['title']; ?></span>
                <span class="home-news-item-date"><i class="bi bi-calendar3"></i><?php echo $news['date']; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="home-news-empty"><i class="bi bi-inbox"></i> No news yet.</div>
        <?php endif; ?>
    </div>

    <div class="col-xs-12 col-sm-4">
        <?php if (!$userLoggedIn): ?>
        <div class="panel panel-sidebar">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo lang('module_titles_txt_2'); ?> <a href="<?php echo $baseUrl; ?>forgotpassword" class="btn btn-primary btn-xs pull-right"><?php echo lang('login_txt_4'); ?></a></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $baseUrl; ?>login" method="post">
                    <div class="form-group"><input type="text" class="form-control" id="loginBox1" name="darkheimLogin_user" required></div>
                    <div class="form-group"><input type="password" class="form-control" id="loginBox2" name="darkheimLogin_pwd" required></div>
                    <button type="submit" name="darkheimLogin_submit" value="submit" class="btn btn-primary"><?php echo lang('login_txt_3'); ?></button>
                </form>
            </div>
        </div>
        <div class="sidebar-banner"><a href="<?php echo $baseUrl; ?>register"><img src="<?php echo $sidebarBanner; ?>" alt="Join"></a></div>
        <?php else: ?>
        <div class="panel panel-sidebar panel-usercp">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo lang('usercp_menu_title'); ?> <a href="<?php echo $baseUrl; ?>logout" class="btn btn-primary btn-xs pull-right"><?php echo lang('login_txt_6'); ?></a></h3>
            </div>
            <div class="panel-body"><?php echo $usercpMenuHtml; ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row" style="margin-top: 20px;">
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-sidebar">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo lang('rankings_txt_1'); ?><a href="<?php echo $rankLevelUrl; ?>" class="btn btn-primary btn-xs pull-right" style="text-align:center;width:22px;">+</a></h3>
            </div>
            <div class="panel-body" style="min-height:400px;">
                <table class="table table-condensed">
                    <thead><tr>
                        <th class="text-center"><?php echo lang('rankings_txt_10'); ?></th>
                        <th class="text-center"><?php echo lang('rankings_txt_11'); ?></th>
                        <th class="text-center"><?php echo lang('rankings_txt_12'); ?></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($topLevelData as $row): ?>
                        <tr>
                            <td class="text-center"><?php echo $row['name']; ?></td>
                            <td class="text-center"><?php echo $row['class']; ?></td>
                            <td class="text-center"><?php echo $row['level']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-sidebar">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo lang('rankings_txt_4'); ?><a href="<?php echo $rankGuildsUrl; ?>" class="btn btn-primary btn-xs pull-right" style="text-align:center;width:22px;">+</a></h3>
            </div>
            <div class="panel-body" style="min-height:400px;">
                <table class="table table-condensed">
                    <thead><tr>
                        <th class="text-center"><?php echo lang('rankings_txt_17'); ?></th>
                        <th class="text-center"><?php echo lang('rankings_txt_28'); ?></th>
                        <th class="text-center"><?php echo lang('rankings_txt_19'); ?></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($topGuildData as $row): ?>
                        <tr>
                            <td class="text-center"><?php echo $row['name']; ?></td>
                            <td class="text-center"><?php echo $row['logo']; ?></td>
                            <td class="text-center"><?php echo $row['score']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-sidebar panel-sidebar-events">
            <div class="panel-heading"><h3 class="panel-title"><?php echo lang('event_schedule'); ?></h3></div>
            <div class="panel-body" style="min-height:400px;">
                <table class="table table-condensed">
                    <tr><td><span id="bloodcastle_name"></span><br><span class="smalltext"><?php echo lang('event_schedule_start'); ?></span></td><td class="text-right"><span id="bloodcastle_next"></span><br><span class="smalltext" id="bloodcastle"></span></td></tr>
                    <tr><td><span id="devilsquare_name"></span><br><span class="smalltext"><?php echo lang('event_schedule_start'); ?></span></td><td class="text-right"><span id="devilsquare_next"></span><br><span class="smalltext" id="devilsquare"></span></td></tr>
                    <tr><td><span id="chaoscastle_name"></span><br><span class="smalltext"><?php echo lang('event_schedule_start'); ?></span></td><td class="text-right"><span id="chaoscastle_next"></span><br><span class="smalltext" id="chaoscastle"></span></td></tr>
                    <tr><td><span id="dragoninvasion_name"></span><br><span class="smalltext"><?php echo lang('event_schedule_start'); ?></span></td><td class="text-right"><span id="dragoninvasion_next"></span><br><span class="smalltext" id="dragoninvasion"></span></td></tr>
                    <tr><td><span id="goldeninvasion_name"></span><br><span class="smalltext"><?php echo lang('event_schedule_start'); ?></span></td><td class="text-right"><span id="goldeninvasion_next"></span><br><span class="smalltext" id="goldeninvasion"></span></td></tr>
                    <tr><td><span id="castlesiege_name"></span><br><span class="smalltext"><?php echo lang('event_schedule_start'); ?></span></td><td class="text-right"><span id="castlesiege_next"></span><br><span class="smalltext" id="castlesiege"></span></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
