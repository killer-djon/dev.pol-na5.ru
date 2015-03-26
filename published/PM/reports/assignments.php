<?php
	
	require_once( "../../common/reports/reportsinit.php" );

	require_once( WBS_DIR."/published/PM/pm.php" );

	$fatalError = false;
	$errorStr = null;	
	$SCR_ID = "WL";

	reportUserAuthorization( $SCR_ID, $PM_APP_ID, false );

	$kernelStrings = $loc_str[$language];
	$pmStrings = $pm_loc_str[$language];

	if ( !isset($firstIndex) )
		$firstIndex = 1;

	switch( true ) {
		case ( true ) :
				$P_ID = base64_decode( $P_ID );

				$projData = pm_getProjectData( $P_ID, $kernelStrings );
				if ( PEAR::isError($projData) ) {
					$fatalError = true;
					$errorStr = $res->getMessage();

					break;
				}

				$managerName = getArrUserName( $projData, true );

				$projectData = array( 'P_ID'=>$P_ID );

				$users = array();

				$qr = db_query( $qr_pm_select_work_asgnlist, $projectData );
				if ( PEAR::isError($qr) ) {
					$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_ASGNFIELD] );

					$fatalError = true;
					break;
				}

				while ( $row = db_fetch_array($qr) )
					$users[$row['U_ID']] = nl2br(getArrUserName( $row, true ));

				@db_free_result( $qr );

				$users = array_unique( $users );
				$users_count = count($users);

				if ( isset($userShift) && strcmp($userShift, $pmStrings['pm_selectuser_item']) ) {
					foreach ( $users as $userNumber => $userValue ) {
						if ( !strcmp($userNumber, $userShift ) )
							break;
						$firstIndex++;
					}
				}

				$project_works = array();

				$qr = db_query( $qr_pm_select_project_works, $projectData );
				if ( PEAR::isError($qr) ) {
					$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

					$fatalError = true;
					break;
				}

				$userAssignments = array();

				while ( $row = db_fetch_array($qr) ) {
					$curRecord = prepareArrayToDisplay( $row );

					$curRecord["P_ID"] = $projectData["P_ID"];

					$work_users = array();
		
					$work_qr =  db_query( $qr_pm_select_work_asgn, $curRecord );
					if ( PEAR::isError($qr) ) {
						$errorStr = sprintf( $pmStrings[PM_ERR_EXTRACTION], $pmStrings[PM_ERR_ASGNFIELD] );

						$fatalError = true;
						break;
					}

					while ( $work_row = db_fetch_array($work_qr) )
						$work_users[count($work_users)] = $work_row["U_ID"];

					@db_free_result($work_qr);

					$work_users_exist = array();

					foreach( $users as $fieldNumber => $fieldValue ) {
						$curU_ID = $fieldNumber;

						if ( !isset($userAssignments[$curU_ID]) )
							$userAssignments[$curU_ID] = 0;

						if ( in_array($fieldNumber, $work_users) ) {
							$work_users_exist[$curU_ID] = PM_EXIST_ID;
							$userAssignments[$curU_ID] ++;
						} else
							$work_users_exist[$curU_ID] = 0;
					}

					$work_users = $work_users_exist;

					$curRecord["ASGN"] = $work_users;

					$project_works[count($project_works)] = $curRecord;
				}

				@db_free_result($qr);

				$userlist = array();
				$userlist[count($userlist)] = $pmStrings['pm_selectuser_item'];
				$userlist = array_merge( $userlist, $users );
				
				$totalColCount = count( $users ) + 3;
				$userCount = count( $users );

				if ( !count($users) )
					$users[] = "&lt;".$pmStrings['pm_noassignemtns_message']."&gt;";
	}

	$preprocessor = new print_preprocessor( $PM_APP_ID, $kernelStrings, $language );

	$preprocessor->assign( REPORT_TITLE, $pmStrings['prs_assignments_tab'] );
	$preprocessor->assign( ERROR_STR, $errorStr );
	$preprocessor->assign( FATAL_ERROR, $fatalError );
	$preprocessor->assign( "pm_loc_str", $pmStrings );
	$preprocessor->assign( "pmStrings", $pmStrings );

	if ( !$fatalError ) {
		$preprocessor->assign( "projData", $projData );
		$preprocessor->assign( "project_works", $project_works );
		$preprocessor->assign( "users", $users );
		
		if( isset($work_users) )
			$preprocessor->assign( "work_users", $work_users );
		else
			$preprocessor->assign( "work_users", null );

		$preprocessor->assign( "userlist", $userlist );
		$preprocessor->assign( "userlistIDs", array_keys($userlist) );

		$preprocessor->assign( "userAssignments", $userAssignments );
		$preprocessor->assign( "totalColCount", $totalColCount+2 );
		$preprocessor->assign( "userCount", $userCount );
		$preprocessor->assign( "managerName", $managerName );
	}

	$preprocessor->display( "assignments.htm" );

?>
