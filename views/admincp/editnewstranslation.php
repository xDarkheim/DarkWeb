<?php
/**
 * AdminCP edit news translation view.
 *
 * Variables:
 * - string $newsId
 * - string $language
 * - string $formTitle
 * - string $formContent
 */
?>
<h1 class="page-header">Edit News Translation</h1>

<form role="form" method="post">
    <input type="hidden" name="news_id" value="<?php echo htmlspecialchars($newsId, ENT_QUOTES, 'UTF-8'); ?>"/>
    <input type="hidden" name="news_language" value="<?php echo htmlspecialchars($language, ENT_QUOTES, 'UTF-8'); ?>"/>

    <div class="form-group">
        <label for="input_1">Language:</label>
        <select class="form-control" id="input_1" disabled>
            <option><?php echo htmlspecialchars($language, ENT_QUOTES, 'UTF-8'); ?></option>
        </select>
    </div>
    <div class="form-group">
        <label for="input_2">Title:</label>
        <input type="text" class="form-control" id="input_2" name="news_title"
               value="<?php echo htmlspecialchars($formTitle, ENT_QUOTES, 'UTF-8'); ?>"/>
    </div>
    <div class="form-group">
        <label for="news_content">Content</label>
        <textarea name="news_content" id="news_content"><?php echo htmlspecialchars($formContent, ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>
    <button type="submit" class="btn btn-large btn-block btn-warning" name="news_submit" value="ok">Update News Translation</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    tinymce.init({
        selector: '#news_content',
        plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons accordion',
        menubar: 'file edit view insert format tools table help',
        toolbar: 'undo redo | accordion accordionremove | blocks fontfamily fontsize | bold italic underline strikethrough | align numlist bullist | link image | table media | lineheight outdent indent| forecolor backcolor removeformat | charmap emoticons | code fullscreen preview | save print | pagebreak anchor codesample | ltr rtl',
        promotion: false, license_key: 'gpl', toolbar_mode: 'sliding',
        contextmenu: 'link image table',
        skin: dark ? 'oxide-dark' : 'oxide', content_css: dark ? 'dark' : 'default',
    });
});
</script>

