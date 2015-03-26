<?php

class CMAjaxListsEditAction extends UGAjaxAction 
{
	protected $list_id;
	
	public function __construct()
	{
		if (Env::Post('edit')) {
			$this->list_id = Env::Post('id', Env::TYPE_INT, 0);
			$this->save();
		}
	}
	
	public function save()
	{
		$name = Env::Post('name');
		$contacts = Env::Post('contacts', Env::TYPE_ARRAY_INT, array());
		$lists_model = new ListsModel();
		
			$lists_model->save($this->list_id, $name);
		if (User::isAdmin('CM')) {
			$lists_model->share($this->list_id, Env::Post('share', Env::TYPE_INT, 0));
		}		
		$contact_list_model = new ContactListModel();
		$contacts_old = $contact_list_model->getContactIds($this->list_id);
		
		$delete_contacts = array_diff($contacts_old, $contacts);
		$contact_list_model->delete($this->list_id, $delete_contacts);
		
		$add_contacts = array_diff($contacts, $contacts_old);
		$contact_list_model->add($this->list_id, $add_contacts);
		$this->response['list'] = array(
			'id' => $this->list_id,
			'name' => $name
		);
	}
	
	public function prepareData()
	{
		
	}
}

?>