<?php
/**
 * UserCP buy zen view.
 *
 * Variables provided by BuyZenSubpageController:
 * - string $pageTitle
 * - string $cardTitle
 * - array<int,array{value:string,label:string}> $characterOptions
 * - array<int,array{credits:int,zen:int,label:string}> $buyOptions
 * - string $submitLabel
 */
?>

<div class="page-title"><span><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></span></div>

<div class="ucp-card">
    <div class="ucp-card-header"><i class="bi bi-coin"></i><?php echo htmlspecialchars($cardTitle, ENT_QUOTES, 'UTF-8'); ?></div>
    <div class="ucp-card-body">
        <form action="" method="post">
            <div class="ucp-buyzen-grid">
                <div class="ucp-form-group">
                    <label for="buyzen_character"><?php echo lang('buyzen_txt_3', true); ?></label>
                    <select name="character" id="buyzen_character" class="form-control">
                        <?php foreach ($characterOptions as $character): ?>
                        <option value="<?php echo htmlspecialchars($character['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($character['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ucp-form-group">
                    <label for="buyzen_credits"><?php echo lang('buyzen_txt_4', true); ?></label>
                    <select name="credits" id="buyzen_credits" class="form-control">
                        <?php foreach ($buyOptions as $option): ?>
                        <option value="<?php echo $option['credits']; ?>"><?php echo htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ucp-form-group ucp-buyzen-submit">
                    <label>&nbsp;</label>
                    <button name="submit" value="submit" class="btn btn-primary" style="width:100%;"><?php echo htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>
