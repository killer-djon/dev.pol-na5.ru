<?php
	//
	// WebAsyst Quick Pages common classes
	//

	// Book List class used in back-end.
	require_once realpath(WBS_DIR."kernel/includes/modules/pclzip.lib.php");

	class qp_documentFolderTree extends genericDocumentFolderTree
	{
		function qp_documentFolderTree( &$descriptor )
		{
			$this->folderDescriptor = $descriptor->folderDescriptor;
			$this->documentDescriptor = $descriptor->documentDescriptor;

			$this->globalPrefix = "QP";
		}
	}

	$qp_treeClass = new qp_documentFolderTree(  $qp_BookTreeFoldersDescriptor );

	// Pages List class used in back-end.

	class qp_pagesFolderTree extends genericDocumentFolderTree
	{
		var $currentBookID;

		function qp_pagesFolderTree()
		{
			$this->folderDescriptor = new treeFolderTableDescriptor( 'QPFOLDER', 'QPF_ID', 'QPF_TITLE', 'QPF_ID_PARENT', 'QPF_STATUS' );
			$this->documentDescriptor = null;

			$this->checkRights = false;
			$this->globalPrefix = "QPF";
		}

		function copyFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null, $callbackParams = null, $onFininshCopy = null, $accessInheritance = ACCESSINHERITANCE_COPY, $onBeforeFolderCreate = null, $checkFolderName = true, $copyChilds = true )
		{
			global $_qpQuotaManager;

			$_qpQuotaManager = new DiskQuotaManager();

			$TotalUsedSpace = $_qpQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::copyFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation, $onFolderCreate, $callbackParams, $onFininshCopy, $accessInheritance, $onBeforeFolderCreate, $checkFolderName, $copyChilds );

			$_qpQuotaManager->Flush( $kernelStrings );

			return $res;
		}


		function getListParentFoldersSQL( &$params )
		{
			global $qr_qp_tree_selectFolders;

			$sql = $this->applySQLObjectNames( $qr_qp_tree_selectFolders );

			$params['QPB_ID'] = $this->currentBookID;

			return $sql;
		}

		function createArchive( $U_ID, $bookData, &$kernelStrings, &$qpStrings, &$fileNumber )
		//
		// Creates archive in the temporary folder
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$objects - list of files or folder identifier for files and folder modes
		//			$kernelStrings - Kernel localization strings
		//			$qpStrings - Document Depot localization strings
		//
		//		Returns path to archive as string or PEAR_Error
		//
		{
			global $qp_addTmpName__;

			@set_time_limit( 3600 );

			// Check if archives are supported
			//
			if ( !qp_archiveSupported() )
				return PEAR::raiseError( $qpStrings['app_nozlib_message'], ERRCODE_APPLICATION_ERR );

			// Create archive in the temporary directory
			//
			$tmpFileName = uniqid( TMP_FILES_PREFIX );
			$archivePath = WBS_TEMP_DIR."/".$tmpFileName;

			$fileNumber = 0;

			// Create archive
			//
			$archive = new PclZip($archivePath);
			$res = $archive->create(array());

			// Add files to the archive
			//
			$nameList = array();

			// Add files or folders to the archive
			//
			$access = null;
			$hierarchy = null;
			$deletable = null;
			$minimalRights = null;

			$folders = $this->listFolders( $U_ID, TREE_ROOT_FOLDER, $kernelStrings, 0, false, $access, $hierarchy, $deletable, $minimalRights );
			if ( PEAR::isError($folders) )
				return $folders;

			$backupData = array();

			$backupData["BOOK_DATA"] = $bookData;
			$backupData["HIERARCHY"] = $hierarchy;

			foreach( $folders as $QPF_ID=>$folder )
			{
				$folderData = $this->getFolderInfo( $QPF_ID, $kernelStrings );
				if ( PEAR::isError($folderData) )
					return $folderData;

				$backupData["FOLDERS"][$QPF_ID] = $folderData;

				$filePath = qp_getNoteAttachmentsDir( $folderData["QPF_UNIQID"] );
				if ( is_dir( $filePath ) )
				{
					if ( $dh = opendir( $filePath ) )
					{
						while ( ( $file = readdir( $dh ) ) !== false)
						{
							if ( $file == ".." || $file == "." )
								continue;

							$backupData["FILES"][$folderData["QPF_UNIQID"]][] = $file;

							$fileNumber++;
							$qp_addTmpName__ = $folderData["QPF_UNIQID"]."/".$file;
							$archive->add( $filePath."/".$file, PCLZIP_CB_PRE_ADD, "qp_preAddCallBack" );
						}
						closedir($dh);
					}
				}
			}

			$bfilename = md5( uniqid(rand(), true ) );
			$bf = WBS_TEMP_DIR."/".$bfilename;

			if ( !( $file = fopen( $bf, "w" ) ) )
				return null;

			if ( fwrite($file, base64_encode( serialize( $backupData ) ) ) === FALSE )
				return null;

			fclose( $file );

			$fileNumber++;

			$qp_addTmpName__ = "qp_backup_data";
			$archive->add( $bf, PCLZIP_CB_PRE_ADD, "qp_preAddCallBack" );

			return $tmpFileName;
		}
	}

	$qp_pagesClass = new qp_pagesFolderTree;

	// Book List class used in public front-end.

	class qp_public_documentFolderTree extends genericDocumentFolderTree
	{
		function qp_public_documentFolderTree( &$descriptor )
		{
			$this->folderDescriptor = $descriptor->folderDescriptor;
			$this->documentDescriptor = $descriptor->documentDescriptor;

			$this->globalPrefix = "QPUB";
		}

		function getListParentFoldersSQL( &$params )
		{
			global $qr_qp_ptree_selectFolders;

			$sql = $this->applySQLObjectNames( $qr_qp_ptree_selectFolders );

			return $sql;
		}
	}

	$qp_ptreeClass = new qp_public_documentFolderTree( $qp_BookTreeFoldersDescriptor );

	// Pages List class used in public front-end.

	class qp_publicFolderTree extends genericDocumentFolderTree
	{
		var $currentBookID;

		function qp_publicFolderTree()
		{
			$this->folderDescriptor = new treeFolderTableDescriptor( 'QPFOLDER', 'QPF_ID', 'QPF_TITLE', 'QPF_ID_PARENT', 'QPF_STATUS' );
			$this->documentDescriptor = null;

			$this->checkRights = false;
			$this->globalPrefix = "QPUBP";
		}

		function getListParentFoldersSQL( &$params )
		{
			global $qr_qp_public_selectFolders;

			$sql = $this->applySQLObjectNames( $qr_qp_public_selectFolders );

			$params['QPB_ID'] = $this->currentBookID;

			return $sql;
		}

		function getSelectFolderSQL( &$params )
		{
			global $qr_qp_public_selectFolder;

			$sql = $this->applySQLObjectNames( $qr_qp_public_selectFolder );

			$params['QPB_ID'] = $this->currentBookID;

			return $sql;
		}

		function listCookiesCollapsedFolders( $DB_KEY )
		{
			$varName = "QP".base64_decode( $DB_KEY )."COLLAPSEDFOLDERS";

			if ( !isset( $_COOKIE[ $varName ] ) )
				return false;

			$folders = base64_decode( $_COOKIE[ $varName ] );

			if ( strlen($folders) )
				$folders = explode( ';', $folders );
			else
				$folders = array();

			$result = array();
			foreach( $folders as $key=>$keyDF_ID )
				if ( $keyDF_ID != "" )
					$result[$keyDF_ID] = 1;

			return $result;
		}

		function setFolderCollapseCookie( $DB_KEY )
		{
			$varName = "QP".base64_decode( $DB_KEY )."COLLAPSEDFOLDERS";
			if ( isset($_COOKIE[ $varName ]) )
				$value = $_COOKIE[ $varName ];
			else
				$value = false;

			setcookie( $varName, $value, time()+60*60*24*30 );
		}

		function setCookiesFolderCollapseValue( $DB_KEY, $ID, $value )
		//
		// Saves folder collapse value
		//
		//		Parameters:
		//			$ID - folder identifier
		//			$value - collapse value
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null
		//
		{
			$varName = "QP".base64_decode( $DB_KEY )."COLLAPSEDFOLDERS";
			if ( isset($_COOKIE[ $varName ]) )
				$folders = base64_decode( $_COOKIE[ $varName ] );
			else
				$folders = "";

			if ( strlen($folders) )
				$folders = explode( ';', $folders );
			else
				$folders = array();

			$keys = array();
			foreach( $folders as $key=>$key_ID )
				$keys[$key_ID] = 1;

			if ( !$value ) {
				if ( isset($keys[$ID]) )
					unset($keys[$ID]);
			} else
				$keys[$ID] = 1;

			$folders = implode( ";", array_keys($keys) );

			if ( $folders == "" )
				$folders = ";";

			$_COOKIE[ $varName ] = base64_encode( $folders );
		}

	}

	$qp_publicClass = new qp_publicFolderTree;

	// Represents Books service settings
	//
	class qp_bookArray extends arrayAdaptedClass
	{
		var $QPB_ID = null;
		var $QPB_TEXTID = null;
		var $QPB_NAME = null;
		var $QPB_TITLE = null;
		var $QPB_KEYWORDS = null;
		var $QPB_DESCRIPTION = null;
		var $QPB_PUBLISHED = null;
		var $QPB_THEME = null;
		var $QPB_STATUS = null;

		function qp_bookArray()
		{
			$this->dataDescrition = new dataDescription();

			$this->dataDescrition->addFieldDescription( 'QPB_NAME', t_string, true, 250 );
			$this->dataDescrition->addFieldDescription( 'QPB_TEXTID', t_string, false, 50 );
			$this->dataDescrition->addFieldDescription( 'QPB_TITLE', t_string, false, 255 );
			$this->dataDescrition->addFieldDescription( 'QPB_KEYWORDS', t_string, false, 255 );
			$this->dataDescrition->addFieldDescription( 'QPB_DESCRIPTION', t_string, false, 255 );
			$this->dataDescrition->addFieldDescription( 'QPB_PUBLISHED', t_integer, false );
			$this->dataDescrition->addFieldDescription( 'QPB_THEME', t_integer, false );
			$this->dataDescrition->addFieldDescription( 'QPB_PROPERTIES', t_string, false );

			$QPB_STATUS = 0;
		}

		function loadEntry( $QPB_ID, $kernelStrings )
		//
		// Loads entry from database
		//
		//		Parameters:
		//			$QPB_ID - book identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_qp_selectBook;

			$this->QPB_ID = $QPB_ID;

			// Load database record
			//
			$data = db_query_result( $qr_qp_selectBook, DB_ARRAY, $this );
			if ( PEAR::isError($data) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( is_array($data) )
				$this->loadFromArray( $data, $kernelStrings, false, array( s_datasource=>s_database ) );

			return null;
		}

		function onBeforeSet( $array, $params = null )
		//
		// onBeforeSet event handler
		//
		//		Parameters:
		//			$array - data source array
		//			$params - packed parameters. Must contain following keys:
		//				'source' - source of data - s_form, s_database
		//
		{
		}

		function getValuesArray( )
		{
			$thisFields = array_keys((array)$this);

			$result = array();

			foreach( $thisFields as $field )
			{

				$descriptor = is_object( $this->dataDescrition ) ? $this->dataDescrition->getFieldDescriptor( $field ) : null;

				if ( !is_null($descriptor) )
					$result[$field] = $this->$field;

			}

			return $result;
		}


		function saveEntry( $action, $kernelStrings, $U_ID )
		//
		// Saves settings to the database
		//
		//		Parameters:
		//			$action - form action
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_error
		//
		{
			global $qr_qp_selectMaxQPB_ID;
			global $qr_qp_insertBook;
			global $qr_qp_updateBook;
			global $qp_treeClass;

			$this->QPB_ID_PARENT = TREE_ROOT_FOLDER;

			if ( $action == ACTION_NEW )
			{
				$QPB_ID = $qp_treeClass->addmodFolder( $action, $U_ID, TREE_ROOT_FOLDER, $this->getValuesArray( ), $kernelStrings, false, null, null, true, false, null, $checkFolderName = false );
				if ( PEAR::isError($QPB_ID) )
					return $QPB_ID;
				$this->QPB_ID = $QPB_ID;
			}

			if ( $this->QPB_THEME == "" )
				$this->QPB_THEME = null;

			$res = db_query( $qr_qp_updateBook, $this );

			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return null;
		}

		function deleteEntry( $QPB_ID, $kernelStrings )
		//
		// Delete entry from database
		//
		//		Parameters:
		//			$QPB_ID - book identifier
		//			$kernelStrings - Kernel localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $qr_qp_deleteBook;

			$this->QPB_ID = $QPB_ID;

			$data = db_query( $qr_qp_deleteBook, $this );

			if ( PEAR::isError($data) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return null;
		}
	}
?>