<?php
/**
 * AdminCP add news view.
 * No data variables — TinyMCE form only.
 */
?>
<h1 class="page-header"><i class="bi bi-plus-circle me-2"></i>Publish News</h1>

<div class="acp-card">
    <div class="acp-card-header">New Article</div>
    <form role="form" method="post" class="p-3">
        <div class="form-group">
            <label>Title</label>
            <input type="text" class="form-control" name="news_title" required/>
        </div>
        <div class="form-group">
            <label for="news_content">Content</label>
            <textarea name="news_content" id="news_content"></textarea>
        </div>
        <div class="form-group">
            <label>Author</label>
            <input type="text" class="form-control" name="news_author" value="Administrator"/>
        </div>
        <button type="submit" class="btn btn-primary" name="news_submit" value="ok">
            <i class="bi bi-send me-1"></i>Publish
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    tinymce.init({
        selector: '#news_content',
        plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons accordion',
        menubar: 'file edit view insert format tools table help',
        toolbar: 'undo redo | accordion accordionremove | blocks fontfamily fontsize | bold italic underline strikethrough | align numlist bullist | link image | table media | lineheight outdent indent| forecolor backcolor removeformat | charmap emoticons | code fullscreen preview | save print | pagebreak anchor codesample | ltr rtl',
        promotion: false, license_key: 'gpl', toolbar_mode: 'sliding',
        contextmenu: 'link image table',
        skin: 'oxide-dark', content_css: 'dark',
    });
});
</script>

