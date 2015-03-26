<?php
	class PDApplication extends WbsApplication {
		static $instance;
		public static $noLocale = false;
		private $AlbumList;
		static $APP_NAME = "PD";
		
		protected function __construct() {
			parent::__construct("PD");
//			if (!self::$noLocale)
				//Locale::loadFile($this->getPath("localization"), "pd");
		}
		
		/**
		 * @return PDApplication
		 */
		public static function getInstance() {
			if (self::$instance)
				return self::$instance;
			self::$instance = new self();
			return self::$instance;			
		}
		
		public function getAppName()
		{
		    return self::$APP_NAME;
		}
		

//		public function getAlbumListThumb($offset = 0, $limit = 20) {
//			$sql = new CSelectSqlQuery("PIXFOLDER", "PF");
//			$sql->setSelectFields(array(
//				"PF.PF_ID", 
//				"PF.PF_NAME", 
//				"PF.PF_DATESTR",
//				"PL.PL_ID",
//				"PL.PL_DISKFILENAME",
//				"PL.PL_FILETYPE",
//				"COUNT(PL2.PL_ID) AS PHOTOS_COUNT"
//			));
//			
//			$sql->setOrderBy("PF_SORT");
//			$sql->leftJoin("PIXLIST", "PL", "PF.PF_THUMB = PL.PL_ID");
//			$sql->leftJoin("PIXLIST", "PL2", "PL2.PF_ID = PF.PF_ID");
//			$sql->setGroupBy("PF.PF_ID");
//			
//			$sql->setLimit($offset, $limit);
//			$rows = Wdb::getData($sql);
//			
//			$sql = new CSelectSqlQuery("PIXLIST");
//			$sql->setSelectFields('COUNT(PL_ID)');
//			$imageCount = Wdb::getFirstField($sql);
//			
//			$sql = new CSelectSqlQuery("PIXCOMMENTS");
//			$sql->setSelectFields('COUNT(PC_ID)');
//			$sql->addConditions('PC_TYPE = 3'); // TYPE_PENDING
//			$pendingCommentCount = Wdb::getFirstField($sql);
//			
//			return array(
//				'status' => 'OK',
//				'data' => $rows,
//				'total' => count($rows),
//				'imageCount' => $imageCount,
//				'pendingComment' => $pendingCommentCount
//			);			
//		}
		
		public function getPublicdataPath($albumId = null) {
			return $this->getPath("")."/../publicdata/".Wbs::getDbkeyObj()->getDbkey()."/attachments/pd/".$albumId;
		}
		
		public function getAppPath () {			
			return WBS::getSystemObj()->files()->getAppPath("PD", "") ;
		}
		
		private function _getDataPath() {
			return WBS::getSystemObj()->files()->getDataPath();
		}
		private function _getPublicDataPath() {
			return WBS::getSystemObj()->files()->getPublishedPath("publicdata");
		}
		
		public function getDataAttachPath($albumId = null) {
			$dataPath = $this->_getDataPath();
			$path = $dataPath ."/". WBS::getDbkeyObj()->getDbkey() ."/attachments/pd/files";
			if ( $albumId )
				$path .= "/". $albumId;
			return $path;
		}
		public function getPublicDataAttachPath($albumId = null) {
			$dataPath = $this->_getPublicDataPath();
			$path = $dataPath ."/". WBS::getDbkeyObj()->getDbkey() ."/attachments/pd";
			if ( $albumId )
				$path .= "/". $albumId;
			return $path;
		}
		
		static function getDataUserThemesPath($file = '')
		{
		    $file = ( !empty( $file ) ) ? '/'.$file :  '';
		    return WBS::getSystemObj()->files()->getPublishedPath('/publicdata/').
		            WBS::getDbkeyObj()->getDbkey() ."/attachments/pd/themes".$file;
		}
		
	    static function getPublishedUserThemesUrl($file)
		{
		    $file = ( !empty( $file ) ) ? '/'.$file :  '';
		    return Url::get('/publicdata/', true).
		            WBS::getDbkeyObj()->getDbkey() ."/attachments/pd/themes".$file;
		}		
		
		/**
		 * @param string $url		'path1/path2' or ''
		 * @param array $param		[ mod_rewrite, !mod_rewrite ]
		 * @return string
		 */
		static function getFrontendUrl($url, $param = null) 
		{
		    $dir = Wbs::getSystemObj()->getWebUrl();
		    $dir = ( $dir == '/' || empty($dir) ) ? '/' : $dir;
		    $photos = ( Wbs::getSystemObj()->getFrontendType() == 'PD' ) ? '' : 'photos/';		    
		    if ( $param && !empty($param) ) {
		        if( Wbs::getSystemObj()->isModeRewrite() || wbs::isHosted()) 
		            $rewrite = $param[0];
        		else
        			$rewrite = $param[1];	
		    }
		    else
		        $rewrite = '';
		        
		    $url = ( $url == '/' ) ? '' : $url;
		        
		    return Url::getServerUrl() . $dir . $photos . $url . $rewrite;
		}
    		
    	/**
    	 * @param array $data
    	 * @param string $rootNodeName
    	 * @param xml $xml
    	 * @return xml
    	 */
    	private function arrayToXML($data, $rootNodeName = 'data', $xml=null)
    	{
    		// turn off compatibility mode as simple xml throws a wobbly if you don't.
    		if (ini_get('zend.ze1_compatibility_mode') == 1)
    		{
    			ini_set ('zend.ze1_compatibility_mode', 0);
    		}
    		
    		if ($xml == null)
    		{
    			$xml = simplexml_load_string("<$rootNodeName />");
    		}
    		
    		// loop through the data passed in.
    		foreach($data as $key => $value)
    		{
    			// no numeric keys in our xml please!
    			if (is_numeric($key))
    			{
    				// make string key...
    				$key = "unknownNode_". (string) $key;
    			}
    			
    			// replace anything not alpha numeric
    			$key = preg_replace('/[^a-z]/i', '', $key);
    			
    			// if there is another array found recrusively call this function
    			if (is_array($value))
    			{
    				$node = $xml->addChild($key);
    				// recrusive call.
    				arrayToXML($value, $rootNodeName, $node);
    			}
    			else 
    			{
    				// add single node.
    				$value = htmlentities($value);
    				$xml->addChild($key,$value);
    			}
    			
    		}
    		// pass back as string. or simple xml object if you want!
    		return $xml->asXML();
    	}	
		
	}
	
?>