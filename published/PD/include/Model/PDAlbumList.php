<?
	class PDAlbumList {
		private $albums;
		
		public function __construct() {
			Kernel::incPackage("rights");
		}
		
		/**
		 * @return PDAlbumList
		 */
		public function loadAlbumList() {
			$sql = new CSelectSqlQuery("PIXFOLDER");
			$sql->setSelectFields("*");
			$sql->setOrderBy("PF_SORT");
			$rows = Wdb::getData($sql);
			$arr = array();			
			foreach ($rows as $row) {
				$arr[] = new PDAlbum($row);
			}
			$this->albums = $arr;
			return $this;
		
		}
		
		/**
		 * @return array PDAlbum 
		 */
		public function getList() {
			return $this->albums;
		}
		
		/**
		 * @return PDAlbum
		 */
		public function getAlbum($idAlbum) {
			if (!$this->albums) {
				$this->loadAlbumList();
			}
			foreach ($this->albums as $album) {
				if ( $album->PF_ID == $idAlbum)
					return $album;
			}
			return false;
		}
		/**
		 * @return PDAlbum
		 */
		public function getAlbumByName($name) {
			foreach ($this->albums as $album) {
				if ( $album->PF_NAME == $name)
					return $album;
			}
			return false;
		}	
		
		public static function makeListToJson($list) {
			$rights_model = new PDRightsModel();
			$rights_model->loadUserRights(CurrentUser::getId(), true);
			
			$access = AccessRights::getInstance()->loadRightsToUser(CurrentUser::getInstance());
			$out = array();
			foreach ($list as $key => $item) {
				$out[$key] = array( $item->PF_ID, 
									$item->PF_NAME, 
									$access->getRight("/ROOT/PD/FOLDERS", $item->PF_ID)->getBitMask() );
			}
			return $out;
		}
		
		
	}

?>