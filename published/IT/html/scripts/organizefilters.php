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
	$invalidField = null;

	define( "ACTION_APLYFILTER", "apply" );
	define( "ACTION_REVOKEFILTER", "revoke" );

	//
	// Form handling
	//
	$btnIndex = getButtonIndex( array(BTN_CANCEL, BTN_SAVE, "deletefilterbtn", "newfilterbtn"), $_POST );

	if ( isset($action) )
		switch ($action) {
			case ACTION_APLYFILTER : {
				it_applyFilter( $currentUser, $ISSF_ID, $kernelStrings, $itStrings );

				if ( READ_ONLY )
					saveCookie( IT_ISSF_ID, $ISSF_ID, COOKIE_TO_LONG );

				redirectBrowser( PAGE_IT_ISSUELIST, array() );

				break;
			}
			case ACTION_REVOKEFILTER : {
				it_revokeIssueFilter( $currentUser, $kernelStrings );

				if ( READ_ONLY )
					saveCookie( IT_ISSF_ID, null, COOKIE_TO_LONG );

				redirectBrowser( PAGE_IT_ISSUELIST, array() );
			}
		}

	switch ( $btnIndex ) {
		case 0 : {
					redirectBrowser( PAGE_IT_ISSUELIST, array( "P_ID"=>$P_ID ) );
				}
		case 2 : {
					if ( !is_array($document) )
						break;

					for ( $i = 0; $i < count($document); $i++ ) {
						$filterID = $document[$i];

						$res = it_deleteIssueFilter( $currentUser, $filterID, $kernelStrings );
						if ( PEAR::isError( $res ) ) {
							$errorStr = $res->getMessage();

							break 2;
						}
					}

					break;
				}
		case 3 : {
					redirectBrowser( PAGE_IT_ISSUEFILTERS, array("P_ID"=>$P_ID, ACTION=>ACTION_NEW, OPENER=>PAGE_IT_ORGANIZEFILTERS ) );									
				}
	}

	switch ( true ) {
		case true : {
						if ( $fatalError )
							break;

						// Load recently applied filters
						// 
						$recentFilters = it_listIssueFilters( $currentUser, $kernelStrings );
						if ( PEAR::isError($recentFilters) ) {
							$errorStr = $recentFilters->getMessage();
							$fatalError = true;

							break;
						}

						$index = 0;
						if ( is_array($recentFilters) )
							foreach ( $recentFilters as $filterID=>$filterName ) {
								$editURL = prepareURLStr( PAGE_IT_ISSUEFILTERS, array( ACTION=>ACTION_EDIT, "ISSF_ID"=>$filterID, 
															"P_ID"=>$P_ID, OPENER=>PAGE_IT_ORGANIZEFILTERS ) );

								$applyURL = prepareURLStr( PAGE_IT_ORGANIZEFILTERS, array( ACTION=>ACTION_APLYFILTER, "ISSF_ID"=>$filterID ) );

								$recentFilters[$filterID] = array( prepareStrToDisplay($filterName, true), $editURL, $index, $applyURL );
								$index++;
							}

						$revokeURL = prepareURLStr( PAGE_IT_ORGANIZEFILTERS, array( ACTION=>ACTION_REVOKEFILTER ) );

		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['ogf_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_IT_ORGANIZEFILTERS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	$preproc->assign( HELP_TOPIC, "organizefilters.htm");

	$preproc->assign( "P_ID", $P_ID ); 

	if ( !$fatalError ) {
		$preproc->assign( "recentFilters", $recentFilters );
		$preproc->assign( "recentFiltersCount", count($recentFilters) );
		$preproc->assign( "revokeURL", $revokeURL );
	}

	$preproc->display( "organizefilters.htm" );
?>