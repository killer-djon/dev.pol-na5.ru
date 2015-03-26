<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/PM/pm.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "WL";

	pageUserAuthorization( $SCR_ID, $PM_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$pmStrings = $pm_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL, 'importbtn' ), $_POST );

	switch ($btnIndex) {
		case 0 : 
				if ( !isset($file['name']) || !strlen($file['name']) ) {
					$invalidField = 'FILE';

					break;
				}

				$tmpFileName = uniqid( TMP_FILES_PREFIX );
				$destPath = WBS_TEMP_DIR."/".$tmpFileName;

				if ( !@move_uploaded_file( $file['tmp_name'], $destPath ) ) {
					$errorStr = $pmStrings['icl_errfilecopy_message'];
					
					break;
				}

				$separator =  getCSVSeparator( $destPath, $kernelStrings );
				if ( PEAR::isError($separator) ) {
					$errorStr = $separator->getMessage();

					break;
				}

				$headers = getCSVHeaders( $destPath, $separator, $kernelStrings );
				if ( PEAR::isError($headers) ) {
					$errorStr = $headers->getMessage();

					break;
				}

				$importColumn = array();
				foreach ( $headers as $key=>$value )
					$importColumn[$key] = 1;

				$headers_pack = base64_encode( serialize($headers) );

				$tmpFileName = base64_encode($destPath);
				$step = 2;

				break;
		case 1 :
				redirectBrowser( PAGE_PM_CUSTOMERLIST, array() );
		case 2 :
				if ( !isset($importColumn) ) 
					$importColumn = array();

				if ( !isset($importFirstLine) )
					$importFirstLine = 0;

				$file = base64_decode($tmpFileName);

				$res = pm_importCustomers( $currentUser, $file, $importFirstLine, array_keys($importColumn), $nameMap, $kernelStrings, $pmStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();
					$invalidField = $res->getUserInfo();

					break;
				}

				redirectBrowser( PAGE_PM_CUSTOMERLIST, array() );
	}

	switch (true) {
		case true : 
					if ( !isset($edited) ) 
						$step = 1;

					foreach( $pm_importColumnNames as $key=>$value )
						$pm_importColumnNames[$key] = $pmStrings[$value];

					$columnNames = array_merge( array($pmStrings['icl_select_item']), array_values($pm_importColumnNames) );
					$columnIDs = array_merge( array(null), array_keys($pm_importColumnNames) );

					if ( isset($headers_pack) )
						$headers = unserialize( base64_decode($headers_pack) );
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $PM_APP_ID );

	$preproc->assign( PAGE_TITLE, $pmStrings['icl_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_PM_IMPORT );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "pmStrings", $pmStrings );

	if ( !$fatalError ) {
		$preproc->assign( "step", $step );

		$preproc->assign( "pm_importColumnNames", $pm_importColumnNames );
		$preproc->assign( "columnNames", $columnNames );
		$preproc->assign( "columnIDs", $columnIDs );

		if ( isset($nameMap) )
			$preproc->assign( "nameMap", $nameMap );

		if ( isset($importColumn) ) 
			$preproc->assign( "importColumn", $importColumn );

		if ( isset($headers) ) {
			$preproc->assign( "headers", $headers );
			$preproc->assign( "headers_pack", base64_encode( serialize($headers) ) );
		}

		if ( isset($tmpFileName) )
			$preproc->assign( "tmpFileName", $tmpFileName );
		
		if ( isset($importFirstLine) )
			$preproc->assign( "importFirstLine", $importFirstLine );
	}

	$preproc->display( "import.htm" );
?>