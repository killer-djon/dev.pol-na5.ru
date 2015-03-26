<?php
	
	require_once( "../../common/reports/reportsinit.php" );

	require_once( WBS_DIR."/published/PM/pm.php" );

	$fatalError = false;
	$errorStr = null;	
	$SCR_ID = "WL";

	reportUserAuthorization( $SCR_ID, $PM_APP_ID, false );

	$kernelStrings = $loc_str[$language];
	$pmStrings = $pm_loc_str[$language];

	switch( true ) {
		case ( true ) :
				$P_ID = base64_decode( $P_ID );
				$interval = base64_decode( $interval );

				$projData = pm_getProjectData( $P_ID, $kernelStrings );
				if ( PEAR::isError($projData) ) {
					$fatalError = true;
					$errorStr = $res->getMessage();

					break;
				}

				$managerName = getArrUserName( $projData, true );

				$showComplete = readUserCommonSetting( $currentUser, 'showCompleteTasks' );
				if ( !strlen($showComplete) )
					$showComplete = 1;

				$project_works = null;
				$ganttSettings = null;
				$res = pm_generateGanttContent( $interval, $P_ID, $project_works, 
											$ganttSettings, "PW_STARTDATE asc", $kernelStrings, $pmStrings, $currentUser, $showComplete );
				if ( PEAR::isError($res) ) {
					$fatalError = true;
					$errorStr = $res->getMessage();

					break;
				}		
	}

	$preprocessor = new print_preprocessor( $PM_APP_ID, $kernelStrings, $language );

	$preprocessor->assign( REPORT_TITLE, $pmStrings['pm_ganttchart_title'] );
	$preprocessor->assign( ERROR_STR, $errorStr );
	$preprocessor->assign( FATAL_ERROR, $fatalError );
	$preprocessor->assign( "pm_loc_str", $pmStrings );
	$preprocessor->assign( "pmStrings", $pmStrings );

	if ( !$fatalError ) {
		$preprocessor->assign( "project_works", $project_works );
		$preprocessor->assign( "ganttSettings", $ganttSettings );
		$preprocessor->assign( "projData", $projData );	
		$preprocessor->assign( "managerName", $managerName );
	}

	$preprocessor->display( "ganttchart.htm" );

?>