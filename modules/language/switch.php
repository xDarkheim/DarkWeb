<?php

use Darkheim\Domain\Validator;

try {
	if(!config('language_switch_active',true)) throw new Exception(lang('error_62'));
	if(!isset($_GET['to'])) throw new Exception(lang('error_63'));
	if(strlen($_GET['to']) != 2) throw new Exception(lang('error_63'));
	if(!Validator::Alpha($_GET['to'])) throw new Exception(lang('error_63'));
	if(!$handler->switchLanguage($_GET['to'])) throw new Exception(lang('error_65'));
	redirect();
} catch (Exception $ex) {
	if(!config('error_reporting',true)) redirect();
	inline_message('error', $ex->getMessage());
}