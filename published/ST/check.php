<?php

if ('cli' == php_sapi_name()) {
	define('PUBLIC_AUTHORIZE', true);
	include_once(dirname(__FILE__).'/config/init.php');
	
	$check_controller = new STRequestsCheckController();
	$check_controller->exec();
}