	<?

	class Access {
		protected static $READ = 1;  
		protected static $WRITE = 2; 
		protected static $FULL = 4;		
		
		private $right = null;
		public function __construct($right) {
			$this->right = $right;
		}

		/**
		 * @return Boolean
		 */
		public function isRead() {
			return ($this->right[AR_VALUE] & self::$READ > 0);
		}
		/**
		 * @return Boolean
		 */
		public function isWrite() {
			return ($this->right[AR_VALUE] & self::$WRITE > 0);
		}
		/**
		 * @return Boolean
		 */
		public function isFull() {
			return ($this->right[AR_VALUE] & self::$FULL > 0);
		}
		
		public function getBitMask() {
			return $this->right["AR_VALUE"];
		}
	}
	
?>