<?php

	if ( !isset( $_POST["DB_KEY"] ) && !isset( $_GET["DB_KEY"] ) )
		die( "No valid DB KEY detected." );

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

	//
	// Page variables setup
	//

	$language = LANG_ENG;

	$kernelStrings = $loc_str[$language];
	$qpStrings = $qp_loc_str[$language];
	$encoding = $wbs_languages[strtolower($language)][WBS_ENCODING];

	$invalidField = null;
	$noAccessGranted = false;
	$folderChanged = false;
	$statisticsMode = false;
	
	$metric = metric::getInstance();

	if ( isset( $pdfout ) )
	{
		define('FPDF_FONTPATH','../../../../kernel/includes/modules/html2fpdf/font/');
		require_once( "../../../../kernel/includes/modules/html2fpdf/html2fpdf.php" );
	}

	if ( !isset( $BookID ) )
	{
		echo "Invalid Book ID.";
		die();
	}

	if ( isset( $_POST["DB_KEY"] ) )
		$ourDB_KEY = $_POST["DB_KEY"];
	else
	if ( isset( $_GET["DB_KEY"] ) )
		$ourDB_KEY = $_GET["DB_KEY"];

	session_start( );

	$oldDB_KEY = $DB_KEY;
	$DB_KEY = $ourDB_KEY;
	
	$metric->addAction(base64_decode($DB_KEY), "", 'QP', 'OPENPAGE', 'PUBLISH');

	switch (true) {
		case true :

					//
					// Dermine current internal QPB_ID
					//

					$textBookID = $BookID;
					$currentBookID = qp_getID( "book", $textBookID );

					if ( PEAR::isError( $currentBookID ) )
					{
						$fatalError = true;
						$errorStr = $currentBookID->getMessage();
						break;
					}

					if ( isset( $PageID ) )
					{
						$textPageID = $PageID;
						$curQPF_ID = qp_getID( "page", $textPageID, $currentBookID );
						if ( PEAR::isError( $curQPF_ID ) )
						{
							$fatalError = true;
							$errorStr = $currentBookID->getMessage();
							break;
						}
					}

					if ( !isset($searchString) )
						$searchString = base64_decode(getAppUserCommonValue( $QP_APP_ID, base64_decode( $DB_KEY ).$textBookID, 'QP_SEARCHSTRING', null, true ));

					if ( $searchString == "" )
						$searchString = null;

					if ( !isset( $prevSearchString ) )
						$prevSearchString = null;

					setAppUserCommonValue( $QP_APP_ID, base64_decode( $DB_KEY ).$textBookID, 'QP_SEARCHSTRING', base64_encode($searchString), $kernelStrings, true );

					// Load books list
					//
					$access = null;
					$hierarchy = null;
					$deletable = null;

					$books = $qp_ptreeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
															$access, $hierarchy, $deletable );

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
					if ( !in_array( $currentBookID, array_keys($books) )
							|| ( ( !isset( $_SESSION['ENABLE_PREVIEW'] ) || $_SESSION['ENABLE_PREVIEW'] != $currentBookID ) && $books[$currentBookID]->QPB_PUBLISHED != 1 )
					)
					{
						echo "Invalid Book ID.";
						die();
					}

					if ( isset( $previewTheme ) && isset( $_SESSION['ENABLE_PREVIEW'] ) )
						$_SESSION['PREVIEW_THEME'] = $previewTheme;

					$bookData = $qp_treeClass->getFolderInfo( $currentBookID, $kernelStrings );

					if ( PEAR::isError($bookData) )
					{
						die( $errorStr = $res->getMessage() );
						break;
					}

					$bookParams = new wbsParameters( $qp_book_data_schema );

					$bookParams->loadFromXML( $bookData["QPB_PROPERTIES"], $kernelStrings, true, null );

					$bookProperties = $bookParams->getValuesArray();
					$bookProperties["codepage"] = $encoding;

					if ( isset( $bookProperties["language"] ) )
					{
						$language = $bookProperties["language"];

						$kernelStrings = $loc_str[$language];
						$qpStrings = $qp_loc_str[$language];
					}

					$useDefault = false;

					if ( $bookProperties["auth"] == 1 )
					{
						qp_publishedBookCheckLogin( $DB_KEY, $textBookID );
					}


					if ( isset ( $bookData["QPB_THEME"] ) && !is_null( $bookData["QPB_THEME"] ) )
					{
						$theme = qp_getTheme( $currentUser, $bookData["QPB_THEME"], $kernelStrings );

						if ( PEAR::isError( $theme ) )
						{
							$errorStr = $res->getMessage();
							$fatalError=true;
							break;
						}

						if ( is_null( $theme ) || $theme["QPT_SHARED"] == 0 )
							$useDefault = true;
					}
					else
						$useDefault = true;

					if ( isset( $_SESSION['PREVIEW_THEME'] ) && $_SESSION['PREVIEW_THEME'] != null )
						$previewTheme = ( $_SESSION['PREVIEW_THEME'] == 'DEFAULT' ) ? $_SESSION['PREVIEW_THEME'] : intval( $_SESSION['PREVIEW_THEME'] );

					if ( isset( $previewTheme ) && $previewTheme != "DEFAULT" && $previewTheme != "" )
					{
						$themeNum = intval( $previewTheme );

						$theme = qp_getTheme( $currentUser, $themeNum, $kernelStrings );

						if ( PEAR::isError( $theme ) )
						{
							$errorStr = $res->getMessage();
							$fatalError=true;
							break;
						}

						if ( is_null( $theme ) )
						{
							$errorStr = $qpStrings["qpt_wrong_theme_message"];;
							$fatalError=true;
							break;
						}
					}
					else
					if ( $useDefault || ( isset( $previewTheme ) && $previewTheme == "DEFAULT" ) )
					{
						$theme["QPT_HEADER"] ="";
						$theme["QPT_PROPERTIES"] = "";
						$theme["QPT_TYPE"] = 1;
					}

					$publishParams = new wbsParameters( $qp_publish_data_schema );

					$publishParams->loadFromXML( $theme["QPT_PROPERTIES"], $kernelStrings, true, null );

					$properties = $publishParams->getValuesArray();

					if ( isset( $properties["language"] ) )
					{
						$language = $properties["language"];

						$kernelStrings = $loc_str[$language];
						$qpStrings = $qp_loc_str[$language];
					}

					$qp_publicClass->currentBookID = $currentBookID;

	}

	//
	// Determine active folder
	//
	if ( !isset( $curQPF_ID ) )
	{
		$curQPF_ID = $qp_publicClass->getUserDefaultFolder( base64_decode( $DB_KEY ).$textBookID, $kernelStrings, true, false );

		if ( $curQPF_ID == TREE_AVAILABLE_FOLDERS )
		{
			$statisticsMode = true;
			setAppUserCommonValue( $QP_APP_ID, base64_decode( $DB_KEY ).$textBookID, 'QP_STATISTICSMODE', $statisticsMode, $kernelStrings, true );
		}
		else
			$statisticsMode = false;
	}
	else
	{
			if ( $curQPF_ID != TREE_AVAILABLE_FOLDERS )
			{
				$qp_publicClass->setUserDefaultFolder( base64_decode( $DB_KEY ).$textBookID, $curQPF_ID, $kernelStrings, true );

				$folderChanged = true;
				$statisticsMode = false;

				setAppUserCommonValue( $QP_APP_ID, base64_decode( $DB_KEY ).$textBookID, 'QP_STATISTICSMODE', $statisticsMode, $kernelStrings, true );

				if ( $curQPF_ID != TREE_AVAILABLE_FOLDERS )
					$qp_publicClass->expandPathToFolder( $curQPF_ID, base64_decode( $DB_KEY ).$textBookID, $kernelStrings );

			}
			else
			{
				$statisticsMode = true;
				setAppUserCommonValue( $QP_APP_ID, base64_decode( $DB_KEY ).$textBookID, 'QP_STATISTICSMODE', $statisticsMode, $kernelStrings, true );

				$qp_publicClass->setUserDefaultFolder( base64_decode( $DB_KEY ).$textBookID, $curQPF_ID, $kernelStrings, true );
			}
	}

	if ( isset($action) && !$fatalError )
		switch ( $action )
		{
			case EXPAND :
			case COLLAPSE :

							if ( isset( $cPageID ) )
							{
								$decodedID = qp_getID( "page", $cPageID, $currentBookID );
								if ( PEAR::isError( $decodedID ) )
									break;
							}
							else
								break;

							$qp_publicClass->setCookiesFolderCollapseValue( $DB_KEY, $decodedID, $action == COLLAPSE );

							if ( $action == COLLAPSE )
							{
								if ( $decodedID != TREE_AVAILABLE_FOLDERS )
								{
									if ( $qp_publicClass->isChildOf( $curQPF_ID, $decodedID, $kernelStrings ) )
										$curQPF_ID = $decodedID;
								} else
									$curQPF_ID = $decodedID;

								$qp_publicClass->setUserDefaultFolder( base64_decode( $DB_KEY ).$textBookID, $curQPF_ID, $kernelStrings, true );

								$curQPF_ID = $qp_publicClass->getUserDefaultFolder( base64_decode( $DB_KEY ).$textBookID, $kernelStrings, true, true );

								if ( $curQPF_ID == TREE_AVAILABLE_FOLDERS )
								{
									$statisticsMode = true;
									setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_STATISTICSMODE', $statisticsMode, $kernelStrings, true );

									$qp_publicClass->setUserDefaultFolder( base64_decode( $DB_KEY ).$textBookID, $curQPF_ID, $kernelStrings, true );
								}
							}

							break;

			case HIDE_FOLDER :

							$foldersHidden = true;
							setAppUserCommonValue( $QP_APP_ID, base64_decode( $DB_KEY ).$textBookID, 'QP_FOLDERSHIDDEN', $foldersHidden, $kernelStrings, true );
							break;

		}

	if ( $prevSearchString != $searchString )
		$folderChanged = true;

	$btnIndex = getButtonIndex( array( 'showFoldersBtn', 'foldersbtn' ), $_POST, false );

	if ( !$fatalError )
	{
		switch ($btnIndex)
		{
			case 'showFoldersBtn' :

					$foldersHidden = false;
					setAppUserCommonValue( $QP_APP_ID, base64_decode( $DB_KEY ).$textBookID, 'QP_FOLDERSHIDDEN', $foldersHidden, $kernelStrings, true );
					break;

			case 'foldersbtn' :
					$searchString=null;
					setAppUserCommonValue( $QP_APP_ID, base64_decode( $DB_KEY ).$textBookID, 'QP_SEARCHSTRING', base64_encode($searchString), $kernelStrings, true );

		}
	}

	$foldersHidden = getAppUserCommonValue( $QP_APP_ID,  base64_decode( $DB_KEY ).$textBookID, 'QP_FOLDERSHIDDEN', null, true );

	switch (true)
	{

		case true :

				if ( $fatalError )
					break;

				if ( $theme["QPT_TYPE"] == 1 )
				{

					// Load folder list
					//
					if ( is_null( $searchString ) )
					{


						$access = null;
						$hierarchy = null;
						$deletable = null;
						$addavailableFoldersP = false;

						$folders = $qp_publicClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
															$access, $hierarchy, $deletable, null,
															null, false, null, $addavailableFoldersP, null, false, $curQPF_ID == TREE_AVAILABLE_FOLDERS );

						if ( PEAR::isError($folders) )
						{
							$fatalError = true;
							$errorStr = $folders->getMessage();
							break;
						}

						if ( !count( $folders ) )
						{
							$curQPF_ID = TREE_AVAILABLE_FOLDERS;
							$statisticsMode = true;
							setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );
							$qp_pagesClass->setUserDefaultFolder( $currentUser, $curQPF_ID, $kernelStrings, $readOnly );
						}
						else if ( !in_array( $curQPF_ID, array_keys( $folders ) ) || $curQPF_ID == TREE_AVAILABLE_FOLDERS || $statisticsMode )
						{
							$ids = array_keys( $folders );
							$curQPF_ID = $ids[0];
							$statisticsMode = false;
							setAppUserCommonValue( $QP_APP_ID, $currentUser, 'QP_STATISTICSMODE', $statisticsMode, $kernelStrings, $readOnly );
							$qp_pagesClass->setUserDefaultFolder( $currentUser, $curQPF_ID, $kernelStrings, $readOnly );
						}

						// Prepare folder list to display
						//
						$collapsedFolders = $qp_publicClass->listCookiesCollapsedFolders( $DB_KEY );
						if ( !is_array( $collapsedFolders ) )
						{
							foreach ( $folders as $QPF_ID=>$folderData )
								$qp_publicClass->setCookiesFolderCollapseValue( $DB_KEY, $QPF_ID, qp_hasItChilds( $hierarchy, $QPF_ID ) );
						}

						foreach ( $folders as $QPF_ID=>$folderData )
							if ( ( $QPF_ID != $curQPF_ID ) && qp_isChildOf( $hierarchy, $QPF_ID, $curQPF_ID ) )
								$qp_publicClass->setCookiesFolderCollapseValue( $DB_KEY, $QPF_ID, false );

						if ( ( isset( $action ) && $action != EXPAND && $action !=COLLAPSE ) || !isset( $action ) )
							$qp_publicClass->setCookiesFolderCollapseValue( $DB_KEY, $curQPF_ID, false );

						$collapsedFolders = $qp_publicClass->listCookiesCollapsedFolders( $DB_KEY );

						if ( !is_array( $collapsedFolders ) )
							$collapsedFolders = array();

						$prevFolder = null;

						foreach ( $folders as $QPF_ID=>$folderData )
						{
							$folderData->curID = $folderData->QPF_TEXTID;
							$folderData->PageID = $folderData->QPF_TEXTID;
							$folderData->BookID = $textBookID;

							$folderData->TREE_ACCESS_RIGHTS = UR_TREE_READ;

							$params = array();

							$params['DB_KEY'] = $DB_KEY;
							$params['BookID'] = $textBookID;
							$params['PageID'] = $folderData->QPF_TEXTID;

							if ( $folderData->TYPE != TREE_AVAILABLE_FOLDERS )
							{
								if ( $folderData->TREE_ACCESS_RIGHTS != TREE_NOACCESS )
								{
									$folderData->ROW_URL = prepareURLStr( PAGE_QP_PUBLISHED, $params );

									if ( !is_null( $prevFolder ) )
									{
										$prev = $folders[$prevFolder];

										$params['PageID'] = $prev->QPF_TEXTID;

										$folderData->prevURL = prepareURLStr( PAGE_QP_PUBLISHED, $params );

										$prev->nextURL = $folderData->ROW_URL;

										$folders[$prevFolder] = $prev;
									}

									$prevFolder = $QPF_ID;

								}
							}
							else
							{
								$folderData->ROW_URL = prepareURLStr( PAGE_QP_PUBLISHED, $params );
								$folderData->NAME = $bookData->NAME;
							}

							$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

							$collapseParams = array();

							$collapseParams['DB_KEY'] = $DB_KEY;
							$collapseParams['BookID'] = $textBookID;
							$collapseParams['cPageID'] = $folderData->QPF_TEXTID;

							if ( isset($collapsedFolders[$QPF_ID]) )
								$collapseParams['action'] = EXPAND;
							else
								$collapseParams['action'] = COLLAPSE;

							$folderData->COLLAPSE_URL = prepareURLStr( PAGE_QP_PUBLISHED, $collapseParams );

							$folders[$QPF_ID] = $folderData;

						}

						if ( is_null($curQPF_ID) )
						{
							$noAccessGranted = true;
							break;
						}

						// Load current folder data
						//
						if ( $curQPF_ID != "AVAILABLEFOLDERS" )
						{
							$folderData = $qp_pagesClass->getFolderInfo( $curQPF_ID, $kernelStrings );
							$folderData["QPF_CONTENT"] = preg_replace( '/(<[^>]*?="{0,1})([^">]*)pageid:([^">]*?[^>]*>)/u', '$1'.'$2'."book.php?DB_KEY=$DB_KEY&BookID=$textBookID&PageID=".'$3', $folderData["QPF_CONTENT"] );

							$fD = $folders[$curQPF_ID];

							$folderData["prevURL"] = isset( $fD->prevURL ) ? $fD->prevURL : "";
							$folderData["nextURL"] = isset( $fD->nextURL ) ? $fD->nextURL : "";
						}
						else
							$folderData = null;
					}
					else
					{
						$public = true;

						$spages = qp_searchPages( $public,$searchString, $currentBookID, $DB_KEY, $kernelStrings, $textBookID );

						$showPageSelector = false;
						$pages = null;
						$pageCount = 0;

						$pagesFound = sprintf( $qpStrings["app_pagesfound_title"], count( $spages ) );

						$spages = addPagesSupport( $spages, 30, $showPageSelector, $currentPage, $pages, $pageCount );
					}

					// Prepare menus
					//
					$folderMenu = array();
					$encodedID = base64_encode($curQPF_ID);

					// Other appearance settings
					//
					$params = array();
					$params['DB_KEY'] = $DB_KEY;
					$params['BookID'] = $textBookID;
					$params[ACTION] = HIDE_FOLDER;

					$closeFoldersLink = prepareURLStr( PAGE_QP_PUBLISHED, $params );

					$hideLeftPanel = $foldersHidden || !is_null( $searchString );
					$showFolderSelector = $foldersHidden && is_null( $searchString );

					if ( $statisticsMode )
					{
						$curQPF_ID = TREE_AVAILABLE_FOLDERS;
					}

					// Read initial folder tree panel width
					//
					if ( isset($_COOKIE['splitterView'.$QP_APP_ID.$textBookID]) )
						$treePanelWidth = (int)$_COOKIE['splitterView'.$QP_APP_ID.$textBookID];
					else
						$treePanelWidth = 200;

				}
				else
				{
					// Load folder list
					//
					$qp_publicClass->currentBookID = $currentBookID;

					$access = null;
					$hierarchy = null;
					$deletable = null;
					$addavailableFoldersP = false;

					$folders = $qp_publicClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
														$access , $hierarchy, $deletable, null,
														null, false, null, $addavailableFoldersP, null, false, false );

					if ( PEAR::isError($folders) )
					{
						$fatalError = true;
						$errorStr = $folders->getMessage();
						break;
					}

					// Prepare folder list to display
					//

					$contentList = array();

					foreach ( $folders as $QPF_ID=>$folderData )
					{
						$folderData = $qp_publicClass->getFolderInfo( $QPF_ID, $kernelStrings );
						$folderData["QPF_CONTENT"] = preg_replace( '/(<[^>]*?="{0,1})([^">]*)pageid:([^">]*?[^>]*>)/u', '$1'.'$2'."#".'$3', $folderData["QPF_CONTENT"] );
						$contentList[$QPF_ID] = $folderData;
					}
				}
	}

	$qp_publicClass->setFolderCollapseCookie( $DB_KEY );

	//
	// Page implementation
	//

	$styleSet = "office";
	$preproc = new php_preprocessor( "qppublic", $kernelStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, $bookData["QPB_NAME"] );

	$preproc->assign( FORM_LINK, PAGE_QP_PUBLISHED );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	$preproc->assign( "qpStrings", $qpStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "tree_access_mode_long_names", $tree_access_mode_long_names );
	$preproc->assign( "genericLinkUnsorted", prepareURLStr( PAGE_QP_PUBLISHED, array('searchString'=>$searchString) ) );

	$preproc->assign( "properties", $properties );
	$preproc->assign( "bookProperties", $bookProperties );

	if ( !$fatalError )
	{
		$preproc->assign( "treeViewName", $QP_APP_ID.$textBookID );

		if ( isset($searchString) )
			$preproc->assign( "searchString", $searchString );

		$preproc->assign( "noAccessGranted", $noAccessGranted );

		if ( !$noAccessGranted )
		{

			$preproc->assign( "DB_KEY", $DB_KEY );

			$theme["QPT_HEADER"] = stripSlashes( $theme["QPT_HEADER"] );
			$theme["QPT_HEADER"] = str_replace( "%BOOKNAME%", $bookData["QPB_NAME"], $theme["QPT_HEADER"] );

			$preproc->assign( "theme", $theme );
			if ( $theme["QPT_TYPE"] == 1 )
			{
				if ( isset( $folderData["QPF_NAME"] ) )
					$preproc->assign( "curFolderName", $folderData["QPF_NAME"] );

				if ( !isset($searchString) )
				{
					$preproc->assign( "folders", $folders );
					$preproc->assign( "hierarchy", $hierarchy );
					$preproc->assign( "collapsedFolders", $collapsedFolders );
					$preproc->assign( "currentFolder", $curQPF_ID );
					$preproc->assign( "curFolderData", $folderData );
					$preproc->assign( "folderData", prepareArrayToStore( (array) $folderData ) );
					$preproc->assign( "currentPageID", $folderData["QPF_TEXTID"] );
				}
				else
				{
					$preproc->assign( PAGES_SHOW, $showPageSelector );
					$preproc->assign( PAGES_PAGELIST, $pages );
					$preproc->assign( PAGES_CURRENT, $currentPage );
					$preproc->assign( PAGES_NUM, $pageCount );
					$preproc->assign( PAGES_CURRENT, $currentPage );
					$preproc->assign( "searchResult", $spages );
					$preproc->assign( "searchCount", count( $spages ) );
					$preproc->assign( "pagesFound", $pagesFound );
				}


				$preproc->assign( "currentBookID", $textBookID );

				$preproc->assign( "bookData", (array) $bookData );

				$preproc->assign( "folderMenu", $folderMenu );

				$preproc->assign( "treePanelWidth", $treePanelWidth );

				$preproc->assign( "closeFoldersLink", $closeFoldersLink );
				$preproc->assign( "hideLeftPanel", $hideLeftPanel );
				$preproc->assign( "showFolderSelector", $showFolderSelector );

				$preproc->assign( "statisticsMode", $statisticsMode );
				$preproc->assign( "tree_access_mode_names", $tree_access_mode_names );
				$preproc->assign( "tree_access_mode_long_names", $tree_access_mode_long_names );

				if ( isset($userShowAllLink) ) {
					$preproc->assign( "userLimitStr", $userLimitStr );
					$preproc->assign( "userShowAllLink", $userShowAllLink );
				}

				if ( isset($groupShowAllLink) ) {
					$preproc->assign( "groupLimitStr", $groupLimitStr );
					$preproc->assign( "groupShowAllLink", $groupShowAllLink );
				}
			}
			else
			{
				$preproc->assign( "folders", $folders );
				$preproc->assign( "hierarchy", $hierarchy );

				$preproc->assign( "contentList", $contentList );

				$preproc->assign( "DB_KEY", $DB_KEY );
				$preproc->assign( "currentBookID", base64_encode( $currentBookID ) );

				$preproc->assign( "properties", $properties );
				$preproc->assign( "bookData", (array) $bookData );
			}

		}
	}

	if ( isset( $previewTheme ) && intval( $previewTheme ) != 0 )
		$preproc->assign( "previewTheme", intval( $previewTheme ) );

	if ( $theme["QPT_TYPE"] == 1 )
		$preproc->display( "quickpages_public.htm" );
	else
	{
		if ( isset( $pdfout ) && !$fatalError )
		{
			$preproc->display( "quickpages_plain_public_pdf.htm" );

			$htmlbuffer = ob_get_contents();

			ob_end_clean();

			$pdf = new HTML2FPDF();
			$pdf->DisplayPreferences('HideWindowUI');
//		$pdf->AddFont('Arial','','arialcyr.php');
//		$pdf->SetFont('Arial','',12);
//		$pdf->AddFont('Arial','','tahoma.php');
//		$pdf->SetFont('Arial','',12);
			$pdf->AddPage();
			$pdf->WriteHTML( $htmlbuffer );

			$pdf->Output( 'plain.pdf', 'I' );
		}
		else
			$preproc->display( "quickpages_plain_public.htm" );

	}

	$DB_KEY = $oldDB_KEY;

?>