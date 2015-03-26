<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "IL";

	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];

	//
	// Form handling
	//
	$btnIndex = getButtonIndex( array("returnbtnctrl", "deletebtnctrl", "setasdefaultbtn"), $_POST );

	switch ( $btnIndex ) {
		case 0 : redirectBrowser( PAGE_IT_TEMPLATELIST, array('P_ID'=>$P_ID) );
		case 1 : {
					$res = it_deleteWorkflowTemplate( $ITT_ID, $kernelStrings, $itStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();

						$fatalError = true;
						break;
					}

					redirectBrowser( PAGE_IT_TEMPLATELIST, array('P_ID'=>$P_ID) );
			}
		case 2 : {
					$res = it_setTemplateAsDefault( $ITT_ID, $kernelStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();

						$fatalError = true;
						break;
					}

					redirectBrowser( PAGE_IT_TEMPLATELIST, array('P_ID'=>$P_ID) );
			}
	}

	switch ( true ) {
		case true : {	
					$res = it_templateExists( $ITT_ID );
					if ( PEAR::isError($res) ) {
						$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

						$fatalError = true;
						break;
					}

					if ( PEAR::isError($res) ) {
						$errorStr = $itStrings[IT_ERR_TEMPLATENOTFOUND];

						$fatalError = true;
						break;
					}

					$templateData = db_query_result( $qr_it_select_template, DB_ARRAY, array("ITT_ID"=>$ITT_ID) );
					if ( PEAR::isError($templateData) ) {
						$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

						$fatalError = true;
						break;
					}

					$templateSchema = it_loadTemplateIssueTransitionSchema( $ITT_ID, $itStrings );
					if ( PEAR::isError($templateSchema) ) {
						$errorStr = $templateSchema->getMessage();

						$fatalError = true;
						break;
					}

					$startStatusName = null;
					$endStatusName = null;

					foreach( $templateSchema as $index=>$stateData ) {
						$stateData["ITS_STATUS"] = $stateData["ITTS_STATUS"];
						$stateData["ITS_ALLOW_DEST"] = $stateData["ITTS_ALLOW_DEST"];
						$stateData["ITS_ALLOW_EDIT"] = $stateData["ITTS_ALLOW_EDIT"];
						$stateData["ITS_ALLOW_DELETE"] = $stateData["ITTS_ALLOW_DELETE"];
						$stateData["ITS_DEFAULT_DEST"] = $stateData["ITTS_DEFAULT_DEST"];

						$stateData["ITS_STYLE"] = it_getIssueHTMLStyle( $stateData["ITTS_COLOR"] ); 
						if ( isset($it_styles[$stateData["ITTS_COLOR"]]) ) {
							$styleData = $it_styles[$stateData["ITTS_COLOR"]];
							$stateData["ITTS_COLOR"] = $itStrings[$styleData[2]]; 
						}
						$stateData["ALLOW_ASSIGNMENT"] = ($stateData["ITTS_ASSIGNMENTOPTION"] != IT_ASSIGNMENTOPT_NOTAPPLICABLE && $stateData["ITTS_ASSIGNMENTOPTION"] != IT_ASSIGNMENTOPT_NOTREQUIRED) ? 1 : 0;

						if ( is_null($startStatusName) )
							$startStatusName = $stateData["ITS_STATUS"];

						$recOption = $stateData["ITTS_ASSIGNMENTOPTION"];
						if ( strlen($recOption) ) {
							if ( isset($it_assignment_chart_names[(int)$recOption]) )
								$stateData["ITS_ASSIGNMENTOPTION"] = $itStrings[$it_assignment_chart_names[(int)$recOption]];
							else
								$stateData["ITS_ASSIGNMENTOPTION"] = null;
						}
						else
							$stateData["ITS_ASSIGNMENTOPTION"] = null;

						if ( $stateData["ITTS_ASSIGNED"] != IT_SENDER_OPTION ) {
							if ( strlen($stateData["ITTS_ASSIGNED"]) )
								$stateData["U_ID_ASSIGNED"] = getUserName( $stateData["ITTS_ASSIGNED"], true ); 
							else 
								$stateData["U_ID_ASSIGNED"] = $itStrings['wt_none_label'];
						}
						else
							$stateData["U_ID_ASSIGNED"] = sprintf( "&lt;%s&gt;", $itStrings['wt_sender_item'] );

						$templateSchema[$index] = $stateData;
					}

					$endStatusName = $stateData["ITS_STYLE"].$stateData["ITS_STATUS"];

					$colNum = 0;
					$rowNum = 0;
					$chartData = it_getTransitionShemaChartData( $templateSchema, $colNum, $rowNum );
		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['wt_page_title'] );
	$preproc->assign( FORM_LINK, prepareURLStr( PAGE_IT_TEMPLATE, array() ) );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "ITT_ID", $ITT_ID ); 

	if ( !$fatalError ) {
			$preproc->assign( "startStatusName", $startStatusName );
			$preproc->assign( "endStatusName", $endStatusName );
			$preproc->assign( "templateData", $templateData );
			$preproc->assign( "chartData", $chartData );
			$preproc->assign( "colNum", $colNum );
			$preproc->assign( "lastRow", $rowNum-1 );
			$preproc->assign( "transSchema", $templateSchema );
	}

	$preproc->display( "workflowtemplate.htm" );
?>