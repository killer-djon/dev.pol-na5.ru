<?
	/**
		$access = new AccessRights();
		$access->loadRightsToUser(CurrentUser::getInstance());
		if ($access->getRight("/ROOT/PD/FOLDERS", "2.")->isFull() ) {...}
	 */
	class AccessRights {
		/**
		 * @var WbsUser
		 */
		private $user = null;
		/**
		 * @var array
		 */
		private $rights = null;
		
		public function loadRightsToUser($user) {
			$this->selectUser($user);
			$this->loadRights();
			return $this;
		}
		/**
		 * @param WbsUser $user
		 * @return AccessRights
		 */
		public function selectUser($user) {
			$this->user = $user;			
			return $this;
		}
		/**
		 * @return AccessRights
		 */		
		public function loadRights() {
			if ( $this->user )
			{
				$sql = new CSelectSqlQuery ("U_ACCESSRIGHTS", "A");
				$sql->addConditions("A.AR_ID", $this->user->getId());
	
				$this->rights = Wdb::getData($sql);
				if (!$this->rights)
					throw new RuntimeException ("Not found user: " . $this->user->getId());
				return $this;
			}
			else
				throw new RuntimeException ("Not select user");
		}
		/**
		 * @param string $path
		 * @param string $objId
		 * @return Access
		 */
		public function getRight($path, $objId) {
			if ( $this->rights )
			{
				foreach ( $this->rights as $row ) {
					if ($row['AR_PATH'] == $path && $row['AR_OBJECT_ID'] == $objId)
						return new Access($row);
				}
				throw new RuntimeException ("Not found right to path = " . $path . " value = " . $objId);
				return new Access(array("AR_VALUE" => 0));
			}
			else
				throw new RuntimeException ("Not load right.");
		}

		private static $instance;
		/**
		 * @return AccessRights
		 */
		public static function getInstance() {
			if (self::$instance)
				return self::$instance;
			self::$instance = new self();
			return self::$instance;
		}		
		
	}
?>