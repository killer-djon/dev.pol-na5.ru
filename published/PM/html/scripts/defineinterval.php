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

	$btnIndex = getButtonIndex( array( BTN_CANCEL, BTN_SAVE, 'deletebtn' ), $_POST );

	switch ($btnIndex) {
		case 1 :
				if ( $startMode == 0 ) {
					$startDate = 0;
				} else {
					$startDate = sprintf( "%s-%02d-01", $startDate_year, $startDate_month );
					$startDate = strtotime($startDate);
					$startDate = date( DATE_DISPLAY_FORMAT, $startDate );
				}

				if ( $endMode == 0 )
					$endDate = 0;
				else {
					$endDate = sprintf( "%s-%02d-01", $endDate_year, $endDate_month );
					$endDate = strtotime($endDate);
					$lastDay = date( "t", $endDate );
					$endDate = sprintf( "%s-%02d-%s", $endDate_year, $endDate_month, $lastDay );
					$endDate = strtotime($endDate);
					$endDate = date( DATE_DISPLAY_FORMAT, $endDate );
				}

				$res = pm_saveGanttInterval( $currentUser, $startDate, $endDate, $kernelStrings, $pmStrings );
				if ( PEAR::isError( $res ) ) {
					$errorStr = $res->getMessage();
					$invalidField = $res->getUserInfo();

					break;
				}

				writeUserCommonSetting( $currentUser, 'ganttInterval', $res, $kernelStrings );
		case 0 : redirectBrowser( PAGE_PM_WORKLIST, array() );
		case 2 : 
				if ( isset($int) )
					pm_deleteGanttIntervals( $currentUser, array_keys($int), $kernelStrings, $pmStrings );
	}

	switch (true) {
		case true : 
					if ( !isset( $edited ) ) {
						$startDate_month = date( 'n' );
						$startDate_year = date( 'Y' );

						$nextMonth = strtotime( "+1 month" );
						$endDate_month = date( 'n', $nextMonth );
						$endDate_year = date( 'Y', $nextMonth );

						$startMode = 0;
						$endMode = 0;
					}

					$start = 0;
					$end = 0;
					pm_findProjectsDateBounds( $start, $end, $currentUser, $pmStrings, $kernelStrings, true );

					$monthList_ids = array();
					$monthList_names = array();
					for ( $i = 1; $i <= 12; $i++ ) {
						$monthList_ids[] = $i;
						$monthList_names[] = $kernelStrings[$monthFullNames[$i-1]];
					}

					$years = array();
					$minYear = date( 'Y', $start ) - 1;
					$maxYear = date( 'Y', $end ) + 1;
					for ( $i = $minYear; $i <= $maxYear; $i++ )
						$years[] = $i;

					$intervals = pm_listGanttIntervals( $currentUser, $pmStrings, $kernelStrings );
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $PM_APP_ID );

	$preproc->assign( PAGE_TITLE, $pmStrings['dvi_screen_title'] );
	$preproc->assign( FORM_LINK, PAGE_PM_DEFINEINTERVAL );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "pmStrings", $pmStrings );

	if ( !$fatalError ) {
		$preproc->assign( "startDate_month", $startDate_month );
		$preproc->assign( "startDate_year", $startDate_year );

		$preproc->assign( "endDate_month", $endDate_month );
		$preproc->assign( "endDate_year", $endDate_year );

		$preproc->assign( "monthList_ids", $monthList_ids );
		$preproc->assign( "monthList_names", $monthList_names );
		$preproc->assign( "years", $years );

		$preproc->assign( "startMode", $startMode );
		$preproc->assign( "endMode", $endMode );

		$preproc->assign( "intervals", $intervals );
		$preproc->assign( "intervalsNum", count($intervals) );
	}

	$preproc->display( "defineinterval.htm" );
?>