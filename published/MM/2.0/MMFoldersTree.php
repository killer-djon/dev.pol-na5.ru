<?php

	class MMFoldersTree extends WbsFoldersTree {

		private $app;

		public function __construct($app, $user) {
			$this->app = $app;
			parent::__construct($user, 'MMFOLDER', 'MMF_', '/ROOT/MM/FOLDERS');
		}

		public function loadTree($withStatInfo = false) {
			parent::loadTree();
		}

		public function getReadableNodes($minimalRights) {
			$nodes = $this->getAvailableNodes();

			$ids = array ();
			foreach ($nodes as $cNode) {
				if($cNode->Rights >= $minimalRights && $cNode->Id != 'ROOT')
					$ids[$cNode->Id] = array('Name'=>$cNode->Name, 'Level'=>$cNode->Level);
			}
			$ids[0] = array('Name'=>'Unsorted', 'Level'=>1);;
			return $ids;
		}

		public function getWriteableNodes() {
			$nodes = $this->getAvailableNodes();
			$ids = array ();
			foreach ($nodes as $cNode) {
				$ids[$cNode->Id] = array($cNode->Name, $cNode->Rights);
			}
			return $ids;
		}

		protected function createNode($row) {
			$node = new MMFolder($this, $row);
			return $node;
		}

	}
?>