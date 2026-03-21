<?php
/**
 * AdminCP edit character view.
 *
 * Variables:
 * - string  $charName, $accountId, $accountInfoUrl
 * - array   $classOptions   – array of ['id','label','selected']
 * - string  $level, $zen, $lvlPoints, $pkLevel, $str, $agi, $vit, $ene, $cmd
 * - string|null  $resets, $gresets   – null when column not defined
 * - bool    $hasMasterLevel
 * - string  $mlLevel, $mlExp, $mlPoints
 * - string|null  $mlNextExp   – null when column not defined
 */
?>
<h1 class="page-header">Edit Character: <small><?php echo htmlspecialchars($charName, ENT_QUOTES, 'UTF-8'); ?></small></h1>

<form role="form" method="post">
    <input type="hidden" name="characteredit_name" value="<?php echo htmlspecialchars($charName, ENT_QUOTES, 'UTF-8'); ?>"/>
    <input type="hidden" name="characteredit_account" value="<?php echo htmlspecialchars($accountId, ENT_QUOTES, 'UTF-8'); ?>"/>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">Common</div>
                <div class="panel-body">
                    <table class="table table-no-border table-hover">
                        <tr>
                            <th>Account:</th>
                            <td><a href="<?php echo htmlspecialchars($accountInfoUrl, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($accountId, ENT_QUOTES, 'UTF-8'); ?></a></td>
                        </tr>
                        <tr>
                            <th>Class:</th>
                            <td>
                                <select class="form-control" name="characteredit_class">
                                    <?php foreach ($classOptions as $opt): ?>
                                    <option value="<?php echo htmlspecialchars($opt['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $opt['selected'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($opt['label'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr><th>Level:</th><td><input class="form-control" type="number" name="characteredit_level" value="<?php echo htmlspecialchars($level, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                        <?php if ($resets !== null): ?>
                        <tr><th>Resets:</th><td><input class="form-control" type="number" name="characteredit_resets" value="<?php echo htmlspecialchars($resets, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                        <?php endif; ?>
                        <?php if ($gresets !== null): ?>
                        <tr><th>Grand Resets:</th><td><input class="form-control" type="number" name="characteredit_gresets" value="<?php echo htmlspecialchars($gresets, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                        <?php endif; ?>
                        <tr><th>Money:</th><td><input class="form-control" type="number" name="characteredit_zen" value="<?php echo htmlspecialchars($zen, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                        <tr><th>Level-Up Points:</th><td><input class="form-control" type="number" name="characteredit_lvlpoints" value="<?php echo htmlspecialchars($lvlPoints, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                        <tr><th>PK Level:</th><td><input class="form-control" type="number" name="characteredit_pklevel" value="<?php echo htmlspecialchars($pkLevel, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">Stats</div>
                <div class="panel-body">
                    <table class="table table-no-border table-hover">
                        <tr><th>Strength:</th><td><input class="form-control" type="number" name="characteredit_str" value="<?php echo htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                        <tr><th>Dexterity:</th><td><input class="form-control" type="number" name="characteredit_agi" value="<?php echo htmlspecialchars($agi, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                        <tr><th>Vitality:</th><td><input class="form-control" type="number" name="characteredit_vit" value="<?php echo htmlspecialchars($vit, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                        <tr><th>Energy:</th><td><input class="form-control" type="number" name="characteredit_ene" value="<?php echo htmlspecialchars($ene, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                        <tr><th>Command:</th><td><input class="form-control" type="number" name="characteredit_cmd" value="<?php echo htmlspecialchars($cmd, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                    </table>
                </div>
            </div>

            <?php if ($hasMasterLevel): ?>
            <div class="panel panel-default">
                <div class="panel-heading">Master Level</div>
                <div class="panel-body">
                    <table class="table table-no-border table-hover">
                        <tr><th>Master Level:</th><td><input class="form-control" type="number" name="characteredit_mlevel" value="<?php echo htmlspecialchars($mlLevel, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                        <tr><th>Experience:</th><td><input class="form-control" type="number" name="characteredit_mlexp" value="<?php echo htmlspecialchars($mlExp, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                        <?php if ($mlNextExp !== null): ?>
                        <tr><th>Next Experience:</th><td><input class="form-control" type="number" name="characteredit_mlnextexp" value="<?php echo htmlspecialchars($mlNextExp, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                        <?php endif; ?>
                        <tr><th>Points:</th><td><input class="form-control" type="number" name="characteredit_mlpoint" value="<?php echo htmlspecialchars($mlPoints, ENT_QUOTES, 'UTF-8'); ?>"/></td></tr>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-large btn-block btn-success" name="characteredit_submit" value="ok">Save Changes</button>
        </div>
    </div>
</form>

