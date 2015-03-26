<?php 
	define('PUBLIC_AUTHORIZE', true);
	
	define("GET_DBKEY_FROM_URL", true);
	
	include_once("_screen.init.php");
	
	$file_name = Env::Get('filename', Env::TYPE_STRING);
	
	$file_name = base64_decode( rawurldecode( $file_name ) );
	
	$hash = Env::Get('hash', Env::TYPE_STRING);
	
	$db_key = WBS::getDbkeyObj()->getDbkey();

    if ( Wbs::isHosted() ) {
		$metric = metric::getInstance();
 		$metric->addAction(Wbs::getDbkeyObj()->getDbkey(), User::getId(), 'PD', 'DOWNLOADORIGINAL', 'FRONTEND');
	}
	
	$imageModel = new PDImage();
	
	$row = $imageModel->getImageByName($file_name);

	if ( $row  ) {
	    
	    $sash_check = md5( $row['PL_ID'] . $row ['PL_UPLOADDATETIME'] );
	    
	    if ( $hash == $sash_check ) {
	        
	        $path = '../../data/'.$db_key.'/attachments/pd/files/'.$row['PF_ID'].'/'.$row['PL_DISKFILENAME'];
	        
			header( 'Content-type: image/gif' );
	        readfile($path);
	        exit;
	    }
	    
	}

	header("HTTP/1.0 404 Not Found");
    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    print "404 Not Found";
	exit;
?>