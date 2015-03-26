<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/QP/qp.php" );
	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QP";

	pageUserAuthorization( $SCR_ID, $QP_APP_ID, false );

	//
	// Page variables setup
	//

	$locStrings = $loc_str[$language];
	$kernelStrings = $loc_str[$language];
	$qpStrings = $qp_loc_str[$language];
	$invalidField = null;

	$currentBookID = base64_decode( $currentBookID );

	switch (true)
	{
		case true :

					$rights = $qp_treeClass->getIdentityFolderRights( $currentUser, $currentBookID, $kernelStrings );

					if ( PEAR::isError($rights) )
					{
						$fatalError = true;
						$errorStr = $rights->getMessage();
						$_SESSION['ENABLE_PREVIEW'] = null;
						$_SESSION['PREVIEW_THEME'] = null;

						break;
					}

					if ( is_null( $currentBookID ) || $currentBookID == "" )
					{
						$fatalError = true;
						$errorStr = $qpStrings['app_norights_book_message'];
						$_SESSION['ENABLE_PREVIEW'] = null;
						$_SESSION['PREVIEW_THEME'] = null;

						break;
					}

					$bookData = $qp_treeClass->getFolderInfo( $currentBookID, $kernelStrings );

					if ( PEAR::isError( $bookData ) )
					{
						$fatalError = true;
						$errorStr = $res->getMessage();
						break;
					}

	}

	$btnIndex = getButtonIndex( array( 'cancelbtn', 'savebtn', 'tree', 'plain', 'setcp' ), $_POST, false );

	$commonRedirParams = array();

	$commonRedirParams['currentBookID'] = base64_encode( $currentBookID );


	switch ($btnIndex)
	{
		case 'savebtn':

				if ( $fatalError )
					break;

				if ( is_null( $currentBookID ) || !UR_RightsObject::CheckMask( $rights, array( TREE_READWRITEFOLDER  )  ) )
				{
						$fatalError = true;
						$errorStr = $qpStrings['app_norights_book_message'];

						break;
				}

				if ( UR_RightsObject::CheckMask( $rights, array( TREE_READWRITEFOLDER  )  ) )
				{

					if ( PEAR::isError( qp_changeBookPublishState( $currentBookID, $state = ( $published == 1 ) ? 1 : 0, $QPB_THEME ) ) )
					{
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
							$fatalError = true;
							break;
					}

					$bookData["QPB_PUBLISHED"] = $state;
					$bookData["QPB_THEME"] = intval( $QPB_THEME );

					if ( is_null( $currentBookID ) || !UR_RightsObject::CheckMask( $rights, array( TREE_READWRITEFOLDER  )  ) )
					{
							$fatalError = true;
							$errorStr = $qpStrings['app_norights_book_message'];

							break;
					}

					$publishParams = new wbsParameters( $qp_book_data_schema );


					$publishParams->loadFromXML( $bookData["QPB_PROPERTIES"], $kernelStrings, true, null );


					if(empty($properties['auth'])) $properties['auth'] = 0;

					$ret = $publishParams->loadFromArray( $properties, $kernelStrings, true, null );

					if ( PEAR::isError( $ret ) )
					{
						$fatalError = true;
						$errorStr = $ret->getMessage();
						break;
					}

					$xml = $publishParams->getValuesXML();

					$res = db_query( $qr_qp_updateBookProperties, array( "QPB_ID" => $currentBookID, "QPB_PROPERTIES" => $xml ) );

					if ( PEAR::isError($res) )
					{
						$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
						$fatalError = true;
						break;
					}
				}

				$bookData["QPB_PROPERTIES"] = $xml;

		case 'cancelbtn' :

					$_SESSION['ENABLE_PREVIEW'] = null;
					$_SESSION['PREVIEW_THEME'] = null;
					redirectBrowser( base64_decode($opener), $commonRedirParams );
					break;

	}

	switch (true) {
		case true :
					if ( $fatalError )
						break;

					$publishParams = new wbsParameters( $qp_book_data_schema );
					$publishParams->loadFromXML( $bookData["QPB_PROPERTIES"], $kernelStrings, true, null );
					$properties = $publishParams->getValuesArray();

					$_SESSION['ENABLE_PREVIEW'] = $currentBookID;

					$url = "DB_KEY=".base64_encode( $DB_KEY )."&BookID=".$bookData["QPB_TEXTID"];

					$urlBook = qp_getBookURL( ).$url;

					$language_names = array();
					$language_ids = array();

					foreach( $wbs_languages as $key=>$value )
					{
						$language_names[] = $value["NAME"];
						$language_ids[] = $value["ID"];
					}

					$themes = qp_getApplicableThemesList( $currentUser, $kernelStrings );

					if ( PEAR::isError( $themes ) )
					{
						$errorStr = $themes->getMesage();
						$fatalError = true;
						break;
					}

					if ( !isset( $edited ) )
						$QPB_THEME = $bookData["QPB_THEME"];

					if ( is_null( $QPB_THEME ) || !in_array( $QPB_THEME, array_keys( $themes ) ) )
						$QPB_THEME = "DEFAULT";
	}

	$preproc = new php_preprocessor( $templateName, $locStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, $qpStrings['pub_screen_title'] );
	$preproc->assign( FORM_LINK, PAGE_QP_PUBLISH );

	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	$preproc->assign( "qpStrings", $qpStrings );

	$preproc->assign( "properties", $properties );

	$preproc->assign( "language_names", $language_names );
	$preproc->assign( "language_ids", $language_ids );

	$preproc->assign( "cpArray", $qp_book_data_schema["codepage"]["OPTIONS"] );

	$preproc->assign( "rights", UR_RightsObject::CheckMask( $rights, array( TREE_READWRITEFOLDER  )  ) );

	$preproc->assign( "urlBook", $urlBook );

	$preproc->assign( "urlHTML", wordwrap( $urlBook, 110, "\n", 1) );

	$preproc->assign( "currentBookID", base64_encode( $currentBookID ) );
	$preproc->assign( "opener", $opener );

	$preproc->assign( "themes", $themes );

	$preproc->assign( "bookData", $bookData );
	$preproc->assign( "published", $bookData["QPB_PUBLISHED"] );
	$preproc->assign( "QPB_THEME", $QPB_THEME );

	$preproc->display( "publish.htm" );

?>