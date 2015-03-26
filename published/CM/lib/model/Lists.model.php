<?php

$classes = array(
	'CMPlugins' => 'published/CM/lib/entity/CMPlugins.class.php',
	'CMPlugin' => 'published/CM/lib/entity/CMPlugin.class.php',
);
AutoLoad::load($classes);

class ListsModel extends DbModel
{
	protected $table = "CLIST";
	
	const SC_LIST = -1;
	/**
	 * Returns info about
	 *  
	 * @param $id
	 * @return array
	 */
	public function get($id) 
	{
	    if ($id == self::SC_LIST) {
	        return array(
	            'CL_ID' => self::SC_LIST,
	            'CL_NAME' => _s('Store customers'),
	            'CL_C_ID' => 0,
	            'CL_SQL' => 'SC_ID > 0',
	        	'CL_SHARED' => 0,
	            'CL_SEARCH' => ''
	        );
	    } else {
			$sql = "SELECT * FROM ".$this->table." WHERE CL_ID = i:id";
			return $this->prepare($sql)->query(array('id' => $id))->fetchAssoc();
	    }
	}
	
	public function getAll($contact_id = false, $implicit = false)
	{
		$sql = "SELECT * FROM ".$this->table." 
				WHERE (".($contact_id ? "CL_C_ID = i:contact_id OR " : "")." CL_SHARED = 1)
				".($implicit ? "AND CL_SQL = ''" :"")."
				ORDER BY CL_NAME ASC";
		return $this->prepare($sql)->query(array('contact_id' => $contact_id))->fetchAll('CL_ID');		
	}
	
	public function getByIds($ids)
	{
	    if (!is_array($ids)) {
	        $cond = "= ".(int)$ids;
	    } else {
	        $cond = "IN ('".implode("', '", $this->escape($ids))."')";
	    }
	    $sql = "SELECT * FROM ".$this->table." WHERE CL_ID ".$cond;
	    $data = $this->query($sql)->fetchAll();
	    if (in_array(-1, $ids)) {
	        $data[] = $this->get(-1);
	    }
	    $lists = CMPlugins::getInstance(true)->getData('contact_lists', $ids);
	    foreach ($lists as $list) {
	    	$list['full'] = true;
	    	$data[] = $list;
	    }
	    return $data;
	}
	
	
	public static function getCondition($code = false)
	{
		$conditions = array(
			1 => _('contains'),
			2 => _('exactly matches'),
			3 => _('starting with'),
			4 => _('does not contain'),
			5 => _('earlier than'),
			6 => _('later than'),
			7 => _('in the range'),
			8 => _('greater than'),
			9 => _('equal to'),
			10 => _('less than')
		);
		if (!$code) {
		    return $conditions;
		}
		if (isset($conditions[$code])) {
			return $conditions[$code];
		} else {
			return null;
		}
	}
	
	public static function getLink($code)
	{
		$links = array(
			1 => _('and'),
			2 => _('or'),
		);				
		return isset($links[$code]) ? $links[$code] : null;
	}
	
	public static function getSearchDescription($search_string, $json = true, $full = true, $list_id = false)
	{
        $search_info = $json ? json_decode($search_string, true) : $search_string;

		switch ($search_info['type']) {
			case 'simple': {
				return _('Name').' <b>&quot;'.$search_info['data'].'&quot;</b>';
			}
			case 'advanced':
				$str = "";
				foreach ($search_info['data'] as $i => $row) {
					if ($str) {
						$str .= ", ";
					}
					if (is_numeric($row['field'])) {
						$field = ContactType::getField($row['field'], User::getLang());
						if ($field['type'] == 'COUNTRY') {
							$countries = Wbs::getCountries();
							$row['val'] = $countries[$row['val']];
						} elseif ($field['type'] == 'CHECKBOX') {
							$row['val'] = $row['val'] ? _s('Yes') : _s("No");
						}
						$str .= $full ? $field['name'] . ' <b>&quot;'.$row['val'].'&quot;</b>' : $row['val'];
					} else {
						switch ($row['field']) {
							case 'added': {
								$str .= $full ? _("Contacts added by")." <b>&quot;"._("subscribers via signup form")."&quot;</b>" : _("subscribers via signup form");
								break;
							}
							case 'createcid': {
								$str .= $full ? _("Contacts added by")." <b>&quot;".Contact::getName($row['val'])."&quot;</b>" : Contact::getName($row['val']);
								break;
							}
							case 'days': {
								$str .= $full ? _("Adding date")." <b>&quot;"._("in last ").$row['val']._(" days")."&quot;</b>" : _("in last ").$row['val']._(" days");
								break;
							}
							case 'from': {
								$str .= $full ? _("Adding date")." <b>&quot;"._("from ").$row['value'] : _("from ").$row['value'];
								if (isset($search_info['data'][$i + 1]['field']) || $search_info['data'][$i + 1]['field'] != 'to') {
								    if ($full) {
    									$str .= "&quot;</b>";
								    }
								}
								break;
							}
							case 'to': {
								if (isset($search_info['data'][$i - 1]['field']) && $search_info['data'][$i-1]['field'] == 'from') {
									$str .= _(" to ").$row['value'].($full ? "&quot;</b>" : "");
								} else {
									$str .= $full ? _("Adding date")." <b>&quot;to ".$row['value']."&quot;</b>" : "to ".$row['value'];
								}
								break;
							}
							case 'folder_id': {
								$folder_model = new ContactFolderModel();
								$folder_info = $folder_model->get(str_replace("%", "", $row['val']));
								$str .= $full ? _("Search in folder")." <b>&quot;".$folder_info['NAME']."&quot;</b>" : $folder_info['NAME'];
								break;
							}
							case 'type_id' : {
								$types = ContactType::getTypeNames(User::getLang());
								$type_name = $types[$row['val']];
								$str .= $full ? _("Contact type")." <b>&quot;".$type_name."&quot;</b>" : $type_name;
								break;
							}
						}
					}
				}				
				return $str;
			case 'smart': {
				$str = "";
                $fields = ContactType::getAllFields(User::getLang(), false, false, true);				
				foreach ($search_info['data'] as $k => $row) {
					if ($row['link']) {
						$str .= " ".self::getLink($row['link'])." ";
					}
					if ($row['field'] == 'folder_id') {
						$field = array(
							'name' => _s('Folder')
						);
						$folder_model = new ContactFolderModel();
						$folder_info = $folder_model->get(str_replace("%", "", $row['val']));
						$row['val'] = $folder_info['NAME'];
					} elseif ($row['field'] == 'type_id') {
						$field = array(
							'name' => _s('Contact type')
						);
						$types = ContactType::getTypeNames(User::getLang());
						$row['val'] = $types[$row['val']];
					} elseif ($row['field'] == -4) {
						if ($row['val'] == -1) {
							$row['val'] = _s("subscribers via sign up form");
						} else {
							$row['val'] = Contact::getName($row['val']);
						}
						$field = $fields[$row['field']];
					} else {
						$field = $fields[$row['field']];
					}
					
					if (!$field && $list_id) {
					    unset($search_info['data'][$k]);
					    Contact::searchByFields($search_info['data'], false, 1, "", $list_id);
					    continue;
					}
					if ($full) {
    					$str .= $field['name'];
    					if (is_numeric($row['field']) && ($row['field'] > 0 || $field['type'] != 'VARCHAR')) {
    						$str .= " ".(isset($row['cond']) ? self::getCondition($row['cond']) : "")." ";
    					}
					}
                    if (is_numeric($row['field']) && $fields[$row['field']]['type'] == 'DATE') {
		                if ($row['cond'] == 7) {
		                    $v = explode(" || ", $row['val']);
		                    $row['val'] = WbsDateTime::fromMySQL($v[0])." — ".WbsDateTime::fromMySQL($v[1]);
		                } else {
                            $row['val'] = WbsDateTime::fromMySQL($row['val']);
		                }
                    }				
					$str .= $full ? ' <b>&quot;'.$row['val'].'&quot;</b>' : $row['val'];
				}
				return $str;
			}
		}
		
	}
	
	/**
	 * Returns set of the lists, which are visible to the current user.
	 * 
	 * @param $contact_id
	 * 
	 * @return array
	 */
	public function getByContactId($contact_id) 
	{
		$data = CMPlugins::getInstance()->getData('contact_lists', array());
		$result = array();
		foreach ($data as $row) {
			$row['CL_SQL'] = 2;
			$result[$row['CL_ID']] = $row;
		}
		
		$sql = "SELECT * FROM ".$this->table." 
				WHERE CL_C_ID = i:contact_id OR CL_SHARED = 1
				ORDER BY CL_NAME ASC";
		return $result + $this->prepare($sql)->query(array('contact_id' => $contact_id))->fetchAll('CL_ID');
	}
		
	
	/**
	 * Add new list
	 * 
	 * @param $name
	 * @param $sql
	 * @param $search_info - array(
	 * 		type => number of the type (simple, advanced, smart)...
	 * }
	 * 
	 * @return insert if the lists
	 */
	public function add($name, $sql = false, $search_info = false)
	{
		$q = "INSERT INTO ".$this->table." 
			  SET CL_NAME = s:name, 
			  	  CL_SQL = s:sql, 
			  	  CL_SEARCH = s:search, 
			  	  CL_C_ID = i:contact_id";
		$list_id = $this->prepare($q)->query(array('name' => $name, 'sql' => $sql, 'search' => json_encode($search_info), 'contact_id' => User::getContactId()))->lastInsertId();
		// For dinamic lists 
		if ($sql) {
			$viewmode = User::getSetting('VIEWMODEsearch0', 'CM');
			if ($viewmode) {
				User::setSetting('VIEWMODElists'.$list_id, $viewmode, 'CM');
			}
			$fields = User::getSetting('SHOWFIELDSsearch0', 'CM');
			if ($fields) {
				User::setSetting('SHOWFIELDSlists'.$list_id, $fields, 'CM');
			}
		}
		return $list_id; 
	}
	
	public function save($id, $name, $search_sql = false, $search_info = false)
	{
		$sql = "UPDATE ".$this->table." SET CL_MODIFYCID = i:contact_id";
		if ($name !== false) {
			$sql .= ", CL_NAME = s:name";
		}
		if ($search_sql !== false) { 
			$sql .= ", CL_SQL = s:sql";
		}
		if ($search_info !== false) { 
			$sql .= ", CL_SEARCH = s:search_info";
		}
		$sql .= ", CL_MODIFYDATETIME = NOW()
				WHERE CL_ID = i:id";
		return $this->prepare($sql)->exec(array(
			'id' => $id, 
			'name' => $name, 
			'contact_id' => User::getContactId(),
			'sql' => $search_sql, 
			'search_info' => json_encode($search_info)
		));		
	}
	
	/**
	 * Delete list by id
	 * 
	 * @param $id
	 * @return bool
	 */
	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->table." WHERE CL_ID = i:id";
		return $this->prepare($sql)->exec(array('id' => $id));
	}
	
	/**
	 * Change shared property of the list by it's id
	 * 
	 * @param $id
	 * @param $shared 
	 * @return bool
	 */
	public function share($id, $shared = 1)
	{
		if (!$shared && User::isAdmin('CM')) {
			$sql = "UPDATE ".$this->table." 
					SET CL_SHARED = i:shared, CL_C_ID = ".(int)User::getContactId()." 
					WHERE CL_ID = i:id";			
		} else {
			$sql = "UPDATE ".$this->table." 
					SET CL_SHARED = i:shared 
					WHERE CL_ID = i:id";
		}
		return $this->prepare($sql)->exec(array('id' => $id, 'shared' => $shared));
	}
}


?>