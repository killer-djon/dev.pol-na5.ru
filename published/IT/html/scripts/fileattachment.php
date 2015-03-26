<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// File Attachment page script
	//

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
	$fileList = array();

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];
	$project_ids = null;
	$project_names= null;

	$sortingFunctions = array( "screenname"=>"sortByName", "size"=>"sortBySize", "filedate"=>"sortByDate",
								"dispnum"=>"sortByNum", "I_SUMMARY"=>"sortBySummary" );

	function common_compare( $a, $b )
	{
		global $fileSortingOrder;

		if ($a == $b)
			return 0;

		if ( $fileSortingOrder == "asc")
			return ($a > $b) ? 1 : -1;
		else
			return ($a < $b) ? 1 : -1;
	}

	function sortByName( $a, $b ) { return common_compare( strtoupper($a["FILE_INFO"]["name"]), strtoupper($b["FILE_INFO"]["name"]) ); }

	function sortBySize( $a, $b ) { return common_compare( $a["FILE_INFO"]["size"], $b["FILE_INFO"]["size"] ); }
	function sortByDate( $a, $b ) { return common_compare( $a["FILE_INFO"]["filedate"], $b["FILE_INFO"]["filedate"] ); }
	function sortBySummary( $a, $b ) { return common_compare( strtoupper($a["I_DESC"]), strtoupper($b["I_DESC"]) ); }

	function sortByNum( $a, $b )
	{
		$aNum = sprintf( "%s.%s", $a["PW_ID"], $a["I_NUM"] );
		$bNum = sprintf( "%s.%s", $b["PW_ID"], $b["I_NUM"] );

		return common_compare( $aNum, $bNum );
	}

	function it_sortFileList( $fileList, $sorting )
	{
		global $fileSortingOrder;
		global $sortingFunctions;

		$sortData = parseSortStr( $sorting );
		$fileSortingOrder = $sortData["order"];
		$functionName = $sortingFunctions[$sortData["field"]];

		if ( strlen($functionName) )
			uasort( $fileList, $functionName );

		return $fileList;
	}
	
	$btnIndex = getButtonIndex( array(BTN_RETURN), $_POST );

	switch ( $btnIndex ) {
		case 0 : redirectBrowser( PAGE_IT_ISSUELIST, array( "P_ID"=>$_SESSION['IT_LIST_PID'] ) );
	}


	switch( true ) {
		case true : {
						if ( $fatalError )
							break;

						if ( !isset( $sorting ) )
							$sorting = "screenname asc";
						else
							$sorting = base64_decode( $sorting );

						// Load projects
						//
						$projectList = it_getUserAssignedProjects( $currentUser, $itStrings, IT_DEFAILT_MAX_PROJNAME_LEN, true, IT_DEFAILT_MAX_CUSTNAME_LEN, true, true );
						if ( PEAR::isError($projectList) ) {
							$errorStr = $itStrings[IT_ERR_LOADPROJECTS];

							$fatalError = true;
							break;
						}

						$P_IDs = array_keys($projectList);

						if ( !isset($P_ID) || !strlen($P_ID) || !in_array($P_ID, $P_IDs) ) {
							$P_ID = it_loadCommonSetting( $currentUser, IT_ISSUELIST_PROJECT );

							if ( !strlen($P_ID) || !in_array($P_ID, $P_IDs) )
								$P_ID = $P_IDs[0];
						}

						it_saveCommonSetting( $currentUser, IT_ISSUELIST_PROJECT, $P_ID, $kernelStrings );

						$projmanData = it_getProjManData( $P_ID );
						if ( PEAR::isError($projmanData) ) {
							$errorStr = $itStrings[IT_ERR_LOADPROJMANDATA];

							$fatalError = true;
							break;
						}

						$userIsProjman = is_array($projmanData) && $projmanData["U_ID_MANAGER"] == $currentUser;

						// Load file list
						//
						$fileList_res = it_getAttachedFilesList( $P_ID, $currentUser, $kernelStrings, $itStrings );
						if ( PEAR::isError($fileList_res) ) {
							$errorStr = $fileList_res->getMessage();

							$fatalError = true;
							break;
						}

						$fileList = $fileList_res;
						$fileList = it_sortFileList( $fileList, $sorting );

						for ( $i = 0; $i < count($fileList); $i++ ) {
							$fileData = $fileList[$i];

							$fileInfo = $fileData["FILE_INFO"];
							$fileInfo["size"] = formatFileSizeStr( $fileInfo["size"] );
							$fileInfo["filedate"] = displayDate($fileInfo["filedate"] );
							$fileData["FILE_INFO"] = $fileInfo;
							$fileData["I_DESC"] = prepareStrToDisplay( $fileData["I_DESC"], true );

							$fileData["DISP_NUM"] = sprintf( "%s.%s", $fileData["PW_ID"], $fileData["I_NUM"] );

							if ( $fileData["TYPE"] == IT_FT_ISSUE ) {
								$params = array( "I_ID"=>$fileData["I_ID"], "fileName"=>base64_encode($fileInfo["name"]) );
								$fileURL = prepareURLStr( PAGE_IT_GETISSUEFILE, $params );
							} else {
								$params = array( "I_ID"=>$fileData["I_ID"], "ITL_ID"=>$fileData["ITL_ID"], "fileName"=>base64_encode($fileInfo["name"]) );
								$fileURL = prepareURLStr( PAGE_IT_GETTRANSITIONFILE, $params );
							}

							$fileData["FILE_LINK"] = sprintf( "<a href=\"%s\" target=\"_blank\">%s</a>", $fileURL, $fileInfo["screenname"] );

							$fileList[$i] = $fileData;
						}

						$fileKeys = array_keys( $fileList );
						for ( $i = 0; $i < count($fileKeys); $i++ )
							$fileList[$fileKeys[$i]]["index"] = $i;

						// Fill project and file lists
						//
						if ( is_array( $projectList ) )
							if ( !count($projectList) )
								$projectList[null] = $itStrings['fa_noprojects_item'];
							else {
								$project_ids = array_keys( $projectList );
								$project_names = array_values( $projectList );
							}

						break;
		}
	}

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['fa_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_IT_FILEATTACHMENT );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( SORTING_COL, $sorting );
	$preproc->assign( "genericLinkUnsorted", prepareURLStr( PAGE_IT_FILEATTACHMENT, array( "P_ID"=>$P_ID ) ) );
	$preproc->assign( "P_ID", $P_ID );
	$preproc->assign( HELP_TOPIC, "fileattachments.htm");

	if ( !$fatalError ) {
		$preproc->assign( "project_ids", $project_ids );
		$preproc->assign( "project_names", $project_names );

		$preproc->assign( "fileList", $fileList );
	}

	$preproc->display( "fileattachment.htm" );
?>