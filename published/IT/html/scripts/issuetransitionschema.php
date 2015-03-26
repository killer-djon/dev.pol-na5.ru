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
	$projName = null;
	$workDescr = null;
	$transitions = array();
	$workflowFound = false;
	$workFound = false;

	if ( !isset($opener) )
		$opener = PAGE_IT_WORKFLOWMANAGER;

	//
	// Form handling
	//
	$btnIndex = getButtonIndex( array(BTN_RETURN, "addstatebtn", "savetemplatebtn", "selecttemplate", "templatelistbtn"), $_POST );

	switch ( $btnIndex ) {
		case 0 : redirectBrowser( $opener, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID ) );
		case 1 : redirectBrowser( PAGE_IT_ADDMODTRANSITION, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, ACTION=>ACTION_NEW, OPENER=>$opener ) );
		case 2 : redirectBrowser( PAGE_IT_SAVETEMPLATE, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, OPENER=>$opener ) );
		case 3 : redirectBrowser( PAGE_IT_SELECTTEMPLATE, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, OPENER=>$opener ) );
		case 4 : redirectBrowser( PAGE_IT_TEMPLATELIST, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, OPENER=>$opener ) );
	}

	switch ( true ) {
		case true : {
						if ( $fatalError )
							break;

						// Check if work exists
						//
						$res = it_workExists( $P_ID, $PW_ID );
						if ( PEAR::isError($res) ) {
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
							$fatalError = true;

							break;
						}

						if ( !$res ) {
							$errorStr = $itStrings[IT_ERR_WORKNOTFOUND];
							$fatalError = true;

							break;
						}

						// Load project and work information
						//
						$projName = it_getProjectName( $P_ID, null, true, IT_DEFAILT_MAX_CUSTNAME_LEN );
						if ( PEAR::isError($projName) ) {
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
							$fatalError = true;

							break;
						}

						// Check user rights for this page
						//
						$projmanData = it_getProjManData( $P_ID );
						if ( PEAR::isError($projmanData) ) {
							$errorStr = $itStrings[IT_ERR_LOADPROJMANDATA];
							$fatalError = true;

							break;
						}

						if ( $projmanData["U_ID_MANAGER"] != $currentUser && !UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($currentUser, $P_ID, $kernelStrings ), array( UR_TREE_FOLDER  )  ) )
						{
							$errorStr = $kernelStrings[ERR_GENERALACCESS];
							$fatalError = true;

							break;
						}

						$workDescr = it_getWorkDescription( $P_ID, $PW_ID );
						if ( PEAR::isError($workDescr) ) {
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
							$fatalError = true;

							break;
						}

						// Process deletion
						//
						if ( isset($action) && $action == ACTION_DELETE ) {
							$transdata = array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "ITS_NUM"=>$ITS_NUM );
							$res = it_deleteIssueTransition( $transdata, $kernelStrings, $itStrings );
							if ( PEAR::isError($res) )
								$errorStr = $res->getMessage();

							$action = ACTION_EDIT;
						}

						$params = array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "ITS_NUM"=>IT_END_STATUS );

						$transSchema = it_loadIssueTransitionSchema( $P_ID, $PW_ID, $itStrings, true );
						if ( PEAR::isError($transSchema) ) {
							$errorStr = $transSchema->getMessage();

							$fatalError = true;
							break;
						}

						$workflowFound = count($transSchema);

						if ( !$workflowFound )
							break;

						foreach( $transSchema as $index=>$stateData ) {
							$stateData["ITS_STYLE"] = it_getIssueHTMLStyle( $stateData["ITS_COLOR"] );
							if ( strlen($stateData["ITS_COLOR"]) ) {
								$styleData = $it_styles[$stateData["ITS_COLOR"]];
								$stateData["ITS_COLOR"] = $itStrings[$styleData[2]];
							}
							$stateData["ALLOW_ASSIGNMENT"] = ($stateData["ITS_ASSIGNMENTOPTION"] != IT_ASSIGNMENTOPT_NOTAPPLICABLE && $stateData["ITS_ASSIGNMENTOPTION"] != IT_ASSIGNMENTOPT_NOTREQUIRED) ? 1 : 0;

							$recOption = $stateData["ITS_ASSIGNMENTOPTION"];
							if ( strlen($recOption) ) {
								if ( isset($it_assignment_chart_names[(int)$recOption]) )
									$stateData["ITS_ASSIGNMENTOPTION"] = $itStrings[$it_assignment_chart_names[(int)$recOption]];
								else
									$stateData["ITS_ASSIGNMENTOPTION"] = null;
							}
							else
								$stateData["ITS_ASSIGNMENTOPTION"] = null;

							if ( $stateData["U_ID_ASSIGNED"] != IT_SENDER_OPTION ) {
								if ( strlen($stateData["U_ID_ASSIGNED"]) )
									$stateData["U_ID_ASSIGNED"] = getArrUserName( $stateData, true );
								else
									$stateData["U_ID_ASSIGNED"] = $itStrings['cw_none_label'];
							}
							else
								$stateData["U_ID_ASSIGNED"] = sprintf( "&lt;%s&gt;", $itStrings['cw_sender_label'] );

							$urlParams = array( "P_ID"=> $P_ID, "PW_ID"=> $PW_ID, ACTION=>ACTION_EDIT, "ITS_NUM"=>$stateData["ITS_NUM"], OPENER=>$opener );
							$row_url = prepareURLStr( PAGE_IT_ADDMODTRANSITION, $urlParams );
							$stateData["ROW_URL"] = $row_url;

							$transSchema[$index] = $stateData;
						}

						$startTransitionURL = prepareURLStr( PAGE_IT_ADDMODTRANSITION, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID,
																						ACTION=>ACTION_EDIT, "ITS_NUM"=>IT_FIRST_STATUS, OPENER=>$opener ) );
						$endTransitionURL = $row_url;

						$colNum = 0;
						$rowNum = 0;
						$chartData = it_getTransitionShemaChartData( $transSchema, $colNum, $rowNum );

						$startStatusName = null;
						$endStatusName = null;

						foreach( $transSchema as $index=>$stateData ) {
							$stateData["ITS_STATUS"] = prepareStrToDisplay( $stateData["ITS_STATUS"], true );

							if ( is_null($startStatusName) )
								$startStatusName = $stateData["ITS_STATUS"];

							$transSchema[$index] = $stateData;
						}

						$endStatusName = $stateData["ITS_STYLE"].$stateData["ITS_STATUS"];

						$openedFromIssueList = $opener == PAGE_IT_ISSUELIST;
		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['cw_page_title'] );
	$preproc->assign( FORM_LINK, prepareURLStr( PAGE_IT_ISSUETRANSITIONSCHEMA, array() ) );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "P_ID", $P_ID );
	$preproc->assign( OPENER, $opener );

	$preproc->assign( HELP_TOPIC, "transitionschema.htm");

	if ( !$fatalError ) {
		$preproc->assign( "projName", prepareStrToDisplay($projName, true) );

		$preproc->assign( "PW_ID", $PW_ID );
		$preproc->assign( "workDescr", prepareStrToDisplay($workDescr, true) );
		$preproc->assign( "chartData", $chartData );
		$preproc->assign( "colNum", $colNum );
		$preproc->assign( "lastRow", $rowNum-1 );
		$preproc->assign( "transSchema", $transSchema );
		$preproc->assign( "startTransitionURL", $startTransitionURL );
		$preproc->assign( "endTransitionURL", $endTransitionURL );
		$preproc->assign( "startStatusName", $startStatusName );
		$preproc->assign( "endStatusName", $endStatusName );
		$preproc->assign( "workflowFound", $workflowFound );

		$preproc->assign( "openedFromIssueList", $openedFromIssueList );
	}

	$preproc->display( "issuetransitionschema.htm" );

?>