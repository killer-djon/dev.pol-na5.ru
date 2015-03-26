<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/PM/pm.php" );

	//
	// Authorization
	//

	$SCR_ID = "WL";
	$errorStr = null;
	$fatalError = false;

	pageUserAuthorization( $SCR_ID, $PM_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$pmStrings = $pm_loc_str[$language];
	$invalidField = null;

	$btnIndex = getButtonIndex( array(PM_BTN_CLOSE, PM_BTN_RESTORECUSTOMER, PM_BTN_DELETECUSTOMER), $_POST );

	switch ( $btnIndex ) {
		case 0 : {
			redirectBrowser( PAGE_PM_CUSTOMERLIST, array("folder"=>RS_DELETED, SORTING_COL=>$sorting, "currentPage"=>$currentPage) );

			break;
		}

		case 1 : {
			$customerData["C_MODIFYUSERNAME"] = getUserName( $currentUser, true );

			$res = pm_restoreCustomer( prepareArrayToStore($customerData), $pmStrings, $kernelStrings );
			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();

				break;
			}

			redirectBrowser( PAGE_PM_CUSTOMERLIST, array("folder"=>RS_DELETED, SORTING_COL=>$sorting, "currentPage"=>$currentPage) );
			break;
		}

		case 2: {
			$res = pm_deleteCustomer( $customerData, $pmStrings, true, $language, $kernelStrings );
			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();

				break;
			}

			redirectBrowser( PAGE_PM_CUSTOMERLIST, array("folder"=>RS_DELETED, SORTING_COL=>$sorting, "currentPage"=>$currentPage) );
			break;
		}
	}

	switch( true ) {
		case( true ) : {
			if ( (!isset($edited) || !$edited) && $action == PM_ACTION_MODIFY ) {
				$customerData["C_ID"] = base64_decode( $C_ID );

				$res = exec_sql( $qr_pm_select_customer, $customerData, $customerData, true );
				if ( PEAR::isError($res) ) {
					$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_CUSTOMERFIELD] );

					$fatalError = true;
					break;
				}

				$customerData = prepareArrayToDisplay( $customerData );
				$customerData["C_MODIFYDATETIME"] = convertToDisplayDateTime( $customerData["C_MODIFYDATETIME"], false, true, true );
			}
		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $PM_APP_ID );

	$preproc->assign( "pmStrings", $pmStrings );

	$preproc->assign( PAGE_TITLE, $pmStrings['amc_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_PM_RESTORECUSTOMER );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( SORTING_COL, $sorting );
	$preproc->assign( "currentPage", $currentPage );

	if ( isset($customerData) )
		$preproc->assign( "customerData", $customerData );
	else
		$preproc->assign( "customerData", null );

	$preproc->display("customerinformation_deleted.htm");
?>