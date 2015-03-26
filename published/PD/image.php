<?php
	error_reporting(E_ALL  & ~E_NOTICE);	

	define('PUBLIC_AUTHORIZE', true);

	$start = microtime(true);
	define("GET_DBKEY_FROM_URL", true);
 	include_once("_screen.init.php");
	
	PDApplication::$noLocale = true;
	$pdapp = PDApplication::getInstance();
	
	
	$imageThumb = new ImageThumber( $_GET );
	$imageThumb->setPublicdataPath( $pdapp->getPublicdataPath() );
	$imageThumb->setAttachmentsPath( $pdapp->getAttachmentsPath() );
	
	$imageThumb->outputImage_();
	
	//added metrics for WG
	if ( Wbs::isHosted() ) {
		if ($client = Env::Get('client', Env::TYPE_STRING_TRIM) ) {
			$size = Env::Get('size', Env::TYPE_INT);
			switch ($client) {
		    	case 'Gallery':
		        	$CLIENT_TYPE = 'WG-GALLERY';
					break;
		        case 'Album':
		          	$CLIENT_TYPE = 'WG-SLIDESHOW';
		          	break;
		        case 'Link':  
		    		$CLIENT_TYPE = 'LINK';
		    		break;
		        default: 
		        	$CLIENT_TYPE = 'WG-UNKNOWN';
					break;
			}
			
			$metric = metric::getInstance();
 			$metric->addAction(Wbs::getDbkeyObj()->getDbkey(), User::getId(), 'PD', 'DOWNLOAD', $CLIENT_TYPE, $size);
		}
	}	
?>