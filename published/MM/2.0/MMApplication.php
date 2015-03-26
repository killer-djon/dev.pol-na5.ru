<?php

	class MMApplication extends WbsApplication
	{
		static $instance;
		protected $dataModel;
		private $foldersTree;

		protected function __construct()
		{
			parent::__construct('MM');
			waLocale::loadFile($this->getPath('localization'), 'mm');
		}

		/**
		 * Returns instance 
		 * 
		 * @return MMApplication
		 */
		public static function getInstance()
		{
			if(self::$instance)
				return self::$instance;
			self::$instance = new self();
			return self::$instance;
		}

		public function getFoldersTree()
		{
			if($this->foldersTree)
				return $this->foldersTree;

			$this->foldersTree = new MMFoldersTree($this, CurrentUser::getInstance());

			return $this->foldersTree;
		}

		public function getDataModel() {
			return $this->dataModel;
		}

		public function getSenders($U_ID)
		{
			$accounts = false; 
			$rights = new Rights($U_ID);
			if($rights->get('MM', Rights::FOLDERS, 'INBOX', Rights::MODE_ONE, Rights::RETURN_OBJECT)->isRead()) {
				$db_model = new DbModel();
				$sql = "SELECT MMA_NAME, MMA_EMAIL, MMA_DOMAIN, MMA_INTERNAL FROM MMACCOUNT";
				$accounts = $db_model->query($sql)->fetchAll();
			}
			/*
			$sql = "SELECT M.MMA_ID, M.MMA_NAME, M.MMA_EMAIL, M.MMA_DOMAIN, M.MMA_INTERNAL FROM "
				."MMACCOUNT AS M, U_ACCESSRIGHTS AS U, UGROUP_USER AS UGU, UG_ACCESSRIGHTS AS UGA WHERE "
				."(U.AR_ID=s:U_ID AND U.AR_OBJECT_ID='inbox' AND U.AR_VALUE>0) OR "
				."(UGU.UG_ID=UGA.AR_ID AND UGU.U_ID=s:U_ID AND UGA.AR_OBJECT_ID='inbox' AND UGA.AR_VALUE>0) "
				."GROUP BY M.MMA_ID";
			*/

			if(!is_array($accounts))
				return array();

			$senders = array();
			foreach($accounts as $row)
			{
				if($row['MMA_INTERNAL'])
					$senders[] = trim($row['MMA_NAME'].' <'.$row['MMA_EMAIL'].'@'.$row['MMA_DOMAIN'].'>');
				else
					$senders[] = trim($row['MMA_NAME'].' <'.$row['MMA_EMAIL'].'>');;
			}
			sort($senders);
			return $senders;
		}

		public function getContacts($start=0, $count=100)
		{
			$sql = new CSelectSqlQuery('CONTACT');
			$sql->setOrderBy('C_FIRSTNAME', 'ASC');
			$sql->setLimit($start, $count);
			return Wdb::getData($sql);
		}

		public function getLists()
		{
			$lists_model = new ListsModel();
			$lists =  $lists_model->getByContactId(User::getContactId(), false, false);
			if (Wbs::getDbkeyObj()->appExists('SC') && User::isAdmin('CM')) {
			    $lists = array_merge(array(
			        array('CL_ID' => -1, 'CL_NAME' => _s('Store customers'))
			    ), $lists);
			}
			return $lists;
		}

		public function getListsCount($lists)
		{
			$contacts_model = new ContactsModel();
			$data = $contacts_model->getWithEmailsByLists($lists, 0, User::getId());
			return $data->count();
		}

		public function getSentCount($time=false)
		//
		//	Returns count of recipients of $time (-> date) sent and pending messages
		//
		{
			if(!$time)
				$time = time();

			$date = date('Y-m-d', $time);

			$sql = new CSelectSqlQuery('MMSENT');
			$sql->setSelectFields('MMS_COUNT');
			$sql->addConditions("MMS_DATE='$date'");
			$sent = Wdb::getFirstField($sql);

			$sql = new CSelectSqlQuery('MMMESSAGE');
			$sql->setSelectFields('COUNT(*)');
			$sql->addConditions("MMM_STATUS='".MM_STATUS_PENDING."'");
			$sql->addConditions("DATE(MMM_DATETIME)='$date'");
			$pend = Wdb::getFirstField($sql);

			return intval($sent + $pend);
		}

		public function convertToSqlDateTime($time_stamp)
		{
			return date('Y-m-d H:i:s', $time_stamp);
		}

		public function getMessage($id)
		{
			$message = array();
			if(is_numeric($id)) {

				$sql = new CSelectSqlQuery('MMMESSAGE');
				$sql->addConditions("MMM_ID='$id'");
				$message = Wdb::getRow($sql);

				$res = parseAddressString( $message['MMM_FROM'] . ', ' . $message['MMM_TO'] . ', ' . $message['MMM_CC'] );
				$out = array();
				if( is_array( $res['accepted'] ) )
					foreach( $res['accepted'] as $item )
						$out[] = trim( $item['name'] . ' <' . $item['email'] . '>' );
				$message['all_emails'] = stripslashes(join(', ', $out));

				// TODO: delete next 2 lines after 01.01.2010 (it's for old MM only)
				$uri = WebQuery::getPublishedUrl('', null, true).'MM/html/scripts/getmsgimage.php?fileName=';
				$message['MMM_CONTENT'] = str_ireplace('getmsgimage.php?fileName=', $uri, $message['MMM_CONTENT']);
			}
			else {
				$arr = explode('~', $id, 2);
				if(isset($arr[1])) {
					$sql = new CSelectSqlQuery('MMCACHE');
					$sql->addConditions("MMC_ACCOUNT='".$arr[0]."'");
					$sql->addConditions("MMC_UID='".$arr[1]."'");
					$message = Wdb::getRow($sql);
					if(is_array($message)) {
						foreach($message as $key=>$val) {
							$message[str_replace('MMC_', 'MMM_', $key)] = $val;
						}
					}
				}
			}
			return $message;
		}

		public function getAvailableFolders($minimalRights = 0)
		{
			$foldersTree = $this->getFoldersTree();
			$foldersTree->loadTree();
			$folders = $foldersTree->getReadableNodes($minimalRights);
			return $folders;
		}

		public function doSearch($searchString, $currentPage)
		{
			global $mm_statusNames, $mm_statusStyle, $mm_statusNodes, $mm_virtualFoldersNames;

			$folders = self::getAvailableFolders();
			unset($folders[0]);
			$foldersIds = array_keys($folders);

			$searchString = preg_replace('/ +/', ' ', trim($searchString));
			$searchArray = explode(' ', $searchString);
/*
			$sql = new CSelectSqlQuery('MMMESSAGE', 'M');
			$sql->setSelectFields('M.MMM_ID, M.MMF_ID, M.MMM_STATUS, M.MMM_FROM, M.MMM_TO, M.MMM_SUBJECT, M.MMM_CONTENT, M.MMM_DATETIME');
			foreach($searchArray as $key)
				$sql->addConditions("M.MMM_SUBJECT LIKE '%$key%' OR M.MMM_CONTENT LIKE '%$key%'");
			$sql->addConditions(
				"(M.MMM_USERID='".CurrentUser::getInstance()->getId()."' AND M.MMF_ID='0') OR ".
				"M.MMF_ID='" . join("' OR M.MMF_ID='", $foldersIds) . "'"
			);
			$sql->setOrderBy('M.MMM_ID', 'DESC');
			$res = Wdb::runQuery($sql);
*/

			$mm = $mc = $keys = array();
			$i = 0;
			foreach($searchArray as $key) {
				$mm[] = "MMM_SUBJECT LIKE s:key_$i OR MMM_CONTENT LIKE s:key_$i";
				$mc[] = "MMC_SUBJECT LIKE s:key_$i OR MMC_CONTENT LIKE s:key_$i";
				$keys["key_$i"] = "%".$key."%";
				$i++;
			}
			$keys['U_ID'] = CurrentUser::getInstance()->getId();

			$mm = join(' OR ', $mm);
			$mc = join(' OR ', $mc);

			$db_model = new DbModel();
			$sql = "SELECT MMM_ID, MMF_ID, MMM_STATUS, MMM_FROM, MMM_TO, MMM_SUBJECT, MMM_CONTENT, MMM_DATETIME "
				."FROM MMMESSAGE WHERE ($mm) AND "
				."((MMM_USERID=s:U_ID AND MMF_ID='0') OR "
				."MMF_ID='" . join("' OR MMF_ID='", $foldersIds) . "') UNION "
				."SELECT MMC_UID MMM_ID, MMC_ACCOUNT MMF_ID, MMC_FLAG MMM_STATUS, "
				."MMC_FROM MMM_FROM, MMC_TO MMM_TO, MMC_SUBJECT MMM_SUBJECT, MMC_CONTENT MMM_CONTENT, MMC_DATETIME MMM_DATETIME "
				."FROM MMCACHE WHERE ($mc) ORDER BY MMM_DATETIME";

			$data =  $db_model->prepare($sql)->query($keys);

			$searchResults = array();

			$startIndex = $currentPage * RESULTS_PER_PAGE;
			$stopIndex = $currentPage * RESULTS_PER_PAGE + RESULTS_PER_PAGE;

			$i = -1;
			foreach($data as $row)
			{
				$row['MMM_CONTENT'] = formatMsgLead($row['MMM_CONTENT'], false);

				foreach($searchArray as $key)
					if(stripos($row['MMM_SUBJECT'].$row['MMM_CONTENT'], $key) === false)
						continue 2;

				$i++;
				if($i < $startIndex)
					continue;
				if($i >= $stopIndex)
					continue;

				$row['MMM_SUBJECT'] = preg_replace('/('.join('|', $searchArray).')/iu', '<span class="highlight">$1</span>', $row['MMM_SUBJECT']);
				$row['MMM_CONTENT'] = preg_replace('/('.join('|', $searchArray).')/iu', '<span class="highlight">$1</span>', $row['MMM_CONTENT']);

				$head = '';
				if(preg_match('/(\s?(\s*[^\s]*){0,3}<span class="highlight">.*?<\/span>([^\s]*\s*){0,4})/u', $row['MMM_CONTENT'], $match))
					$head = '...'.$match[1].'...';

				$tail = '';
				foreach($searchArray as $key)
					if(stripos($head.$tail, $key) === false)
						if(preg_match('/(\s?(\s*[^\s]*){0,3}<span class="highlight">'.$key.'<\/span>([^\s]*\s*){0,4})/ui', $row['MMM_CONTENT'], $match))
							$tail .= ' '.$match[1].'...';

				$row['MMM_CONTENT'] = $head.$tail;

				if(strpos($row['MMF_ID'], '@') === false) { // not inbox mode

					$row['statusName'] = $mm_statusNames[$row['MMM_STATUS']];
					$row['statusStyle'] = $mm_statusStyle[$row['MMM_STATUS']];

					if($row['MMF_ID'] == 0) {
						$row['MMF_ID'] = $mm_statusNodes[$row['MMM_STATUS']]; // 'unsortedBox'
						$row['MMF_NAME'] = $mm_virtualFoldersNames[$row['MMM_STATUS']];
					}
					else {
						$row['MMF_NAME'] = $folders[$row['MMF_ID']]['Name'];
					}
				}
				else {
					$row['statusName'] = $mm_statusNames[MM_STATUS_RECEIVED];
					$row['statusStyle'] = $mm_statusStyle[MM_STATUS_RECEIVED];
					$row['MMF_NAME'] = $row['MMF_ID'];
				}

				$row['MMM_DATETIME'] = WbsDateTime::getTime(strtotime($row['MMM_DATETIME']));

				if(strpos($row['MMF_ID'], '@')) {
					$row['MMM_ID'] = $row['MMF_ID'].'~'.$row['MMM_ID'];
				}

				$row['index'] = $i + 1;

				$searchResults['content'][] = $row;
			}
			$searchResults['count'] = $i + 1;

			return $searchResults;
		}

		public function deleteMessages($document)
		{
			$sql = new CDeleteSqlQuery("MMMESSAGE");
			$sql->addConditions("MMM_ID='".join("' OR MMM_ID='", $document)."'");
			Wdb::runQuery($sql);
		}

	}

?>