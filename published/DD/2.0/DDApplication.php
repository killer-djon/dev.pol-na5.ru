<?php
	class DDApplication extends WbsApplication {
		static $instance;
		/**
		 * @var DDDataModel
		 */
		protected $dataModel;
		protected $zohoEnabled;
		
		protected function __construct() {
			parent::__construct("DD");
			$this->dataModel = new DDDataModel($this);
			waLocale::loadFile($this->getPath("localization", true), "dd");
		}
		
		/**
		 * @return DDApplication
		 */
		public static function getInstance() {
			if (self::$instance)
				return self::$instance;
			self::$instance = new self();
			return self::$instance;
		}
		
		public function getDataModel() {
			return $this->dataModel;
		}
		
		/**
		 * @return DDFoldersTree
		 */
		public function getFoldersTree($projectId = false) {
			return $this->dataModel->getFoldersTree($projectId);
		}
		
		public function userHasAccessToSettings($user) {
			
		}
		
		public function zohoEnabled () {
			if ($this->zohoEnabled === null)
				$this->zohoEnabled = ($this->getSetting("ZOHOEDITSTATE") != "DISABLED");
			return $this->zohoEnabled;
		}
		
		public function getZohoKey () {
			$result = $this->getSetting("ZOHOSECRETKEY");
			return is_null($result) ? '' : $result;
		}
		
		public function getLinkViews() {
			$views = array ("grid", "list", "thumb_list", "tiles");
			$result = array ();
			foreach ($views as $cView) {
				$result[$cView] = waLocale::getStr("dd_public", "view_" . $cView);
			}
			return $result;
		}
	}
?>