<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;	
	$SCR_ID = "IL";

	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];
	$invalidField = null;
	$project_ids = null;
	$project_names= null;
	$reportLoaded = false;
	if ( !isset($prevV_ID) )
		$prevV_ID = null;
	if ( !isset($prevP_ID) )
		$prevP_ID = null;

	define( "COLS_PER_PAGE", 6 );

	if( !isset($firstIndex) || !strlen($firstIndex) )
		$firstIndex = 0;

	switch ( true ) {
		case true : {
						if ( $fatalError )
							break;

						// Load projects
						//
						$qr = db_query( $qr_it_select_all_projects_customers_activefirst, array() );
						if ( PEAR::isError($qr) ) {
							$errorStr =  $kernelStrings[ERR_QUERYEXECUTING];

							$fatalError = true;
							break;
						}

						$projectList[IT_ISM_ALL_PROJECTS] = sprintf("&lt;%s&gt;", $itStrings['iss_allprojects_item']);
						$closedFound = false;
						while ( $row = db_fetch_array($qr) ) {
							$row = prepareArrayToDisplay( $row, array("P_DESC") );
							$row["P_DESC"] = strTruncate( $row["P_DESC"], 50);

							$row["P_DESC"] = implode( ". ", array(strTruncate( $row["C_NAME"], 30 ), $row["P_DESC"]) );
							
							if ( $row["COMPLETE"] ) {
								$row["P_DESC"] .=  " (" . $itStrings['iss_complproj_text'] . ")";

								if ( !$closedFound ) {
									$closedFound = true;
										$projectList[null] = $itStrings['iss_complproj_separator'];
								}
							}

							$projectList[$row['P_ID']] = $row["P_DESC"];
						}

						db_free_result( $qr );

						$project_ids = array_keys( $projectList );
						$project_names = array_values( $projectList );

						if ( !isset($P_ID) )
							$P_ID = IT_ISM_ALL_PROJECTS;

						// Allowed views list
						//
						$viewIDs = it_listAllowedISMViews( $P_ID ); 
						$viewNames = array();
						for ( $i = 0; $i < count($viewIDs); $i++ )
							$viewNames[] = $itStrings[$IT_ISM_VIEW_NAMES[$viewIDs[$i]]];

						if ( (!isset($V_ID) && is_array($viewIDs) && count($viewIDs)) || !in_array($V_ID, $viewIDs) )
							$V_ID = $viewIDs[0];

						if ( $prevV_ID != $V_ID || $prevP_ID != $P_ID ) {
							$firstIndex = 0;
							$searchCOL_ID = null;
						}

						// Load report data
						//
						$rowNum = it_getISMData( $P_ID, $V_ID, $rowNames, $colNames, $reportData, $itStrings, $kernelStrings );
						if ( PEAR::isError($rowNum) ) {
							$errorStr = $rowNum->getMessage();

							break;
						} else
							$reportLoaded = true;

						if ( $rowNum ) {
							foreach( $rowNames as $rowID=>$rowName )
								$rowNames[$rowID] = array( "name"=>prepareStrToDisplay(strTruncate($rowName, 60), true) );

							$ISM_Columns = $IT_ISM_COLUMN_TITLES[$V_ID];
							$titleColName = $itStrings[$ISM_Columns[0]];
							$titleRowName = $itStrings[$ISM_Columns[1]];
						}

						if ( isset( $edited) && isset($searchCOL_ID) ) {
							$index = 0;

							foreach( $colNames as $colData_id=>$colData ) { 
								if ( $colData_id == stripSlashes($searchCOL_ID) ) {
									$firstIndex = $index;
									break;
								}

								$index++;
							}
						}

						$allNames = $colNames;
						$maxDataCount = count($allNames);

						$colNames = array_slice( $colNames, $firstIndex, COLS_PER_PAGE );
						$dataCount = count($colNames);

						$prevIndex = $firstIndex - COLS_PER_PAGE;
						if ( $prevIndex < 0 ) 
							$prevIndex = 0;

						$nextIndex = $firstIndex + COLS_PER_PAGE;
						if ( $nextIndex > $maxDataCount ) 
							$nextIndex = $maxDataCount;

						$rowNamesDump = array();
						if ( is_array($rowNames) )
							$index = 0;
							foreach( $rowNames as $ROW_ID=>$ROW_DATA ) {
								$dataFound = false;
								foreach( $colNames as $COL_ID=>$COL_DATA ) {
									if ( isset($reportData[$ROW_ID][$COL_ID]) && strlen($reportData[$ROW_ID][$COL_ID]) ) {
										$data = $reportData[$ROW_ID][$COL_ID];
										$dataFound = true;
									} else
										$data = null;

									if ( $ROW_ID == IT_ISM_ROWID_FIX )
										$urlROW_ID = null;
									else 
										$urlROW_ID = $ROW_ID;

									$urlCOL_ID = substr( $COL_ID, strlen(IT_ISM_COLID_FIX) );

									$printPageName = sprintf( "../../reports/%s", PAGE_IT_STATISTICS);
									$URL = prepareURLStr($printPageName, array(IT_ISM_REPORT_PARAM1=>base64_encode($urlROW_ID), 
																				IT_ISM_REPORT_PARAM2=>base64_encode($urlCOL_ID),
																				"V_ID"=>$V_ID, "P_ID"=>$P_ID,
																				"col_title"=>base64_encode($COL_DATA),
																				"row_title"=>rawurlencode(base64_encode($ROW_DATA["name"])) ));
									$reportData[$ROW_ID][$COL_ID] = array( "data"=>$data, "url"=>$URL );
								}

							if ( $dataFound ) {
								$ROW_DATA["index"] = $index;
								$rowNamesDump[$ROW_ID] = $ROW_DATA;
								$index++;
							}
						}
				
						$rowNames = $rowNamesDump;

						if ( is_array($colNames) )
							foreach( $colNames as $colID=>$colName ) {
								$shortName = prepareStrToDisplay( $colName, true);

								$longName = prepareStrToDisplay($colName, true);
								$colNames[$colID] = array( "short"=>$shortName, "long"=>$longName );
						}
		}
	}

	//
	// Form handling
	//
	$btnIndex = getButtonIndex( array(BTN_RETURN), $_POST );

	switch ( $btnIndex ) {
		case 0 : redirectBrowser( PAGE_IT_ISSUELIST, array( "P_ID"=>$_SESSION['IT_LIST_PID'] ) );
	}
	
	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['iss_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_IT_STATISTICS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "issuestatmatrix.htm");

	if ( !$fatalError ) {
		$preproc->assign( "P_ID", $P_ID );
		$preproc->assign( "V_ID", $V_ID );

		$preproc->assign( "rowNum", $rowNum );

		$preproc->assign( "project_ids", $project_ids );
		$preproc->assign( "project_names", $project_names );

		$preproc->assign( "viewIDs", $viewIDs );
		$preproc->assign( "viewNames", $viewNames );

		$preproc->assign( "reportLoaded", $reportLoaded );
		$preproc->assign( "rowNames", $rowNames );
		$preproc->assign( "colNames", $colNames );
		$preproc->assign( "reportData", $reportData );		

		$preproc->assign( "colNum", count($colNames) );

		if ( isset($titleColName) )
			$preproc->assign( "titleColName", $titleColName );

		if ( isset($titleRowName) )
			$preproc->assign( "titleRowName", $titleRowName );

		$showPrevLink = $firstIndex > 0;
		$showNextLink = $firstIndex < ($maxDataCount-COLS_PER_PAGE);

		$colCount = $dataCount+1;
		if ($showPrevLink) $colCount ++;
		if ($showNextLink) $colCount ++;

		$preproc->assign( "dataCount", $dataCount );
		$preproc->assign( "totalColCount", $colCount );
		
		$preproc->assign( "firstIndex", $firstIndex );

		$prevParams = array( "firstIndex"=>$prevIndex, "P_ID"=>$P_ID, "V_ID"=>$V_ID, "prevV_ID"=>$V_ID, "prevP_ID"=>$P_ID );
		$nextParams = array( "firstIndex"=>$nextIndex, "P_ID"=>$P_ID, "V_ID"=>$V_ID, "prevV_ID"=>$V_ID, "prevP_ID"=>$P_ID );

		$preproc->assign( "prev_pageLink", ($showPrevLink) ? prepareURLStr( PAGE_IT_STATISTICS, $prevParams ) : null );
		$preproc->assign( "next_pageLink", ($showNextLink) ? prepareURLStr( PAGE_IT_STATISTICS, $nextParams ) : null );

		if ( isset($allNames) ) {
			$allNames = array_merge( array("-1"=>sprintf("&lt;%s&gt;", $kernelStrings['usa_select_item'] )), $allNames );
			$preproc->assign( "col_ids", array_keys($allNames) );
			$preproc->assign( "col_count", count($allNames)-1 );
			$preproc->assign( "col_names", array_values($allNames) );
		}
	}

	$preproc->display( "statistics.htm" );
?>