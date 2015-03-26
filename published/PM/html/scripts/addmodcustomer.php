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

	$btnIndex = getButtonIndex( array(BTN_CANCEL, BTN_SAVE, PM_BTN_DELETECUSTOMER, "inactivatebtn"), $_POST );

	switch ( $btnIndex ) {
		case 0 : {
			redirectBrowser( PAGE_PM_CUSTOMERLIST, array("folder"=>RS_ACTIVE,  SORTING_COL=>$sorting, "currentPage"=>$currentPage) );

			break;
		}
		case 1 : {
			$customerData["C_MODIFYUSERNAME"] = getUserName( $currentUser, true );

			$res = pm_addmodCustomer( $action, prepareArrayToStore($customerData), $pmStrings, $kernelStrings );
			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();

				$errCode = $res->getCode();

				if ( in_array($errCode, array(ERRCODE_INVALIDFIELD)) )
					$invalidField = $res->getUserInfo();

				break;
			}

			redirectBrowser( PAGE_PM_CUSTOMERLIST, array("folder"=>RS_ACTIVE, SORTING_COL=>$sorting, "currentPage"=>$currentPage) );
		}
		case 2 : {
				$customerData["C_MODIFYUSERNAME"] = getUserName( $currentUser, true );

				$res = pm_deleteCustomer( $customerData, $pmStrings, true, $language, $kernelStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();

					break;
				}

				redirectBrowser( PAGE_PM_CUSTOMERLIST, array("folder"=>RS_ACTIVE, SORTING_COL=>$sorting, "currentPage"=>$currentPage) );
		}
		case 3 : {
				$customerData["C_MODIFYUSERNAME"] = getUserName( $currentUser, true );

				$res = pm_deleteCustomer( $customerData, $pmStrings, false, $language, $kernelStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();

					break;
				}

				redirectBrowser( PAGE_PM_CUSTOMERLIST, array("folder"=>RS_ACTIVE, SORTING_COL=>$sorting, "currentPage"=>$currentPage) );
		}
	}

	switch ( true ) {
		case ( true ) : {
			if ( (!isset($edited) || !$edited) && ($action == PM_ACTION_MODIFY) ) {
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
	$preproc->assign( FORM_LINK, PAGE_PM_ADDMODCUSTOMER );
	$preproc->assign( ACTION, $action );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( SORTING_COL, $sorting );

	if ( $action == ACTION_NEW )
		$preproc->assign( HELP_TOPIC, "addcustomer.htm");
	else
		$preproc->assign( HELP_TOPIC, "modifycustomer.htm");

	$preproc->assign( "currentPage", $currentPage );

	if ( isset($customerData) )
		$preproc->assign( "customerData", $customerData );
	else
		$preproc->assign( "customerData", null );

	$preproc->display("customerinformation_active.htm");
?>