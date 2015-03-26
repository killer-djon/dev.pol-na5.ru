<?php 

class CMContactsAddAction extends UGViewAction
{
	
	protected $type_id;
	protected $type;
	public function __construct()
	{
		Contact::checkLimits(1);
		parent::__construct();
		$this->type_id = Env::Get('type', Env::TYPE_INT, 1);
	}
	
	
    public function getBackTitle()
    {
        $this->back_url = 'index.php';
    	$mode = Env::Get('mode');
    	$id = Env::Get('id');
    	switch ($mode) {
    		case 'groups':
    			$group = Groups::get($id);
    			return $group['UG_NAME'];
    		case 'folders':
    			$contac_folders_model = new ContactFolderModel();
    			$folder = $contac_folders_model->get($id);
    			return $folder['NAME'];
    		case 'lists':
    			$lists_model = new ListsModel();
    			$list = $lists_model->get($id);
    			return $list['CL_NAME'];
    		case 'search':
    			return _('Search results');
    		case 'contact':
    		    $this->back_url = 'index.php?mod=users&C_ID=' . Env::Get('id');
    		    return _('Edit contact');
    		    
    		default:
    			return false;
    	}
    }	
	
    public function getFolders() 
    {       
        $folders = Contact::getFolders();
        if (!$folders) {
            throw new UserException(_("Not sufficient access rights."));
        }
        $result = array();
        foreach ($folders as $folder) {
    		$result[] = array(
    			'key' => $folder['ID'],
    			'value' => $folder['NAME'],
    		    'disabled' => $folder['RIGHTS'] < Rights::RIGHT_WRITE,
    		    'offset' => $folder['OFFSET']
    		);
    	}	
    	return $result;
    }	
	
    public function prepareData()
    {	
		$contact_type = new ContactType($this->type_id);
		$this->type = $type = $contact_type->getType();
		$js = array();
		
		$photo_field = false;
		$main_fields = $contact_type->getMainFields();
		$main_section = ContactType::getMainSection();
        $type['fields'] = array_values($type['fields']);				
		foreach($type['fields'] as $x => &$group) {
			$js_f = array();
			if ($group['fields']) {
				foreach ($group['fields'] as $f) {
					 $field_info = array(
						$f['id'],
						$f['name'],
						$f['type'],
						$f['dbname'] == 'C_EMAILADDRESS' ? array() : "",
						max($f['required'], in_array($f['id'], $main_fields) ? 3 : 0)
					); 
					
					if (!$photo_field && $group['id'] == $main_section && $f["type"] == "IMAGE") {
	                    $photo_field = $f["id"];
					} 
					if ($f["options"]) {
						$field_info[] = $f["options"];
					}
					$js_f[] = $field_info;
				}
			}
			if ($group['id'] == $main_section) {			
				$main = array();
				$other = array();
				foreach ($js_f as $field_info) {
					if (in_array($field_info[0], $main_fields)) {
						$main[] = $field_info;
					} else {
						$other[] = $field_info;
					}
				}
				$js_f = array_merge($main, $other);
			}
			$js[] = array($group['id'] != $main_section ? "group".$x : "CONTACT", $js_f);
		}

		if (Env::Get('folder')) {
			$last_folder = Env::Get('folder');
		} else {
			$last_folder = User::getSetting("LASTFOLDER", 'CM');
			if (!$last_folder || User::hasAccess('CM', Rights::FOLDERS, $last_folder) < 3) {
			    $last_folder = "PRIVATE".User::getContactId();
			}
		}
		$js[] = array('FOLDER', array(
    		        array(
    		            'CF_ID',
    		            _('Folder'),
    		            'MENU',
    		            $last_folder,
    		            2,
    		            $this->getFolders()
			        )
	            )
	    );
	    
	    $types = ContactType::getTypeNames(null);
	    $add_type_links = array();
	    $url = Env::Server('REQUEST_URI');
	    foreach ($types as $id => $title) {
	        if ($id != $this->type_id) {
		        $add_type_links[] = array(
		            'id' => $id,
		            'url' => str_replace('type='.$this->type_id, 'type='.$id, $url), 
		            'title' => _('Add a new '.mb_strtolower($title))
		        );
	        }
	    }
	    
	    $this->smarty->assign('add_type_links', $add_type_links);
	    
	    $this->smarty->assign('back_title', $this->getBackTitle());
	    $this->smarty->assign('back_url', $this->back_url);
		
	    $this->smarty->assign('admin', User::hasAccess('CM', 'FUNCTIONS', 'ADMIN'));
		$this->smarty->assign('js', json_encode($js));
		$this->smarty->assign('fields', $type['fields']);
		$this->smarty->assign("photo_field", $photo_field);
		
		$this->smarty->assign("typeId", Env::Get('type', Env::TYPE_STRING));
		$this->smarty->assign('add_type_name', _s('Add a new '.mb_strtolower($this->type['name'])));
		
		$this->smarty->assign('main_fields', json_encode($main_fields));
		$this->smarty->assign('first_field', $main_fields[0]);
		$this->smarty->assign('super_main_fields', json_encode($contact_type->getMainFields(0)));
				
		$date_format = mb_strtolower(Wbs::getDbkeyObj()->getDateFormat());
		$this->smarty->assign("dateFormat", mb_substr($date_format, 0, -2));
		
		$countries = Wbs::getCountries();
		$this->smarty->assign('countries', json_encode($countries));		
    }
	 
}

?>