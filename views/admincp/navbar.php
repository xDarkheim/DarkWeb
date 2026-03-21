<?php
/**
 * AdminCP navbar editor view.
 *
 * Variables:
 * - array<int,array{id:string,order:string,active:bool,type:string,link:string,phrase:string,visibility:string,newtab:bool,deleteUrl:string}> $rows
 */
?>
<h1 class="page-header">Navigation Menu</h1>

<table class="table table-condensed table-bordered table-hover table-striped">
    <thead>
    <tr>
        <th></th>
        <th>Order</th>
        <th>Status</th>
        <th>Link Type</th>
        <th>Link</th>
        <th>Phrase</th>
        <th>Visibility</th>
        <th>New Tab</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
    <?php $formId = 'navbar_form_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $row['id']); ?>
    <tr>
        <td class="text-center" style="vertical-align:middle;">
            <a href="<?php echo htmlspecialchars($row['deleteUrl'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-danger btn-xs"><span class="fa fa-times" aria-hidden="true"></span></a>
        </td>
        <td style="max-width:70px;">
            <label class="visually-hidden" for="<?php echo $formId; ?>_order">Order</label>
            <input id="<?php echo $formId; ?>_order" form="<?php echo $formId; ?>" type="text" name="navbar_order" class="form-control" value="<?php echo htmlspecialchars($row['order'], ENT_QUOTES, 'UTF-8'); ?>"/>
        </td>
        <td class="text-center" style="vertical-align:middle;">
            <label class="radio-inline"><input form="<?php echo $formId; ?>" type="radio" name="navbar_status" value="1" <?php echo $row['active'] ? 'checked' : ''; ?>> Show</label>
            <label class="radio-inline"><input form="<?php echo $formId; ?>" type="radio" name="navbar_status" value="0" <?php echo !$row['active'] ? 'checked' : ''; ?>> Hide</label>
        </td>
        <td>
            <label class="visually-hidden" for="<?php echo $formId; ?>_type">Link Type</label>
            <select id="<?php echo $formId; ?>_type" form="<?php echo $formId; ?>" name="navbar_type" class="form-control">
                <option value="internal" <?php echo $row['type'] === 'internal' ? 'selected' : ''; ?>>internal</option>
                <option value="external" <?php echo $row['type'] === 'external' ? 'selected' : ''; ?>>external</option>
            </select>
        </td>
        <td>
            <label class="visually-hidden" for="<?php echo $formId; ?>_link">Link</label>
            <input id="<?php echo $formId; ?>_link" form="<?php echo $formId; ?>" type="text" name="navbar_link" class="form-control" value="<?php echo htmlspecialchars($row['link'], ENT_QUOTES, 'UTF-8'); ?>"/>
        </td>
        <td>
            <label class="visually-hidden" for="<?php echo $formId; ?>_phrase">Phrase</label>
            <input id="<?php echo $formId; ?>_phrase" form="<?php echo $formId; ?>" type="text" name="navbar_phrase" class="form-control" value="<?php echo htmlspecialchars($row['phrase'], ENT_QUOTES, 'UTF-8'); ?>"/>
        </td>
        <td>
            <label class="visually-hidden" for="<?php echo $formId; ?>_visibility">Visibility</label>
            <select id="<?php echo $formId; ?>_visibility" form="<?php echo $formId; ?>" name="navbar_visibility" class="form-control">
                <option value="user" <?php echo $row['visibility'] === 'user' ? 'selected' : ''; ?>>user</option>
                <option value="guest" <?php echo $row['visibility'] === 'guest' ? 'selected' : ''; ?>>guest</option>
                <option value="always" <?php echo $row['visibility'] === 'always' ? 'selected' : ''; ?>>always</option>
            </select>
        </td>
        <td class="text-center" style="vertical-align:middle;">
            <label class="radio-inline"><input form="<?php echo $formId; ?>" type="radio" name="navbar_newtab" value="1" <?php echo $row['newtab'] ? 'checked' : ''; ?>> Yes</label>
            <label class="radio-inline"><input form="<?php echo $formId; ?>" type="radio" name="navbar_newtab" value="0" <?php echo !$row['newtab'] ? 'checked' : ''; ?>> No</label>
        </td>
        <td class="text-center" style="vertical-align:middle;">
            <button form="<?php echo $formId; ?>" type="submit" name="navbar_submit" value="ok" class="btn btn-primary">save</button>
            <form id="<?php echo $formId; ?>" action="?module=navbar" method="post">
                <input type="hidden" name="navbar_id" value="<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>"/>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>

    <?php $newFormId = 'navbar_form_new'; ?>
    <tr><th colspan="9" class="text-center"><br /><br />Add New Element</th></tr>
    <tr>
        <td></td>
        <td style="max-width:70px;">
            <label class="visually-hidden" for="<?php echo $newFormId; ?>_order">Order</label>
            <input id="<?php echo $newFormId; ?>_order" form="<?php echo $newFormId; ?>" type="text" name="navbar_order" class="form-control" value="10"/>
        </td>
        <td class="text-center" style="vertical-align:middle;">
            <label class="radio-inline"><input form="<?php echo $newFormId; ?>" type="radio" name="navbar_status" value="1" checked> Show</label>
            <label class="radio-inline"><input form="<?php echo $newFormId; ?>" type="radio" name="navbar_status" value="0"> Hide</label>
        </td>
        <td>
            <label class="visually-hidden" for="<?php echo $newFormId; ?>_type">Link Type</label>
            <select id="<?php echo $newFormId; ?>_type" form="<?php echo $newFormId; ?>" name="navbar_type" class="form-control">
                <option value="internal" selected>internal</option>
                <option value="external">external</option>
            </select>
        </td>
        <td>
            <label class="visually-hidden" for="<?php echo $newFormId; ?>_link">Link</label>
            <input id="<?php echo $newFormId; ?>_link" form="<?php echo $newFormId; ?>" type="text" name="navbar_link" class="form-control" placeholder="rankings/resets"/>
        </td>
        <td>
            <label class="visually-hidden" for="<?php echo $newFormId; ?>_phrase">Phrase</label>
            <input id="<?php echo $newFormId; ?>_phrase" form="<?php echo $newFormId; ?>" type="text" name="navbar_phrase" class="form-control" placeholder="lang_phrase_x"/>
        </td>
        <td>
            <label class="visually-hidden" for="<?php echo $newFormId; ?>_visibility">Visibility</label>
            <select id="<?php echo $newFormId; ?>_visibility" form="<?php echo $newFormId; ?>" name="navbar_visibility" class="form-control">
                <option value="user" selected>user</option>
                <option value="guest">guest</option>
                <option value="always">always</option>
            </select>
        </td>
        <td class="text-center" style="vertical-align:middle;">
            <label class="radio-inline"><input form="<?php echo $newFormId; ?>" type="radio" name="navbar_newtab" value="1"> Yes</label>
            <label class="radio-inline"><input form="<?php echo $newFormId; ?>" type="radio" name="navbar_newtab" value="0" checked> No</label>
        </td>
        <td class="text-center" style="vertical-align:middle;">
            <button form="<?php echo $newFormId; ?>" type="submit" name="new_submit" value="ok" class="btn btn-success">add</button>
            <form id="<?php echo $newFormId; ?>" action="?module=navbar" method="post"></form>
        </td>
    </tr>
    </tbody>
</table>

