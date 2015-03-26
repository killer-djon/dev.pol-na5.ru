<?
	$loginScript = true;
	require_once( "../../../common/html/includes/httpinit.php" );
	
	$kernelStrings = $loc_str[$language];
	
	$loginData = array ();
	
	if (process_htmllogin( $kernelStrings, $loginData, false, null, false, $noEnter = true)) {
		print "OK";	
	} else {
		print "Error";	
	}
?>