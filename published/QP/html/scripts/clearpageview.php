<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QP/qp.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QP";

	$processButtonTemplate = "javascript:processTextButton('%s', 'form')";

	define( 'EXPAND', 'expand' );
	define( 'COLLAPSE', 'collapse' );
	define( 'HIDE_FOLDER', 'hidefolders' );
	define( 'ACTION_DELETEFOLDER', 'DELETE' );

	pageUserAuthorization( $SCR_ID, $QP_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$qpStrings = $qp_loc_str[$language];
	$invalidField = null;
	$noAccessGranted = false;
	$folderChanged = false;
	$readOnly = false;
	$noBooks = false;

	if ( !isset($searchString) )
		$searchString = base64_decode(getAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_SEARCHSTRING', null, $readOnly ));

	if ( $searchString == "" )
		$searchString = null;

	if ( !isset( $prevSearchString ) )
		$prevSearchString = null;
	
	$canTools = checkUserFunctionsRights( $currentUser, "QP", APP_CANTOOLS_RIGHTS, $kernelStrings );
	$toolsMenu[$qpStrings["qpt_screen_long_name"]] = prepareURLStr(PAGE_QP_THEMES, array() );
	
	setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_SEARCHSTRING', base64_encode($searchString), $kernelStrings, $readOnly );

	$btnIndex = getButtonIndex( array( 'organizebtn', 'printbtn', 'bookbtn', 'showFoldersBtn', 'pagedeletebtn', 'previewbtn', 'bookdeletebtn', 'foldersbtn', 'publbtn', 'unpublbtn', 'copybtn' ), $_POST, false );

	switch (true)
	{
		case true :
					if ( isset( $currentBookID ) )
						$currentBookID = base64_decode( $currentBookID );

					// Load books list
					//

					$access = null;
					$hierarchy = null;
					$deletable = null;

					$books = $qp_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
															$access, $hierarchy, $deletable );

					if ( PEAR::isError($books) )
					{
						$fatalError = true;
						$errorStr = $books->getMessage();
						break;
					}

					if ( count( $books ) == 0 )
					{
						$fatalError = true;
						$noBooks = true;
						
						if ( $qp_treeClass->isRootIdentity( $currentUser, $kernelStrings ) )
							$addCreateButton = true;

						$errorStr = $qpStrings["qp_screen_nobooks_error"];
						break;
					}


					// Dermine current BookId
					//
					if ( ( !isset( $currentBookID ) || $currentBookID=="" ) || isset( $newCurrentBookID ) )
					{
						$currentBookID = getAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_CURRENT_BOOK', null, $readOnly );

						if ( !strlen( $currentBookID ) )
						{
							$keys = array_keys( $books );
							$currentBookID = $keys[0];
							setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_CURRENT_BOOK', base64_encode( $currentBookID ), $kernelStrings, $readOnly );
						}
						else
							$currentBookID = base64_decode( $currentBookID );
					}
					else
						setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_CURRENT_BOOK', base64_encode( $currentBookID ), $kernelStrings, $readOnly );

					if ( !in_array( $currentBookID, array_keys($books) ) )
					{
						$keys = array_keys( $books );
						$currentBookID = $keys[0];
						setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_CURRENT_BOOK', base64_encode( $currentBookID ), $kernelStrings, $readOnly );
					}

					$bookData = $books[$currentBookID];
					
					// Load current folder data
						//
						$folderData = $qp_pagesClass->getFolderInfo( $QPF_ID, $kernelStrings );
						if ( PEAR::isError( $folderData ) )
						{
							$folderData = array();
							$folderData["QPF_CONTENT"] = "";
							$folderData["QPF_MODIFYDATETIME"] ="";
						}

						$folderData["QPF_CONTENT"] = preg_replace( '/(<[^>]*?="{0,1})([^">]*)pageid:([^">]*?[^>]*>)/u', '$1'.'$2'."quickpages.php?currentBookID=".base64_encode( $currentBookID )."&curBOOK_ID=".'$3', $folderData["QPF_CONTENT"] );
						
						print $folderData["QPF_CONTENT"];
						
						$readOnly = false;
						$qp_pagesClass->setUserDefaultFolder( $currentUser, $QPF_ID, $kernelStrings, $readOnly );
						$folderChanged = true;
						
						print "\n<script>parent.setPagePublished(" . ($folderData["QPF_PUBLISHED"] ? "true" : "false") . ");</script>";
	}
	
?>	