<?php

	$get_key_from_url = true;

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

	$currentUser = "";

	$template_id = "default";

	//
	// Page variables setup
	//

	$language = LANG_ENG;

	$kernelStrings = $loc_str[$language];
	$qpStrings = $qp_loc_str[$language];

	$invalidField = null;
	$noAccessGranted = false;
	$folderChanged = false;

	$searchString = "";

	if ( isset( $currentBookID ) )
		$currentBookID = base64_decode( $currentBookID );
	else
	{
		echo "Invalid Book ID.";
		die();
	}

	switch (true) {
		case true :

					// Load books list
					//
					$books = $qp_ptreeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
															$access = null, $hierarchy = null, $deletable = null );

					if ( PEAR::isError($books) )
					{
						$fatalError = true;
						$errorStr = $folders->getMessage();
						break;
					}

					if ( count( $books ) == 0 )
					{
						$fatalError = true;
						$errorStr = $qpStrings.qp_book;
						$errorStr = "No books available.";
						break;
					}

					//
					// Dermine current BookId
					//
					if ( !in_array( $currentBookID, array_keys($books) ) )
					{
						echo "Invalid Book ID.";
						die();
					}

					$bookData = $books[$currentBookID];
					$qp_publicClass->currentBookID = $currentBookID;

					if ( $printMode == 0 )
					{
						$documents = unserialize( base64_decode( $doclist ) );

						$pages = qp_listPublicSelectedNotes( $documents, $currentBookID, $kernelStrings );

					}
					elseif ( $printMode == 1 )
					{

						$parent = base64_decode( $curQPF_ID ) == "AVAILABLEFOLDERS" ? TREE_ROOT_FOLDER : base64_decode( $curQPF_ID );

						$pages = $qp_publicClass->listFolders( $currentUser, $parent, $kernelStrings, 0, false,
													$access = null, $hierarchy = null, $deletable = null, null,
													null, false, null, true, null, false, $curQPF_ID == TREE_AVAILABLE_FOLDERS );

						if ( PEAR::isError($pages) )
						{
							$fatalError = true;
							$errorStr = $pages->getMessage();
							break;
						}

					} else {
						$pages = $qp_publicClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
													$access = null, $hierarchy = null, $deletable = null, null,
													null, false, null, true, null, false, $curQPF_ID == TREE_AVAILABLE_FOLDERS );

						if ( PEAR::isError($pages) )
						{
							$fatalError = true;
							$errorStr = $pages->getMessage();
							break;
						}
					}

					foreach( $pages as $key=>$value )
					{
						$value = (array) $value ;
						if ( $key == "AVAILABLEFOLDERS" )
						{
							$value["QPF_CONTENT"] = "";
							$value["QPF_NAME"] = $bookData->QPB_NAME;
							$value["QPF_TITLE"] = $bookData->QPB_TITLE;
						}
						else
							$value["QPF_CONTENT"] = str_replace ( "getpagefile.php?", "getpublicfile.php?DB_KEY=$DB_KEY&", $value["QPF_CONTENT"] );

						$pages[$key] = $value;
					}

					if ( $template_id != "default" )
					{
						$tpl = qn_getTemplate( $template_id, $currentUser, $kernelStrings );
						if ( PEAR::isError( $tpl ) )
						{
							$template_id = "default";
							break;
						}

						$tpl_strs = array();

						foreach( $folders as $folder )
						{
							foreach( $folder['LIST'] as $key=>$value )
							{
								$note = $value;
								$note['FOLDER_NAME'] = $folder['NAME'];

								$tpl_strs[] = qn_applyTemplate( $note, $tpl['QNT_HTML'] );
							}
						}
					}

	}


	$styleSet = "office";
	$preprocessor = new php_preprocessor( "classic", $kernelStrings, $language, $QP_APP_ID );

	$preprocessor->assign( ERROR_STR, $errorStr );
	$preprocessor->assign( FATAL_ERROR, $fatalError );
	$preprocessor->assign( "qpStrings", $qpStrings );
	$preprocessor->assign( 'pageTitle', $qnStrings['qp_screen_long_name'] );

	if ( !$fatalError ) {
		$preprocessor->assign( "pages", $pages );
		$preprocessor->assign( "printMode", $printMode );
		$preprocessor->assign( "bookData", $bookData );
	}

	if ( $template_id == "default" )
	{
		$preprocessor->display( "report_published.htm" );
	}
	else
	{
		$preprocessor->assign( "templated_strings", $tpl_strs );
		$preprocessor->display( "templated.htm" );
	}


?>
