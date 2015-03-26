<?php
	error_reporting(E_ALL  & ~E_NOTICE);
	include_once("_screen.init.php");
	
    if ( !extension_loaded( "imagick" ) && !extension_loaded( "gd" ) ) {
        die ( 'Neither GD nor Imagick are supported by this server. WebAsyst Photos will not work. See system requirements.' );
    }
    	
	$front = new FrontController();
	$front->setControllerPath('./controllers');
	$front->run();
    
?>