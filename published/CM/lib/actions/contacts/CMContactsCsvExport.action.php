<?php
 
class CMContactsCsvExportAction extends UGViewAction 
{
	
	protected $fields= array();
	protected $file = false;
	protected $users = array();
	
    public function __construct()
    {
        ini_set('max_execution_time', 3600);
        parent::__construct();
        $this->contacts = Env::Post('contacts');
        if ($this->contacts) {
        	$this->contacts = explode(',', $this->contacts);
        } else {
        	$this->contacts = array();
        }
        if (Env::Post('export')) {
        	$this->export();
        }
    }

    /**
     *	@todo: Fix for very match users (split data) 
     */
    public function export()
    {
		// Get fields
		$fields = Env::Post('fields');
		$dbfields = $this->getDbFields(false);
		$exportfields = array();
		foreach ($fields as $field_id) {
		    $exportfields[$field_id] = $dbfields[$field_id];
		}
		$csv = new CSV(false, false, CSV::$delimiters[Env::Post('delimiter')][0], $exportfields);
		// Export
		$contacts = $this->getContacts();
		$csv->export($contacts);
    }
    
    public function getContacts()
    {
    	$from = Env::Post('from', Env::TYPE_INT);
    	switch ($from) {
    		case 1: 
                $contacts_model = new ContactsModel();
    			return $contacts_model->getByIds($this->contacts, false, true);
    		case 2:
    			switch (Env::Get('mode')) {
    			    case 'folders':
    			        $contacts_model = new ContactsModel();
    			        $folder_id = Env::Post('current');
    			        
    			        return $contacts_model->exportByFolder($folder_id, User::getContactId());
    			    case 'search': 
            		    $last_search = User::getSetting('LASTSEARCH', 'CM');
            		    if ($last_search) {
            		        $last_search = json_decode($last_search, true);
                    		$search_type = $last_search['type'];
                            $search_info = $last_search['data'];

                            switch ($search_type) {
                                case 'simple': 
                                    $contacts_model = new ContactsModel();
                                    return $contacts_model->getByName($search_info);
                                case 'advanced': 
                                case 'smart':
                                    $result = Contact::searchByFields($search_info, false, false, false, false, false, $search_type);                      
                                    return $result['users'];    
                            }
            		    }
            			break;
    			    case 'analytics':
		    	        $field = Env::Post('query');
		    	        $value = Env::Post('value');
		    	        $contacts_model = new ContactsModel();
		                
		                if ($field == 'C_EMAILADDRESS' && !$value) {
		                    $query = "C_EMAILADDRESS != ''";
		                } elseif (!strlen($value)) {
		                    if ($field == 'C_CREATECID') {
		                        $query = $field." IS NULL";
		                    } else {
		                        $query = $field." IS NOT NULL AND ".$field." != ''";
		                    }
		                } else {
		                    $query = $field." = '".$contacts_model->escape($value)."'";
		                }
		    	        return $contacts_model->getBySQL($query, 'C_FULLNAME', false);    			    	
    			    case 'lists': 
    			    	$list_id = Env::Post('current');
    			    	$result = Contact::getByList($list_id, 'C_FULLNAME ASC');
    			    	return $result['users']; 
    			}                     		    
    		default:
    			return array();
    	}
    }
    

    public function getDbFields($json = false)
    {

    	$dbfields =  array('C_FULLNAME' => _('Full name')) + ContactType::getFieldsNames(User::getLang(), $json, true, true);
    	if (!$json) {
    		return $dbfields;
    	}
    	$fields = array();
    	foreach ($dbfields as $field_id => $field_name) {
    		$fields[] = array($field_id, $field_name);
    	}
    	return $fields;   	
    }
    
    
    public function prepareData()
    {
    	$this->smarty->assign('group', Env::Get('group_id', Env::TYPE_INT, 0));
    	$this->smarty->assign('users', implode(",", $this->users));
    	$this->smarty->assign('users_count', count($this->users));
    	$this->smarty->assign('groups', Groups::getGroups());
    	$this->smarty->assign('delimiters', CSV::getDelimiters());
    	$this->smarty->assign('groups', Groups::getGroups());
    	$this->smarty->assign('fields', json_encode($this->getDbFields(true)));
    	$this->smarty->assign('mode', Env::Get('mode', Env::TYPE_STRING));
    	$this->smarty->assign('params', Env::Post());
    }
    
}

?>
