<?php
	error_reporting(E_ALL  & ~E_NOTICE);
	define('PUBLIC_AUTHORIZE', true);
	
	define("GET_DBKEY_FROM_URL", true);
	
	if ( preg_match('~filename~', $_SERVER['REQUEST_URI'] )) {
		include "getfullsize.php";
		exit;
	}
		
	include_once("_screen.init.php");
	
	try {
		$front = new FrontController();
		$front->setParseType(FrontController::PARSE_TYPE_FRONT);
		$front->setControllerPath('./controllers');
		$front->run();
	}
	catch ( RuntimeException $e) {
		ob_end_clean();
		header("HTTP/1.0 404 Not Found");
        header("Status: 404 Not Found");
        echo "404 page not found";
		die();		        
	}
	
?>
