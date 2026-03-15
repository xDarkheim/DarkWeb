<?php
if(empty($_REQUEST['subpage'])) {
	redirect(1,$_REQUEST['page'].'/'.mconfig('rankings_show_default').'/');
}