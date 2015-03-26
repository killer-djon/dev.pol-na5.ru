<?php

	//
	// WebAsyst Newsletter Manager Application SOAP service
	//
	require_once("../../common/soap/includes/soapinit.php");
	require_once( "../qp.php" );
	require_once("../../common/soap/includes/soapclient_funcs.php");

	require_once ("SOAP/Value.php");
	require_once ("SOAP/Fault.php");

	class SOAP_QP_Server {
		var $__typedef     = array();
		var $__dispatch_map = array();

		var $bookData;
		var $pageData;
		var $bookProperties;

		function SOAP_QP_Server() {

			$this->__dispatch_map['qp_booklist'] =
			//
			// Prepare and return two serialized arrays wich consists of
			// pages titles and book hierarchy of pages
			//
			//	Parameters:
			//
			// 	Returns
			//
				array(
					'in' => array(
						'U_ID' => 'string',
						'PASSWORD' => 'string'
					),
					'out' => array(
						'booklist' => 'string',
						'error' => 'int',
						'errorCode' => 'int'
					)
			);

			$this->__dispatch_map['qp_publictree'] =
			//
			// Prepare and return two serialized arrays wich consists of
			// pages titles and book hierarchy of pages
			//
			//	Parameters:
			//
			// 	Returns
			//
				array(
					'in' => array(
						'book_id' => 'string',
						'U_ID' => 'string',
						'PASSWORD' => 'string'
					),
					'out' => array(
						'pagelist' => 'string',
						'hierarchy' => 'string',
						'error' => 'int',
						'errorCode' => 'int'
					)
			);

			$this->__dispatch_map['qp_publicpage'] =
			//
			// Prepare and return two serialized arrays wich consists of
			// pages titles and book hierarchy of pages
			//
			//	Parameters:
			//
			// 	Returns
			//
				array(
					'in' => array(
						'book_id' => 'string',
						'page_id' => 'string',
						'U_ID' => 'string',
						'PASSWORD' => 'string'
					),
					'out' => array(
						'pagedata' => 'string',
						'error' => 'int',
						'errorCode' => 'int'
					)
			);

			$this->__dispatch_map['qp_publicpageimages'] =
			//
			// Prepare and return two serialized arrays wich consists of
			// page's images
			//
			//	Parameters:
			//
			// 	Returns
			//
				array(
					'in' => array(
						'book_id' => 'string',
						'page_id' => 'string',
						'U_ID' => 'string',
						'PASSWORD' => 'string'
					),
					'out' => array(
						'pagedata' => 'string',
						'error' => 'int',
						'errorCode' => 'int'
					)
			);

			$this->__dispatch_map['qp_publicbooklastmodified'] =
			//
			// Prepare and return two serialized arrays wich consists of
			// pages titles and book hierarchy of pages
			//
			//	Parameters:
			//
			// 	Returns
			//
				array(
					'in' => array(
						'book_id' => 'string',
						'U_ID' => 'string',
						'PASSWORD' => 'string'
					),
					'out' => array(
						'date' => 'string',
						'error' => 'int',
						'errorCode' => 'int'
					)
			);

			$this->__dispatch_map['qp_publicpagelastmodified'] =
			//
			// Prepare and return two serialized arrays wich consists of
			// pages titles and book hierarchy of pages
			//
			//	Parameters:
			//
			// 	Returns
			//
				array(
					'in' => array(
						'book_id' => 'string',
						'page_id' => 'string',
						'U_ID' => 'string',
						'PASSWORD' => 'string'
					),
					'out' => array(
						'date' => 'string',
						'error' => 'int',
						'errorCode' => 'int'
					)
			);

			$this->__dispatch_map['qp_searchpages'] =
			//
			//
			//
			//
			//	Parameters:
			//
			// 	Returns
			//
				array(
					'in' => array(
						'book_id' => 'string',
						'search_string' => 'string',
						'U_ID' => 'string',
						'PASSWORD' => 'string'
					),
					'out' => array(
						'pagelist' => 'string',
						'error' => 'int',
						'errorCode' => 'int'
					)
			);

		}

		function qp_booklist( $U_ID, $PASSWORD )
		{
			global $loc_str;
			global $qp_loc_str;
			global $qp_publicClass;
			global $qp_ptreeClass;

			//
			// Authorize service access
			//
			$res = authorizeServiceAccess( "QP", base64_decode($U_ID), base64_decode($PASSWORD), "public", $loc_str[LANG_ENG] );

			if (PEAR::isError($res))
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', SOAPROBOT_ERR_AUTHORIZATION),
					new SOAP_Value('booklist', 'string', null )
				);

			$books = $qp_ptreeClass->listFolders( "", TREE_ROOT_FOLDER, $kernelStrings, 0, false,
												$access = null, $hierarchy = null, $deletable = null );

			if ( PEAR::isError( $books ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', SOAPROBOT_ERR_QUERYEXECUTING ),
					new SOAP_Value('booklist', 'string', null )
				);


			return array(
					new SOAP_Value('error', 'int', 0 ),
					new SOAP_Value('errorCode', 'int', 0 ),
					new SOAP_Value('booklist', 'string', base64_encode( serialize( $books ) ) )
				);
		}

		function qp_publictree( $book_id, $U_ID, $PASSWORD )
		{
			global $loc_str;
			global $qp_loc_str;
			global $qp_publicClass;

			//
			// Authorize service access
			//
			$res = authorizeServiceAccess( "QP", base64_decode($U_ID), base64_decode($PASSWORD), "public", $loc_str[LANG_ENG] );
			if (PEAR::isError($res))
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', SOAPROBOT_ERR_AUTHORIZATION),
					new SOAP_Value('pagelist', 'string', null ),
					new SOAP_Value('hierarchy', 'string', null )
				);

			//
			// Dermine current internal QPB_ID
			//
			$QPB_ID = qp_getID( "book", base64_decode( $book_id ) );
			if ( PEAR::isError( $QPB_ID ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', 6000 ),
					new SOAP_Value('pagelist', 'string', null ),
					new SOAP_Value('hierarchy', 'string', null )
				);

			$qp_publicClass->currentBookID = $QPB_ID;
                        $access = null;
                        $hierarchy = null;
                        $deletable = null;
                                                                         
			$folders = $qp_publicClass->listFolders( "", TREE_ROOT_FOLDER, $kernelStrings, 0, false,
													$access, $hierarchy, $deletable, null,
													null, false, null, $addavailableFoldersP = false, null, false, false );
			if ( PEAR::isError( $folders ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', SOAPROBOT_ERR_QUERYEXECUTING ),
					new SOAP_Value('pagelist', 'string', null ),
					new SOAP_Value('hierarchy', 'string', null )
				);


			return array(
					new SOAP_Value('error', 'int', 0 ),
					new SOAP_Value('errorCode', 'int', 0 ),
					new SOAP_Value('pagelist', 'string', base64_encode( serialize( $folders ) ) ),
					new SOAP_Value('hierarchy', 'string', base64_encode( serialize( $hierarchy ) ) )
				);
		}

		function qp_publicpage( $book_id, $page_id, $U_ID, $PASSWORD )
		{
			global $loc_str;
			global $qp_loc_str;
			global $qp_publicClass;

			//
			// Authorize service access
			//
			$res = authorizeServiceAccess( "QP", base64_decode($U_ID), base64_decode($PASSWORD), "public", $loc_str[LANG_ENG] );

			if (PEAR::isError($res))
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', SOAPROBOT_ERR_AUTHORIZATION),
					new SOAP_Value('pagedata', 'string', null )
				);

			//
			// Dermine current internal QPB_ID
			//
			$QPB_ID = qp_getID( "book", base64_decode( $book_id )  );
			if ( PEAR::isError( $QPB_ID ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', 6000 ),
					new SOAP_Value('pagedata', 'string', null )
				);

			//
			// Dermine current internal QPF_ID
			//
			$QPF_ID = qp_getID( "page", base64_decode( $page_id ), $QPB_ID );
			if ( PEAR::isError( $QPF_ID ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', 6001 ),
					new SOAP_Value('pagedata', 'string', null )
				);

			$qp_publicClass->currentBookID = $QPB_ID;
			$pageData = $qp_publicClass->getFolderInfo( $QPF_ID, $kernelStrings );
			if ( PEAR::isError( $pageData ) ) 
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', SOAPROBOT_ERR_QUERYEXECUTING ),
					new SOAP_Value('pagedata', 'string', null )
				);
			

				return array(
					new SOAP_Value('error', 'int', 0 ),
					new SOAP_Value('errorCode', 'int', 0 ),
					new SOAP_Value('pagedata', 'string', base64_encode( serialize( $pageData ) ) )
				);
		}

		function qp_publicpageimages( $book_id, $page_id, $U_ID, $PASSWORD )
		{
			global $loc_str;
			global $qp_loc_str;
			global $qp_publicClass;

			//
			// Authorize service access
			//
			$res = authorizeServiceAccess( "QP", base64_decode($U_ID), base64_decode($PASSWORD), "public", $loc_str[LANG_ENG] );

			if (PEAR::isError($res))
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', SOAPROBOT_ERR_AUTHORIZATION),
					new SOAP_Value('pagedata', 'string', null )
				);

			//
			// Dermine current internal QPB_ID
			//
			$QPB_ID = qp_getID( "book", base64_decode( $book_id )  );
			if ( PEAR::isError( $QPB_ID ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', 6000 ),
					new SOAP_Value('pagedata', 'string', null )
				);

			//
			// Dermine current internal QPF_ID
			//
			$QPF_ID = qp_getID( "page", base64_decode( $page_id ), $QPB_ID );
			if ( PEAR::isError( $QPF_ID ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', 6001 ),
					new SOAP_Value('pagedata', 'string', null )
				);

			$qp_publicClass->currentBookID = $QPB_ID;
			$pageData = $qp_publicClass->getFolderInfo( $QPF_ID, $kernelStrings );

			if ( PEAR::isError( $pageData ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', SOAPROBOT_ERR_QUERYEXECUTING ),
					new SOAP_Value('pagedata', 'string', null )
				);

			$pageData =  (array) $pageData;

			$RECORD_FILES = $pageData["QPF_ATTACHMENT"];

			$files = listAttachedFiles( base64_decode($RECORD_FILES) );

			$sourcePath = qp_getNoteAttachmentsDir( $pageData["QPF_UNIQID"] );

			$filelist = array();
			$count = 0;

			if ( !is_null( $files ) )
				foreach ( $files as $key => $value )
				{
					$fileName = $sourcePath.'/'.$value["diskfilename"];

					if ( !file_exists( $fileName ) )
						continue;

					$filelist[$value["diskfilename"]] = base64_encode( @file_get_contents( $fileName ) );
					++$count;
				}

			$pData["COUNT"] = $count;
			$pData["FILELIST"] = $filelist;

			return array(
				new SOAP_Value('error', 'int', 0 ),
				new SOAP_Value('errorCode', 'int', 0 ),
				new SOAP_Value('pagedata', 'string', base64_encode( serialize( $pData ) ) )
			);
		}

		function qp_publicbooklastmodified( $book_id, $U_ID, $PASSWORD )
		{
			global $loc_str;
			global $qp_loc_str;
			global $qp_publicClass;
			//
			// Authorize service access
			//
			$res = authorizeServiceAccess( "QP", base64_decode($U_ID), base64_decode($PASSWORD), "public", $loc_str[LANG_ENG] );

			if (PEAR::isError($res))
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', SOAPROBOT_ERR_AUTHORIZATION),
					new SOAP_Value('date', 'string', null )
				);

			//
			// Dermine current internal QPB_ID
			//
			$QPB_ID = qp_getID( "book", base64_decode( $book_id ) );

			if ( PEAR::isError( $QPB_ID ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', 6000 ),
					new SOAP_Value('date', 'string', null )
				);

			$res = qp_getLastModified( $QPB_ID );

			if ( PEAR::isError( $res ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', SOAPROBOT_ERR_QUERYEXECUTING ),
					new SOAP_Value('date', 'string', null )
				);

			return array(
				new SOAP_Value('error', 'int', 0 ),
				new SOAP_Value('errorCode', 'int', 0 ),
				new SOAP_Value('date', 'string', $res )
			);
		}

		function qp_publicpagelastmodified( $book_id, $page_id, $U_ID, $PASSWORD )
		{
			global $loc_str;
			global $qp_loc_str;
			global $qp_publicClass;

			//
			// Authorize service access
			//
			$res = authorizeServiceAccess( "QP", base64_decode($U_ID), base64_decode($PASSWORD), "public", $loc_str[LANG_ENG] );

			if (PEAR::isError($res))
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', SOAPROBOT_ERR_AUTHORIZATION),
					new SOAP_Value('date', 'string', null )
				);

			//
			// Dermine current internal QPB_ID
			//
			$QPB_ID = qp_getID( "book", base64_decode( $book_id ) );
			if ( PEAR::isError( $QPB_ID ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', 6000 ),
					new SOAP_Value('date', 'string', null )
				);

			$QPF_ID = qp_getID( "page", base64_decode( $page_id ), $QPB_ID );
			if ( PEAR::isError( $QPF_ID ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', 6001 ),
					new SOAP_Value('date', 'string', null )
				);

			$res = qp_getLastModified( $QPB_ID, $QPF_ID );

			if ( PEAR::isError( $res ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', SOAPROBOT_ERR_QUERYEXECUTING ),
					new SOAP_Value('date', 'string', null )
				);

			return array(
				new SOAP_Value('error', 'int', 0 ),
				new SOAP_Value('errorCode', 'int', 0 ),
				new SOAP_Value('date', 'string', $res )
			);
		}


		function qp_searchpages( $book_id, $search_string, $U_ID, $PASSWORD )
		{
			global $loc_str;
			global $qp_loc_str;
			global $qp_publicClass;
			global $DB_KEY;

			//
			// Authorize service access
			//
			$res = authorizeServiceAccess( "QP", base64_decode($U_ID), base64_decode($PASSWORD), "public", $loc_str[LANG_ENG] );

			if (PEAR::isError($res))
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', SOAPROBOT_ERR_AUTHORIZATION),
					new SOAP_Value('pagelist', 'string', null )
				);

			//
			// Dermine current internal QPB_ID
			//
			$QPB_ID = qp_getID( "book", base64_decode( $book_id ) );
			if ( PEAR::isError( $QPB_ID ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', 6000 ),
					new SOAP_Value('pagelist', 'string', null )
				);

                        $res = qp_searchPages( true, base64_decode( $search_string ), $QPB_ID, $DB_KEY, $loc_str[LANG_ENG], $textBookID=null, $entryProcessor = null, $charsAround = 50, $numEntrance = 2 );
			if ( PEAR::isError( $res ) )
				return array(
					new SOAP_Value('error', 'int', 1 ),
					new SOAP_Value('errorCode', 'int', SOAPROBOT_ERR_QUERYEXECUTING ),
					new SOAP_Value('pagelist', 'string', null )
				);

			foreach( $res as $key=>$value )
			{
				$value->QPF_CONTENT = "";
				$res[$key] = $value;
			}

			return array(
				new SOAP_Value('error', 'int', 0 ),
				new SOAP_Value('errorCode', 'int', 0 ),
				new SOAP_Value('pagelist', 'string', base64_encode( serialize( $res ) ) )
			);
		}

	}

	require_once 'SOAP/Server.php';

	$server = new SOAP_Server;

	$soapclass = new SOAP_QP_Server();
	$server->_auto_translation = true;
	$server->addObjectMap($soapclass, 'urn:SOAP_QP_Server');

	$_SERVER['CONTENT_TYPE']='text/xml; charset=iso-8859-1';

	if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST') {
		$server->service($HTTP_RAW_POST_DATA);
	} else {
		require_once 'SOAP/Disco.php';
		$disco = new SOAP_DISCO_Server($server,'QPServer');
		header("Content-type: text/xml");

		echo $disco->getWSDL();
		exit;
	}
?>