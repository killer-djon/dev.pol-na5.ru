<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/MM/mm.php" );
	require_once( "../../../common/html/includes/controls/varlistcontrol.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "MM";

	pageUserAuthorization( $SCR_ID, $MM_APP_ID, false );


	require_once( "../../../common/html/includes/modules/JsHttpRequest/config.php" );
	require_once( "../../../common/html/includes/modules/JsHttpRequest/Php.php" );

	$JsHttpRequest =& new Subsys_JsHttpRequest_Php('windows-1251');

	$DATE = $_POST['DATE'];
	$TIME = $_POST['TIME'];
	$FORMAT = $_POST['FORMAT'];

	define( "DB_DEF_DATE_FORMAT", $FORMAT );
	define( "DATE_DISPLAY_FORMAT", DB_DEF_DATE_FORMAT );


	switch( true )
	{
		case true:

			$whentimestamp = 0;

			if ( !( validateInputDate( $DATE, $whentimestamp, false ) ) || !isTimeStr( $TIME ) )
			{
				$_RESULT = array( "state"=>'ERROR', "error"=>'' );
				break;
			}

			$parts = explode( ":", $TIME  );

			$whentimestamp = $whentimestamp + $parts[0]*3600 + $parts[1]*60;

			if ( $whentimestamp <= convertTimestamp2Local( time() ) )
			{
				$_RESULT = array( "state"=>'ERROR', "error"=>''  );
				break;
			}

			$_RESULT = array( "state"=>'OK', "error"=>''  );
	}

?>

