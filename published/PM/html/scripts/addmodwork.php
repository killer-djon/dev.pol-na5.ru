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

	$btnIndex = getButtonIndex( array(BTN_CANCEL, BTN_SAVE, PM_BTN_FINISHWORK, PM_BTN_REOPENWORK, PM_BTN_DELETEWORK, PM_BTN_CLOSE, "saveaddbtn"), $_POST );

	switch ( $btnIndex ) {
		case 0 : {
			redirectBrowser( $opener, array(SORTING_COL=>$sorting, "P_ID"=>base64_encode($workData["P_ID"]), "firstIndex"=>$firstIndex, "list_action"=>$list_action) );

			break;
		}
		case 1 :
		case 6 : {
			if ( isset($assigned_users) )
				$workData["ASSIGNED"] = $assigned_users;
			
			$res = pm_addmodWork( $action, prepareArrayToStore($workData), $pmStrings, $kernelStrings, $language );
			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();

				$errorCode = $res->getCode();

				if ( in_array($errorCode, array(ERRCODE_INVALIDFIELD, ERRCODE_INVALIDLENGTH, ERRCODE_INVALIDDATE, PM_ERRCODE_STARTEXCEND, PM_ERRCODE_STARTEXCDUE, PM_ERRCODE_P_STARTEXC_PW_START)) )
					$invalidField = $res->getUserInfo();

				break;
			}

			if ( $btnIndex == 1 )
				redirectBrowser( $opener, array(SORTING_COL=>$sorting, "P_ID"=>base64_encode($workData["P_ID"]), "PW_ID"=>base64_encode($workData["PW_ID"]), "firstIndex"=>$firstIndex, "list_action"=>$list_action) );
			else
				redirectBrowser( PAGE_PM_ADDMODWORK, array(SORTING_COL=>$sorting, "P_ID"=>base64_encode($workData["P_ID"]), "PW_ID"=>base64_encode($workData["PW_ID"]), "firstIndex"=>$firstIndex, "opener"=>$opener, "list_action"=>$list_action, ACTION=>PM_ACTION_NEW) );

			break;
		}

		case 2 : {
			redirectBrowser( PAGE_PM_FINISHWORK, array(SORTING_COL=>$sorting, "P_ID"=>base64_encode($workData["P_ID"]), "PW_ID"=>base64_encode($workData["PW_ID"]), "firstIndex"=>$firstIndex, "opener"=>$opener, "list_action"=>$list_action) ) ;
			break;
		}

		case 3 : {
				$res = pm_reopenWork( prepareArrayToStore($workData), $pmStrings, $kernelStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();

					break;
				}

				redirectBrowser( PAGE_PM_ADDMODWORK, array(SORTING_COL=>$sorting, ACTION=>PM_ACTION_MODIFY, "P_ID"=>base64_encode($workData["P_ID"]), "PW_ID"=>base64_encode($workData["PW_ID"]), "firstIndex"=>$firstIndex, "opener"=>$opener, "list_action"=>$list_action) );
				break;
		}

		case 4 : {
				$res = pm_deleteWork( $workData, $pmStrings, $language, $kernelStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();

					$errorCode = $res->getCode();

					break;
				}

				redirectBrowser( $opener, array(SORTING_COL=>$sorting, "P_ID"=>base64_encode($workData["P_ID"]), "PW_ID"=>base64_encode($workData["PW_ID"]), "firstIndex"=>$firstIndex, "list_action"=>$list_action) );
		}

		case 5 : {
				redirectBrowser( $opener, array(SORTING_COL=>$sorting, "P_ID"=>base64_encode($workData["P_ID"]), "PW_ID"=>base64_encode($workData["PW_ID"]), "firstIndex"=>$firstIndex, "list_action"=>$list_action) );
				break;
		}
	}

	switch ( true ) {
		case ( true ) : {
			if ( !isset($sorting) )
				$sorting = base64_encode( "PW_STARTDATE asc" );

			if ( !isset($firstIndex) )
				$firstIndex = 1;

			$currency_ids = listCurrency();
			if ( PEAR::isError($currency_ids) ) {
				$errorStr = $pmStrings['amt_curlisterr_message'];

				$fatalError = true;
				break;
			}

			if ( is_array($currency_ids) )
				$currency_ids = array_keys( $currency_ids );

			if ( (!isset($edited) || !$edited) && $action == PM_ACTION_NEW )
			{
				$workData = pm_initWork( base64_decode($P_ID), $pmStrings );
				if ( PEAR::isError($workData) ) {
					$errorStr = $workData->getMessage();

					$fatalError = true;
					break;
				}

				$workData = prepareArrayToDisplay( $workData, array("PW_DESC") );

				$available_users = array();
				if ( PEAR::isError( $qr = db_query( $qr_pm_select_asgn_list ) ) )
				{
					$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_ASGNFIELD] );

					$fatalError = true;
					break;
				}

				while( $row = db_fetch_array( $qr ) )
					$available_users[count( $available_users )] = $row["U_ID"];
				
				@db_free_result( $qr );
			}

			if ( (!isset($edited) || !$edited) && ($action == PM_ACTION_MODIFY) )
			{
				$workData["P_ID"] = base64_decode( $P_ID );
				$workData["PW_ID"] = base64_decode( $PW_ID );

				$res = exec_sql( $qr_pm_select_work, $workData, $workData, true );
				if ( PEAR::isError($res) ) {
					$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

					$fatalError = true;
					break;
				}

				$workData = prepareArrayToDisplay( $workData, array("PW_DESC") );

				$workData["P_DESC"] = implode( ". ", array( strTruncate( $workData["C_NAME"], PM_CUST_NAME_LEN ), $workData["P_DESC"]) );

				$userRights = $PMRightsManager->evaluateUserProjectRights( $currentUser, $workData["P_ID"], $kernelStrings );

				$workData["U_STATUS"] = PM_VALIDMANAGER;
				if ( $userRights < PM_RIGHT_READWRITE )
					$workData["U_STATUS"] = PM_INVALIDMANAGER;

				foreach( $workData as $fieldName => $fieldValue )
					if ( in_array($fieldName, array("PW_STARTDATE", "PW_DUEDATE", "PW_ENDDATE", "P_ENDDATE")) )
						$workData[$fieldName] = convertToDisplayDate( $fieldValue );

				$workData["P_STATUS"] = pm_compareWithCurDate( $workData["P_ENDDATE"] ) | $workData["U_STATUS"];
				$workData["PW_STATUS"] = pm_compareWithCurDate( $workData["PW_ENDDATE"] ) | $workData["U_STATUS"];
				$workData["PW_COSTESTIMATE"] = formatFloat($workData["PW_COSTESTIMATE"], 2, ".");
			}

			if ( !isset($edited) || !$edited ) {
				$available_users = array();

				if ( PEAR::isError( $qr = db_query( $qr_pm_select_asgn_list ) ) ) {
					$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_ASGNFIELD] );

					$fatalError = true;
					break;
				}

				while( $row = db_fetch_array( $qr ) )
					$available_users[count( $available_users )] = $row["U_ID"];

				@db_free_result( $qr );

				if ( $action == PM_ACTION_MODIFY ) {
					$assigned_users = array();

					if ( PEAR::isError( $qr = db_query( $qr_pm_select_work_asgn, array( "P_ID" => $workData["P_ID"], "PW_ID" => $workData["PW_ID"] ) ) ) ) {
						$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_ASGNFIELD] );

						$fatalError = true;
						break;
					}

					while ( $row = db_fetch_array( $qr ))
						$assigned_users[count( $assigned_users )] = $row["U_ID"];

					@db_free_result( $qr );

					$array = array();

					for ( $i = 0; $i <= count($available_users)-1; $i++ ) {
						for ( $j = 0; $j <= count( $assigned_users)-1; $j++ )
							if ( $available_users[$i] == $assigned_users[$j] ) break;

						if ( $j == count($assigned_users) )
							$array[count($array)] = $available_users[$i];
					}

					$available_users = $array;
				}
			}

		}

		if ( isset($available_users) )
			for ( $i = 0; $i <= count($available_users)-1; $i++ )
				$available_users_names[] = getUserName( $available_users[$i], true );

		if ( isset($assigned_users) )
			for ( $i = 0; $i <= count($assigned_users)-1; $i++ )
				$assigned_users_names[] = getUserName( $assigned_users[$i], true );
	}

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $PM_APP_ID );

	$preproc->assign( "pmStrings", $pmStrings );

	$title = ($action == PM_ACTION_MODIFY) ? $pmStrings['amt_pageedit_title'] : $pmStrings['amt_pageadd_title'];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK , PAGE_PM_ADDMODWORK );
	$preproc->assign( ACTION, $action );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( SORTING_COL, $sorting );
	$preproc->assign( "opener", $opener );
	$preproc->assign( "firstIndex", $firstIndex );

	if ( isset($edited) )
		$preproc->assign( "edited", $edited );

	if ( $action == ACTION_NEW )
		$preproc->assign( HELP_TOPIC, "addwork.htm");
	else
		$preproc->assign( HELP_TOPIC, "modifywork.htm");

	if ( !isset($list_action) )
		$list_action = null;

	$preproc->assign( "list_action", $list_action );

	if ( !$fatalError ) {
		$preproc->assign( "workData", $workData );

		if ( isset($assigned_users) )
			$preproc->assign( "assigned_users", $assigned_users );
		else
			$preproc->assign( "assigned_users", null );

		if ( isset($available_users) )
			$preproc->assign( "available_users", $available_users );
		else
			$preproc->assign( "available_users", null );

		if ( isset( $available_users_names ) )
			$preproc->assign( "available_users_names", $available_users_names );
		else
			$preproc->assign( "available_users_names", null );

		if ( isset( $assigned_users_names ) )
			$preproc->assign( "assigned_users_names", $assigned_users_names );
		else
			$preproc->assign( "assigned_users_names", null );

		$preproc->assign( "currency_ids", $currency_ids );
	}

	$preproc->display("workinformation.htm");
?>