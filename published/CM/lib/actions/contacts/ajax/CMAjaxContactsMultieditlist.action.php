<?php

class CMAjaxContactsMultieditlistAction extends UGAjaxAction 
{
	protected $ids;
	protected $types = array();
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getSelectedContacts()
	{
		$this->ids  = Env::Get('contacts');
		$this->ids  = explode(",", $this->ids);
		
    	try {
			$contacts_model = new ContactsModel();
			$contacts = $contacts_model->getByIds($this->ids, true, false);
			$delete_users = User::hasAccess("UG");
			Contact::useStore(false);
			foreach ($contacts as &$contact_info){
				$contact_info = Contact::getInfo($contact_info['C_ID'], $contact_info, false, 2);
	        	if (is_array($contact_info["C_EMAILADDRESS"])) {
	        	    $contact_info["C_EMAILADDRESS"] = array_shift($contact_info["C_EMAILADDRESS"]);
	        	} 
	        	foreach ($contact_info as $dbname => $v) {
	        		if (!$this->fieldExists($contact_info['CT_ID'], $dbname)) {
	        			unset($contact_info[$dbname]);
	        		}
	        	}
				if ($contact_info['C_ID'] == User::getContactId() || ($contact_info['U_ID'] && !$delete_users)) {
					$contact_info['right_delete'] = 0;
				} else {
					$contact_info['right_delete'] = 1;
				}
				
				$contact_info['right'] = Contact::accessFolder($contact_info['CF_ID']) >= 3 || User::isAdmin('CM');
			}
			Contact::useStore(true);
			$this->response['contacts'] = $contacts;
    	} catch (MySQLException $e) {
	    	$this->response = array("isError" => true, "errorStr" => _('Database error'));
	    }	
	}
	
	protected function fieldExists($type_id, $dbname)
	{
		if (in_array($dbname, array('C_ID', 'CT_ID', 'U_ID', 'CF_ID'))) {
			return true;
		}
		if (!isset($this->types[$type_id])) {
			$contact_type = new ContactType($type_id);
			$all_fields = ContactType::getDbFields();
			$fields = $contact_type->getTypeDbFields();
			$this->types[$type_id] = array_intersect($all_fields, $fields);
		}
		return in_array($dbname, $this->types[$type_id]);
	}
	
	public function prepareData()
	{
			$this->getSelectedContacts();

	}
}

?> 