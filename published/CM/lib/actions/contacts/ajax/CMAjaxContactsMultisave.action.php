<?php

/**
 * Saving of the view settings of users and contacts by ajax
 * 
 * @copyright WebAsyst © 2008-2009
 * @author WebAsyst Team
 * @version SVN: $Id$
 */
class CMAjaxContactsMultisaveAction extends UGAjaxAction 
{
	protected $contacts_list = array();
	protected $delete_list = array();
	protected $saved_count = 0;
	protected $deleted_count = 0;
	
	public function __construct()
	{
		parent::__construct();

		$this->contacts_list = Env::Post('CONTACTS');
		$this->delete_list = Env::Post('DELETE');
		if (empty($this->contacts_list)) $this->contacts_list = array();
		if (empty($this->delete_list)) $this->delete_list = array();

		$this->errors = array();
		if (!empty($this->delete_list)){
			$this->deleteContacts();
		}
		$this->saveContacts();
		$this->response['saved'] = $this->saved_count - $this->deleted_count;
		$this->response['deleted'] = $this->deleted_count;
	}
	
	protected function deleteContacts()
	{
		$this->errors = array();
		$contacts_model = new ContactsModel();
		$users_model = new UsersModel();
		$contacts = $contacts_model->getByIds($this->delete_list); 
		foreach ($contacts as $contact) {
			$errors = array();
			try{
				if ($contact['U_ID']) {
					if (User::hasAccess('UG')) {
						$users_model->delete($contact['U_ID']);
					} else {
						throw new Exception(_('Not sufficient access rights.'));
					}
				}
				$contacts_model->delete($contact['C_ID']);
				$this->deleted_count++;
			} catch (Exception $e) {
				$this->errors[$contact['C_ID']][0]['id'] = $contact['C_ID'];
				$this->errors[$contact['C_ID']][0]['type'] = 'delete';
				$this->errors[$contact['C_ID']][0]['text'] = $e->getMessage();
			}
		}
	}
	
	protected function saveContacts()
	{
		foreach ($this->contacts_list as $contact_id => $contact_info) {
			if (!in_array($contact_id, $this->delete_list)){
				$errors = array();
				if (Contact::save($contact_id, $contact_info, $errors, true)) {
					$this->saved_count++;
				}
				if ($errors) {
					$this->errors[$contact_id] = $errors;
				}
			}
		}
	}
	
}

?>