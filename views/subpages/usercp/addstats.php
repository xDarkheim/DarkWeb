<?php
/**
 * UserCP add stats view.
 *
 * Variables provided by AddStatsSubpageController:
 * - string $pageTitle
 * - int $maxStats
 * - array<int,array{name:string,availablePoints:string,avatarHtml:string,strength:int,agility:int,vitality:int,energy:int,command:int,showCommand:bool}> $characters
 * - array<int,string> $requirementsLines
 */
?>

<div class="page-title"><span><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></span></div>

<?php foreach ($characters as $index => $character): ?>
<div class="ucp-card" style="margin-bottom:16px;">
    <div class="ucp-card-header">
        <i class="bi bi-bar-chart-fill"></i>
        <span style="color:var(--ucp-text);font-weight:700;margin-left:4px;"><?php echo htmlspecialchars($character['name'], ENT_QUOTES, 'UTF-8'); ?></span>
        <span style="margin-left:auto;font-size:11px;color:var(--ucp-text-muted);"><?php echo \Darkheim\Application\Language\Translator::phraseFmt('addstats_txt_2', [$character['availablePoints']]); ?></span>
    </div>
    <div class="ucp-card-body">
        <div class="ucp-addstats-row">
            <div class="ucp-addstats-avatar">
                <?php echo $character['avatarHtml']; ?>
            </div>

            <div class="ucp-addstats-form">
                <form class="ucp-form ucp-stats-grid" action="" method="post">
                    <input type="hidden" name="character" value="<?php echo htmlspecialchars($character['name'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="ucp-form-group">
                        <label for="addstats_str_<?php echo $index; ?>"><?php echo \Darkheim\Application\Language\Translator::phrase('addstats_txt_3', true); ?> <span class="ucp-stat-current">(<?php echo $character['strength']; ?>)</span></label>
                        <input type="number" class="form-control" id="addstats_str_<?php echo $index; ?>" min="1" step="1" max="<?php echo $maxStats; ?>" name="add_str" placeholder="0">
                    </div>

                    <div class="ucp-form-group">
                        <label for="addstats_agi_<?php echo $index; ?>"><?php echo \Darkheim\Application\Language\Translator::phrase('addstats_txt_4', true); ?> <span class="ucp-stat-current">(<?php echo $character['agility']; ?>)</span></label>
                        <input type="number" class="form-control" id="addstats_agi_<?php echo $index; ?>" min="1" step="1" max="<?php echo $maxStats; ?>" name="add_agi" placeholder="0">
                    </div>

                    <div class="ucp-form-group">
                        <label for="addstats_vit_<?php echo $index; ?>"><?php echo \Darkheim\Application\Language\Translator::phrase('addstats_txt_5', true); ?> <span class="ucp-stat-current">(<?php echo $character['vitality']; ?>)</span></label>
                        <input type="number" class="form-control" id="addstats_vit_<?php echo $index; ?>" min="1" step="1" max="<?php echo $maxStats; ?>" name="add_vit" placeholder="0">
                    </div>

                    <div class="ucp-form-group">
                        <label for="addstats_ene_<?php echo $index; ?>"><?php echo \Darkheim\Application\Language\Translator::phrase('addstats_txt_6', true); ?> <span class="ucp-stat-current">(<?php echo $character['energy']; ?>)</span></label>
                        <input type="number" class="form-control" id="addstats_ene_<?php echo $index; ?>" min="1" step="1" max="<?php echo $maxStats; ?>" name="add_ene" placeholder="0">
                    </div>

                    <?php if ($character['showCommand']): ?>
                    <div class="ucp-form-group">
                        <label for="addstats_cmd_<?php echo $index; ?>"><?php echo \Darkheim\Application\Language\Translator::phrase('addstats_txt_7', true); ?> <span class="ucp-stat-current">(<?php echo $character['command']; ?>)</span></label>
                        <input type="number" class="form-control" id="addstats_cmd_<?php echo $index; ?>" min="1" step="1" max="<?php echo $maxStats; ?>" name="add_com" placeholder="0">
                    </div>
                    <?php endif; ?>

                    <div class="ucp-form-submit" style="grid-column:1/-1;">
                        <button name="submit" value="submit" class="btn btn-primary"><?php echo \Darkheim\Application\Language\Translator::phrase('addstats_txt_8', true); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php if ($requirementsLines !== []): ?>
<div class="module-requirements text-center">
    <?php foreach ($requirementsLines as $line): ?>
    <p><?php echo $line; ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>
