<div class="page-title"><span><?php echo \Darkheim\Application\Language\Translator::phrase('module_titles_txt_26', true); ?></span></div>

<div class="panel panel-general">
    <div class="panel-body">
        <form action="" method="post">
            <div class="form-group">
                <label for="contactInput1"><?php echo \Darkheim\Application\Language\Translator::phrase('contactus_txt_1', true); ?></label>
                <input type="email" class="form-control" id="contactInput1" name="contact_email">
            </div>
            <div class="form-group">
                <label for="contactInput2"><?php echo \Darkheim\Application\Language\Translator::phrase('contactus_txt_2', true); ?></label>
                <textarea class="form-control" id="contactInput2" style="height:250px;" name="contact_message"></textarea>
            </div>
            <button type="submit" name="submit" value="submit" class="btn btn-primary"><?php echo \Darkheim\Application\Language\Translator::phrase('contactus_txt_3', true); ?></button>
        </form>
    </div>
</div>

