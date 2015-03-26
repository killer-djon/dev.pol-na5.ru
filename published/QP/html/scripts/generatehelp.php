<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QP/qp.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QP";

	pageUserAuthorization( $SCR_ID, $QP_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$qpStrings = $qp_loc_str[$language];
	$invalidField = null;
	
	$currentBookID = base64_decode( $currentBookID );
	
	if ($DB_KEY !== "WEBASYST")
		die ("Closed Section");

	do {
		$rights = $qp_treeClass->getIdentityFolderRights( $currentUser, $currentBookID, $kernelStrings );

		if ( PEAR::isError($rights) )
		{
			$fatalError = true;
			$errorStr = $rights->getMessage();
			break;
		}

		if ( is_null( $currentBookID ) )
		{
			$fatalError = true;
			$errorStr = $qpStrings['app_page_norights_message'];
			break;
		}

		$bookData = $qp_treeClass->getFolderInfo( $currentBookID, $kernelStrings );

		if ( PEAR::isError($bookData) )
		{
			die( $errorStr = $res->getMessage() );
			break;
		}

		$qp_pagesClass->currentBookID = $currentBookID;
		
		preg_match ("/manual-([A-Za-z]{2})-([A-Za-z]{3})/", $bookData["QPB_TEXTID"], $matches);
		if (!$matches) {
			$errorStr = "Book ID is wrong: " . $bookData["QPB_TEXTID"];
			$fatalError = true;
			break;
		}
		$bookAppId = strtoupper($matches[1]);
		$bookAppLanguage = $matches[2];
		
	} while (false);
	
	$btnIndex = getButtonIndex( array(), $_POST );
	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL), $_POST );
	
	
	
	$saved = false;
	switch ($btnIndex) {
		case 0:
			$params = array ("DB_KEY" => base64_encode($DB_KEY));
			$params['BookID'] = $bookData["QPB_TEXTID"];
			$publishedContentUrl = "http://" . getenv("HTTP_HOST") . str_replace("generatehelp.php", "", getenv("SCRIPT_NAME")) . PAGE_QP_PUBLISHED . "?" . "DB_KEY=" . base64_encode($DB_KEY) . "&BookID=" . $bookData["QPB_TEXTID"];
			$content = file_get_contents ($publishedContentUrl);
			
			if (!$content)
			{
				$errorStr = "Empty content from published url: $publishedContentUrl";				
				break;
			}
			
			$helpFilesPath = WBS_DIR . "published/" . $bookAppId . "/help/" . $bookAppLanguage . "/";
			if (!file_exists($helpFilesPath)) {
				$errorStr = "cannot find help files path: " . $helpFilesPath;
				break;
			}
			
			preg_match_all ("<img src=\"([^\"]*)\">", $content, $imgMatches);
			
			if ($imgMatches[1]) {
				$imagesFilesPath = $helpFilesPath . "images/";
				if (!file_exists($imagesFilesPath))
				{
					$errorStr = "cannot find help images files path: " . $imagesFilesPath;
					break;
				}
				$images = $imgMatches[1];
				foreach ($images as $cSrc) {
					$scriptPath = getenv("SCRIPT_NAME");
					$scriptPathParts = split("/", $scriptPath);
					for ($i = 0; $i < 4; $i++) {
						array_pop($scriptPathParts);
					}
					$scriptPath = join ("/", $scriptPathParts);
					
					$realSrc = str_replace("../../..", "http://" . getenv("HTTP_HOST") . $scriptPath, $cSrc);
					$cFilename = basename ($cSrc);
					
					$cFilepath = $imagesFilesPath . $cFilename;
					if (!copy ($realSrc, $cFilepath)) {
						$errorStr = "cannot copy image: $realSrc to $cFilepath";
					}
					$content = str_replace($cSrc, "images/" . $cFilename, $content);
				}
			}
			
		
			if (!empty($clearstyles)) {
			
				$contents = explode("<body ", $content);
				$contents[0] = preg_replace("/<style>(.*)<\/style>/usim", '<link href=" ../../../common/html/classic/help_generated.css" rel="stylesheet" type="text/css">', $contents[0]);  
				
				foreach ($contents as $i => &$c) {				
					if ($i) {				
						$c = preg_replace("/style=\"[^\"]*\"/sim", "", $c);
						$c = preg_replace("/<p><\/p>|<p>&nbsp;<\/p>|<br>/", "", $c);
					}
				}
				$content = implode("<body ", $contents);
			}
			
			$help_filename = $helpFilesPath . strtolower($bookAppId) . ".html";
			$f = fopen($help_filename, "w+");
			if (!$f) {
				$errorStr = "cannot open file $help_filename";
				break;

			} else {
				fwrite($f, $content);
				fclose($f);
			}
/*			if (!file_put_contents($help_filename, $content)) {
				$errorStr = "cannot save content to $help_filename";
				break;
			} */
			$saved = true;
			redirectBrowser( PAGE_QP_QUICKPAGES, array() );
			
			break;
		case 1 :
		case 2 :
			redirectBrowser( PAGE_QP_QUICKPAGES, array() );
	}


	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, "Webasyst Help Files Generator" );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qpStrings", $qpStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );

	$preproc->assign( 'currentBookID', base64_encode( $currentBookID ) );

	if ( !$fatalError )
	{
		$preproc->assign( "bookData", $bookData );
		$preproc->assign ("bookAppId", $bookAppId);
		$preproc->assign ("bookAppLanguage", $bookAppLanguage);
		$preproc->assign( "bookHelpSaved", $saved );
	}

	$preproc->display( "generatehelp.htm" );
?>