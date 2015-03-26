<?php

class CMContactsSearchAction extends UGViewAction
{
	protected $search_id;
	protected $advanced;
	
	public function __construct()
	{
		parent::__construct();
		$this->search_id = Env::Get('id', false);
		$this->type = Env::Get('type');
	}
	
	public function prepareData()
	{
		$search_info = false;
		if ($this->type == 'list') {
			$lists_model = new ListsModel();
			$list_info = $lists_model->get($this->search_id);
						
			$search_info = json_decode($list_info['CL_SEARCH'], true);
			$this->type = $search_info['type'];
			$search_info = ($this->type == 'simple' ? $search_info['data'] : $search_info['data']);
			$this->smarty->assign('list', $this->search_id);
			$this->smarty->assign('list_name', $list_info['CL_NAME']);
		}
		elseif (!$this->type) {
		    $last_search = User::getSetting('LASTSEARCH', 'CM');
		    if ($last_search) {
		        $last_search = json_decode($last_search, true);
        		$this->type = $last_search['type'];
                $search_info = $last_search['data'];
		    }
		} 
		
		if ($this->type == 'smart') {
			$fields = ContactType::getAllFields(User::getLang(), ContactType::TYPE_FIELD, true, true);
			$js_fields = array();
			foreach ($fields as $field) {
				if ($field['type'] != 'IMAGE' ) {
					$js_fields[$field['id']] = array(
						$field['id'],
						$field['name'],
						$field['type']
					);
				}
			}
		
			$js_fields['folder_id'] = array('folder_id', _s('System') ." — ". _s('Folder'), 'SELECT');
			$js_fields[-2] = array(-2, _s('System') ." — ". _s('Contact ID'), 'NUMERIC');
			$js_fields[-3] = array(-3, _s('System') ." — ". _s('Adding date'), 'DATE');
			$js_fields[-5] = array(-5, _s('System') ." — ". _s('Adding application'), 'SELECT');
			$js_fields['type_id'] = array('type_id', _s('System') ." — ". _s('Contact type'), 'SELECT');
			if (User::isAdmin('CM') || User::hasAccess('UG')) {
				$js_fields[-4] = array(-4, _s('System') ." — ". _s('Added by'), 'SELECT');
			}
			$this->smarty->assign('folders', Contact::getFolders());
			$this->smarty->assign('fields', json_encode(array_values($js_fields)));
			$this->smarty->assign("conditions", json_encode(ListsModel::getCondition()));
			$rights = new Rights(User::getId());
			$apps = $rights->getApps();
			$available_apps = array('SC', 'CM', 'UG', 'ST', 'MT');
			foreach ($apps as $id => $name) {
				if (!in_array($id, $available_apps)) {
					unset($apps[$id]);
				}
			}
			$this->smarty->assign('apps', $apps);
			$this->smarty->assign('types', ContactType::getTypeNames(User::getLang()));
						
			if (User::isAdmin('CM') || User::hasAccess('UG')) {	
				$contacts_model = new ContactsModel();
				$sql = "SELECT C1.* 
						FROM CONTACT C2 JOIN
							 CONTACT C1 ON C2.C_CREATECID = C1.C_ID
						GROUP BY C2.C_CREATECID
						ORDER BY C1.C_FULLNAME";
				$data = $contacts_model->query($sql);
				$created_contacts = array(
					-1 => _s('subscribers via sign up form')
				);
				Contact::useStore(false);
				foreach ($data as $row) {
					$created_contacts[$row['C_ID']] = Contact::getName($row['C_ID'], false, $row);
				}
				Contact::useStore(true);
				$this->smarty->assign('users', $created_contacts);
			}
			$this->smarty->assign('countries', Wbs::getCountries());
		}

		if ($this->type == 'advanced') {

			$fields = ContactType::getAllFields(User::getLang());
			$sections = array();
			foreach ($fields as $field) {
				if ($field['section'] && $field['type'] != 'IMAGE') {
					$sections[$field['section']]['fields'][$field['id']] = $field;
				} elseif (!$field['section']) {
					$sections[$field['id']] = $field;
				}
			}
			foreach ($sections as $i => $s) {
			    if (!isset($s['fields']) || !$s['fields']) {
			        unset($sections[$i]);
			    }
			}
			$this->smarty->assign('fields', array_values($sections));
						
			if (User::hasAccess('UG')) {
				// Get all contacts which added contacts
				$sql = "SELECT C2.* 
						FROM CONTACT C1 JOIN 
							 CONTACT C2 ON C1.C_CREATECID = C2.C_ID
						GROUP BY C2.C_ID";
				$contacts_model = new ContactsModel();
				$data = $contacts_model->query($sql)->fetchAll('C_ID');
				$contacts_created = array(); 
				Contact::useStore(false);
				foreach ($data as $contact_id => $contact_info) {
					$contacts_created[$contact_id] = Contact::getName($contact_id, false, $contact_info);	
				}
				Contact::useStore(true);
			} else {
				$contacts_created = array(
					User::getContactId() => User::getName()
				);
			}
			$this->smarty->assign('contacts_created', $contacts_created);
			
			// Get all available folders
			$rights = new Rights(User::getId());
			$folders = $rights->getFolders('CM', false, true, Rights::FLAG_NOT_EMPTY|Rights::FLAG_ARRAY_OFFSET|Rights::FLAG_RIGHTS_INT);
			$this->smarty->assign('folders', $folders);	

			$this->smarty->assign('types', ContactType::getTypeNames(User::getLang()));
			$this->smarty->assign('countries', Wbs::getCountries());
		}
				
		$this->smarty->assign('type', $this->type);
		if ($search_info) {
		    if ($this->type == 'simple') {
		        $this->smarty->assign('search', $search_info);
		    } else {
		        $fields = ContactType::getAllFields(User::getLang(), false, false, true);
		        foreach ($search_info as $k => $i) {
		            if (is_numeric($i['field']) && $fields[$i['field']]['type'] == 'DATE') {
		                if ($i['cond'] == 7) {
		                    $v = explode(" || ", $i['val']);
		                    $search_info[$k]['val'] = WbsDateTime::fromMySQL($v[0])." || ".WbsDateTime::fromMySQL($v[1]);
		                } else {
                            $search_info[$k]['val'] = WbsDateTime::fromMySQL($i['val']);
		                }
		            }
		        }
		        $this->smarty->assign('search', json_encode($search_info));    
		    }
		    
		} elseif ($this->type == 'simple' && Env::Get('search')) {
		    $this->smarty->assign('search', Env::Get('search'));
		}		
		
		$this->smarty->assign('last_list', User::getSetting('LASTLIST', 'CM'));
						
		$date_format = mb_strtolower(Wbs::getDbkeyObj()->getDateFormat());
		$this->smarty->assign("dateFormat", mb_substr($date_format, 0, -2));		
	}
	
}
?>