<?php
	if ( !isset( $_POST["DB_KEY"] ) && !isset( $_GET["DB_KEY"] ) )
		die( "No valid DB KEY detected." );


	$get_key_from_url = true;

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QP/qp.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QP";

	$currentUser = "";

	//
	// Page variables setup
	//

	$language = LANG_ENG;

	$kernelStrings = $loc_str[$language];
	$qpStrings = $qp_loc_str[$language];

	$invalidField = null;

	$DB_KEY=base64_decode( $DB_KEY);


	$commonRedirParams = array();

	$commonRedirParams['currentBookID'] = base64_encode( $currentBookID );

	session_start();

	$errorStr = "";
	switch ($edited)
	{
		case '1':

			$loginData = $userdata;
			$loginData["U_PASSWORD"] =  md5($loginData["U_PASSWORD"]);

			$loginStatus = checkUserLoginInfo( $loginData, $fase, $kernelStrings );

			if ( $loginStatus == LRC_INVALIDUSER )
				$errorStr =  $kernelStrings['app_invlogindata_message'];
			else
			if ( $loginStatus == LRC_INACTIVEUSER )
				$errorStr =  $kernelStrings['app_notactivelogin_message'];
			else
			if ( $loginStatus == ST_INVALIDDA )
				$errorStr =  $kernelStrings['app_invdirectaccess_message'];


			if ( $errorStr == "" )
			{
				$_SESSION["QPPUBL_DBKEY"] = $DB_KEY;
				$_SESSION["QPPUBL_TEXTID"] = $BookID;
				$_SESSION["QPPUBL_UID"] = $userdata["U_ID"];

				redirectBrowser( "book.php", array( "DB_KEY"=>base64_encode( $DB_KEY ), "BookID"=>$BookID ) );
				die();
			}

			break;
	}

	switch (true) {
		case true :
					if ( $fatalError )
						break;
	}

	$styleSet = "office";
	$preproc = new php_preprocessor( "qppublic", $kernelStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, $qpStrings['pub_screen_title'] );
	$preproc->assign( FORM_LINK, PAGE_QP_PUBLISH );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	$preproc->assign( "BookID", $BookID );


	$preproc->display( "login.htm" );
?>