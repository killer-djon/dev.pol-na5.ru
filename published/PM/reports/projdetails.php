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

				$projData = pm_getProjectData( $P_ID, $kernelStrings );
				if ( PEAR::isError($projData) ) {
					$fatalError = true;
					$errorStr = $res->getMessage();

					break;
				}
				
				$currency_ids = listCurrency();
				if ( PEAR::isError($currency_ids) ) {
					$errorStr = $pmStrings['amt_curlisterr_message'];

					$fatalError = true;
					break;
				}

				$currencyList = array ();
				foreach ($currency_ids as $cId => $cCurrency) {
					$currencyList[] = "['" . $cId . "']";
				}
				
				$currenciesListStr = join(",", $currencyList);
				
				$showComplete = readUserCommonSetting( $currentUser, 'showCompleteTasks' );
				if ( !strlen($showComplete) )
					$showComplete = 1;
				
				break;

				/*$managerName = getArrUserName( $projData, true );

				$project_works = array();

				$sortClause = "PW_ID";
				$qr = db_query( sprintf( $qr_pm_select_project_works_ordered, $sortClause ), array('P_ID'=>$P_ID) );
				if ( PEAR::isError($qr) ) {
					$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );
					
					$fatalError = true;
					break;
				}

				$totalCost = array();
				while ( $row = db_fetch_array($qr) ) {
					$curRecord = prepareArrayToDisplay( $row );

					$curRecord["PW_STARTDATE"] = convertToDisplayDate( $curRecord["PW_STARTDATE"] );
					$curRecord["PW_DUEDATE"] = convertToDisplayDate( $curRecord["PW_DUEDATE"] );
					$curRecord["PW_ENDDATE"] = convertToDisplayDate( $curRecord["PW_ENDDATE"] );

					$curRecord["PW_STATUS"] = ( (dateCmp($curRecord["PW_DUEDATE"], displayDate(time())) < 0 && !strlen($curRecord["PW_ENDDATE"]) && strlen($curRecord["PW_DUEDATE"])) || (dateCmp($curRecord["PW_DUEDATE"], $curRecord["PW_ENDDATE"]) < 0) ) ? RS_DELETED : RS_ACTIVE;

					if ( strlen($curRecord["PW_COSTESTIMATE"]) ) {
						if ( !array_key_exists( $curRecord["PW_COSTCUR"], $totalCost ) )
							$totalCost[$curRecord["PW_COSTCUR"]] = 0;
						$totalCost[$curRecord["PW_COSTCUR"]] += $curRecord["PW_COSTESTIMATE"];

						$curRecord["PW_COSTESTIMATE"] = formatFloat( $curRecord["PW_COSTESTIMATE"], 0 );
						$curRecord["PW_COSTESTIMATE"] = implode( " ", array($curRecord["PW_COSTESTIMATE"], $curRecord["PW_COSTCUR"]) );
					} else
						$curRecord["PW_COSTESTIMATE"] = null;

					$project_works[count($project_works)] = $curRecord;
				}
				foreach ( $totalCost as $cur=>$value )
					$totalCost[$cur]  = "<nobr>".implode( " ", array($value, $cur) )."</nobr>";

				$totalCost = implode( "<br>", $totalCost );

				@db_free_result( $qr );*/
	}

	$preprocessor = new print_preprocessor( $PM_APP_ID, $kernelStrings, $language );

	$preprocessor->assign( REPORT_TITLE, $pmStrings['pm_report_title'] );
	$preprocessor->assign( ERROR_STR, $errorStr );
	$preprocessor->assign( FATAL_ERROR, $fatalError );
	$preprocessor->assign( "pm_loc_str", $pmStrings );
	$preprocessor->assign( "pmStrings", $pmStrings );
	$preprocessor->assign( "currencyListStr", $currencyListStr );
	$preprocessor->assign( "dateDisplayFormat", DATE_DISPLAY_FORMAT );
	$preprocessor->assign ("currentTimestamp", convertTimestamp2Local( mktime()));

	if ( !$fatalError ) {
		$preprocessor->assign( "projData", $projData );
		$preprocessor->assign( "project_works", $project_works );
		$preprocessor->assign( "totalCost", $totalCost );
		$preprocessor->assign ( "currenciesListStr", $currenciesListStr);
		$preprocessor->assign( "showComplete", $showComplete );
		$preprocessor->assign( "managerName", $managerName );
	}

	$preprocessor->display( "projdetails.htm" );

?>
