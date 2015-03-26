<?php
	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/PM/pm.php" );

	//	
	// Authorization
	//

	$SCR_ID = "WL";
	$errorStr = null;
	$fatalError = false;
	$userNames = array();

	if ( isset($currentPage) )
		$statData['SCREEN'] = $currentPage;

	if ( !isset($statData) )
		$statData = array();

	define( 'USERS_PER_PAGE', 6 );

	if ( !isset($firstIndex) )
		$firstIndex = 1;

	$processButtonTemplate = "javascript:processTextButton('%s', 'form')";

	pageUserAuthorization( $SCR_ID, $PM_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$pmStrings = $pm_loc_str[$language];
	
	//
	// Form handling
	//

	if (!empty($_GET["actionName"]))
		$_POST[$_GET["actionName"]] = 1;
	$btnIndex = getButtonIndex( array('showWorks', 'showCost', 'showAssignments'), $_POST );
	
	switch ( $btnIndex ) {
		case 0 : $statData["SCREEN"] = PM_SHOW_WORK_COUNT; $pageTitle = $pmStrings["prs_taskcount_tab"]; break;
		case 1 : $statData["SCREEN"] = PM_SHOW_ESTIMATED_COST; $pageTitle = $pmStrings["prs_estcost_tab"]; break;
		case 2 : $statData["SCREEN"] = PM_SHOW_ASSIGNED_USERS; $pageTitle = $pmStrings["prs_assignments_tab"]; break;
	}
	
	
	
	
	if ($btnIndex == -1)
		$pageTitle = $pmStrings["prs_taskcount_tab"]; 

	switch( true ) {
		case( true ) : {
			$currency_ids = listCurrency();
			if ( PEAR::isError($currency_ids) )
				$errorStr = $pmStrings['prs_curerr_message'];

			if ( is_array($currency_ids) )
				$currency_ids = array_keys( $currency_ids );

			if ( !isset($statData["SCREEN"]) )
				$statData["SCREEN"] = PM_SHOW_WORK_COUNT;

			if ( $statData["SCREEN"] == PM_SHOW_ASSIGNED_USERS ) {
				$users = array();

				$query = $qr_pm_select_all_projects_asgnlist;

				$qr = db_query( $query );
				if ( PEAR::isError($qr) )
					$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_ASGNFIELD] );

				while( $row = db_fetch_array($qr) )
					$users[$row['U_ID']] = nl2br(getArrUserName( $row, true ));

				db_free_result( $qr );

				$users = array_unique( $users );
				$users_count = count( $users );

				if ( isset($userShift) && strcmp($userShift, $pmStrings['prs_selectuser_item']) ) {
					foreach ( $users as $userNumber => $userValue ) {
						if ( !strcmp($userNumber, $userShift ) )
							break;
						$firstIndex++;
					}
				}

				if ( ($firstIndex > $users_count) || $users_count < (USERS_PER_PAGE + 1) )
					$firstIndex = 1; 
			}

			$projects = array();

			$qr = db_query( $qr_pm_select_projects_ordered );
			if ( PEAR::isError($qr) ) {
				$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_PROJECTFIELD] );

				$fatalError = true;
				break;
			}

			while ( $row = db_fetch_array($qr) ) {
				$curRecord = $row;

				$curRecord = prepareArrayToDisplay( $curRecord );

				$params = array();
				$params["P_ID"] = base64_encode($curRecord['P_ID']);
				$params["folder"] = 0;
				$params["action"] = PM_ACTION_MODIFY;
				$params["sorting"] = "";
				$params["currentPage"] = $statData["SCREEN"];
				$params[OPENER] = PAGE_PM_PROJECTSTATISTICS;

				$curRecord["ROW_URL"] = prepareURLStr( PAGE_PM_VIEWPROJECT, $params );

				$project_name =  strTruncate( $curRecord["C_NAME"], PM_CUST_NAME_LEN ) . ". " . $curRecord["P_DESC"];
				$page = PAGE_PM_ADDMODPROJECT;

				$curRecord["PROJECT_NAME"] = $project_name;

				switch( $statData["SCREEN"] ) {
					case( PM_SHOW_WORK_COUNT ) : {
						$curRecord["TOTAL_WORKS"] = db_query_result( $qr_pm_count_project_total_work, DB_FIRST, $curRecord );
						if ( PEAR::isError($curRecord["TOTAL_WORKS"]) )
							$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

						$curRecord["IN_PROGRESS_WORKS"] = db_query_result( $qr_pm_count_project_in_progress_work, DB_FIRST, $curRecord );
						if ( PEAR::isError($curRecord["IN_PROGRESS_WORKS"]) )
							$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

						$curRecord["CLOSED_WORKS"] = db_query_result( $qr_pm_count_project_closed_work, DB_FIRST, $curRecord );
						if ( PEAR::isError($curRecord["CLOSED_WORKS"]) )
							$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

						$curRecord["OVERDUE_WORKS"] = db_query_result( $qr_pm_count_project_overdue_work, DB_FIRST, $curRecord );
						if ( PEAR::isError($curRecord["OVERDUE_WORKS"]) )
							$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

						if ( !isset($statData["TOTAL_WORKS"]) )  $statData["TOTAL_WORKS"] = 0;
						if ( !isset($statData["IN_PROGRESS_WORKS"]) )  $statData["IN_PROGRESS_WORKS"] = 0;
						if ( !isset($statData["CLOSED_WORKS"]) )  $statData["CLOSED_WORKS"] = 0;
						if ( !isset($statData["OVERDUE_WORKS"]) )  $statData["OVERDUE_WORKS"] = 0;

						$statData["TOTAL_WORKS"] += $curRecord["TOTAL_WORKS"];
						$statData["IN_PROGRESS_WORKS"] += $curRecord["IN_PROGRESS_WORKS"];
						$statData["CLOSED_WORKS"] += $curRecord["CLOSED_WORKS"];
						$statData["OVERDUE_WORKS"] += $curRecord["OVERDUE_WORKS"];

						break;
					}

					case( PM_SHOW_ESTIMATED_COST ) : {
						$curRecord["BILLABLE"] = db_query_result( $qr_pm_select_project_billable, DB_FIRST, $curRecord );
						if ( PEAR::isError($curRecord["BILLABLE"]) )
							$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_PROJECTFIELD] );

						$curRecord["BILLABLE"] = formatFloat( $curRecord["BILLABLE"], 2, "." );

						foreach( $currency_ids as $currencyId => $currency ) {
							$total_cost = db_query_result( sprintf($qr_pm_select_project_total_work_cost, $currency), DB_FIRST, $curRecord );
							if ( PEAR::isError($total_cost) )
								$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

							$total_cost = formatFloat( $total_cost, 2, "." );

							$in_progress_cost= db_query_result( sprintf($qr_pm_select_project_in_progress_work_cost, $currency), DB_FIRST, $curRecord );
							if ( PEAR::isError($in_progress_cost) )
								$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

							$in_progress_cost = formatFloat( $in_progress_cost, 2 , ".");

							$closed_cost = db_query_result( sprintf($qr_pm_select_project_closed_work_cost, $currency), DB_FIRST, $curRecord );
							if ( PEAR::isError($closed_cost) )
								$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

							$closed_cost = formatFloat( $closed_cost, 2 , ".");

							$overdue_cost = db_query_result( sprintf($qr_pm_select_project_overdue_work_cost, $currency), DB_FIRST, $curRecord );
							if ( PEAR::isError($overdue_cost) )
								$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

							$overdue_cost = formatFloat( $overdue_cost, 2, "." );

							if ( !isset($curRecord["TOTAL_COST"]) ) $curRecord["TOTAL_COST"] = array();
							if ( !isset($curRecord["IN_PROGRESS_COST"]) ) $curRecord["IN_PROGRESS_COST"] = array();
							if ( !isset($curRecord["CLOSED_COST"]) ) $curRecord["CLOSED_COST"] = array();
							if ( !isset($curRecord["OVERDUE_COST"]) ) $curRecord["OVERDUE_COST"] = array();

							$curRecord["TOTAL_COST"] [count($curRecord["TOTAL_COST"])] = array( "CURRENCY"=>$currency, "COST"=>$total_cost );
							$curRecord["IN_PROGRESS_COST"] [count($curRecord["IN_PROGRESS_COST"])] = array( "CURRENCY"=>$currency, "COST"=>$in_progress_cost );
							$curRecord["CLOSED_COST"] [count($curRecord["CLOSED_COST"])] = array( "CURRENCY"=>$currency, "COST"=>$closed_cost );
							$curRecord["OVERDUE_COST"] [count($curRecord["OVERDUE_COST"])] = array( "CURRENCY"=>$currency, "COST"=>$overdue_cost );

							if ( strlen($total_cost) && !isset($statData["TOTAL_COST"][$currency]) ) $statData["TOTAL_COST"][$currency] = 0;
							if ( strlen($in_progress_cost) && !isset($statData["IN_PROGRESS_COST"][$currency]) ) $statData["IN_PROGRESS_COST"][$currency] = 0;
							if ( strlen($closed_cost) && !isset($statData["CLOSED_COST"][$currency]) ) $statData["CLOSED_COST"][$currency] = 0;
							if ( strlen($overdue_cost) && !isset($statData["OVERDUE_COST"][$currency]) ) $statData["OVERDUE_COST"][$currency] = 0;

							if ( strlen($total_cost) ) $statData["TOTAL_COST"][$currency] += $total_cost;
							if ( strlen($in_progress_cost) ) $statData["IN_PROGRESS_COST"][$currency] += $in_progress_cost;
							if ( strlen($closed_cost) ) $statData["CLOSED_COST"][$currency] += $closed_cost;
							if ( strlen($overdue_cost) ) $statData["OVERDUE_COST"][$currency] += $overdue_cost;
						}

						if ( !isset($statData["BILLABLE"]) ) $statData["BILLABLE"] = 0;
						$statData["BILLABLE"] += $curRecord["BILLABLE"];

						break;
					}

					case( PM_SHOW_ASSIGNED_USERS ) : {
						$total_users = array();
					
						$total_users_qr = db_query( $qr_pm_select_project_work_user, $curRecord );
						if ( PEAR::isError($total_users_qr) ) {
							$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_ASGNFIELD] );

							$fatalError = true;
							break;
						}

						while( $total_row = db_fetch_array($total_users_qr) ) {
							$total_users[$total_row["U_ID"]] = $total_row["U_ID"];
							$userNames[$total_row["U_ID"]] = getArrUserName($total_row);
						}

						@db_free_result( $total_users_qr );

						$total_users = array_unique( $total_users );

						$curRecord["TOTAL_ASSIGNMENTS"] = count( $total_users );

						$project_work_users_exist = array();

						foreach( $users as $fieldNumber => $fieldValue ) {
							if ( in_array($fieldNumber, $total_users) )
								$project_work_users_exist[$fieldNumber] = PM_EXIST_ID;
							else
								$project_work_users_exist[$fieldNumber] = 0;
						}

						$total_users = $project_work_users_exist; 
						$total_users = refinedSlice( $total_users, $firstIndex-1, USERS_PER_PAGE );

						$curRecord["ASGN"] = $total_users;

						break;
					}
				}

				$projects[] = $curRecord;
			}

			db_free_result( $qr );

			if ( $statData["SCREEN"] == PM_SHOW_ASSIGNED_USERS ) {
				$userlist = array();
				$userlist[count($userlist)] = $pmStrings['icl_select_item'];

				$userlist = array_merge( $userlist, $users );
				$users = refinedSlice( $users, $firstIndex-1, USERS_PER_PAGE );

				$prevIndex = $firstIndex - USERS_PER_PAGE;
				if ( $prevIndex < 1 )
					$prevIndex = 1;

				$nextIndex = $firstIndex + USERS_PER_PAGE;
				if ( $nextIndex > $users_count )
					$nextIndex = $users_count;

				$nextLink = ( ($firstIndex+USERS_PER_PAGE < $users_count+1) && count($projects) ) ? prepareURLStr( PAGE_PM_PROJECTSTATISTICS, array("statData[SCREEN]"=>$statData["SCREEN"], "firstIndex"=>$nextIndex ) ) : null;
				$prevLink = ( $firstIndex > 1 && count($projects) ) ? prepareURLStr( PAGE_PM_PROJECTSTATISTICS, array("statData[SCREEN]"=>$statData["SCREEN"], "firstIndex"=>$prevIndex ) ) : null;

				$totalColCount = count( $users ) + 2;
				$rowCount = count($users) + 1;

				if ( $nextLink ) {
					$totalColCount += 1;
					$rowCount += 1;
				}
				if ( $prevLink ) {
					$totalColCount += 1;
					$rowCount += 1;
				}

				$minCount = $rowCount - 1;
				$userCount = count( $users ) - 1;

				$statData["TOTAL_ASSIGNMENTS"] = count( $userlist ) - 1;
			}
		}

		// Tabs
		//
		$viewMenu = array();

		$checked = ($statData["SCREEN"] == PM_SHOW_WORK_COUNT) ? "checked" : "unchecked";
		$viewMenu[] = array( 'caption'=>$pmStrings['prs_taskcount_tab'], 'link'=>sprintf( $processButtonTemplate, 'showWorks' )."||$checked" );

		$checked = ($statData["SCREEN"] == PM_SHOW_ESTIMATED_COST) ? "checked" : "unchecked";
		$viewMenu[] = array( 'caption'=>$pmStrings['prs_estcost_tab'], 'link'=>sprintf( $processButtonTemplate, 'showCost' )."||$checked" );

		$checked = ($statData["SCREEN"] == PM_SHOW_ASSIGNED_USERS) ? "checked" : "unchecked";
		$viewMenu[] = array( 'caption'=>$pmStrings['prs_assignments_tab'], 'link'=>sprintf( $processButtonTemplate, 'showAssignments' )."||$checked" );
	}

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $PM_APP_ID );

	$preproc->assign( "pmStrings", $pmStrings );

	$preproc->assign( FORM_LINK, PAGE_PM_PROJECTSTATISTICS );
	$preproc->assign( PAGE_TITLE, $pageTitle );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	$preproc->assign( "statData", $statData );
	$preproc->assign( "viewMenu", $viewMenu );
	
	$preproc->assign( HELP_TOPIC, "projstatistics.htm");

	if ( isset($nextLink) )
		$preproc->assign( "nextLink", $nextLink );
	else
		$preproc->assign( "nextLink", null );

	if ( isset($prevLink) )
		$preproc->assign( "prevLink", $prevLink );
	else
		$preproc->assign( "prevLink", null );

	if ( isset($userlist) )
		$preproc->assign( "userlist", $userlist );
	else
		$preproc->assign( "userlist", null );

	$preproc->assign( "showUserSelector", isset($userlist) && count($userlist) > USERS_PER_PAGE );

	if ( isset($users) )
		$preproc->assign( "users", $users );
	else
		$preproc->assign( "users", null );

	if ( isset($totalColCount) )
		$preproc->assign( "totalColCount", $totalColCount );
	else
		$preproc->assign( "totalColCount", null );

	$preproc->assign( "userNames", $userNames );	

	if ( isset($minCount) )
		$preproc->assign( "minCount", $minCount );
	else
		$preproc->assign( "minCount", null );

	if ( isset($rowCount) )
		$preproc->assign( "rowCount", $rowCount );
	else
		$preproc->assign( "rowCount", null );

	if ( isset($userCount) )
		$preproc->assign( "userCount", $userCount );
	else
		$preproc->assign( "userCount", null );

	$preproc->assign( "projects", $projects );

	$preproc->display("projectstatistics.htm");
?>