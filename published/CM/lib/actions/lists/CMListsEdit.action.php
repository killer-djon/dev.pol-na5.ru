<?php 

class CMListsEditAction extends UGViewAction
{
	protected $list_id;
	protected $list_info = array();
	
	public function __construct()
	{
		parent::__construct();
		$this->list_id = Env::Get('id', Env::TYPE_INT, 0);
		$lists_model = new ListsModel();
		$this->list_info = $lists_model->get($this->list_id);
	}
	
	public function prepareData()
	{
		$this->smarty->assign('folders', Contact::getFolders());
		// Get all contacts
		$contact_list_model = new ContactListModel();
		$contacts = $contact_list_model->getContacts($this->list_id);
		$result = array();
		Contact::useStore(false);
		foreach ($contacts as $c) {
			$result[] = array(
				'id' => $c['C_ID'],
				'name' => Contact::getName($c['C_ID'], false, $c)
			);
		}
		Contact::useStore(false);
		// Set flag    
		$this->smarty->assign('contacts', $result);
		$this->smarty->assign('edit', 1);
		$this->smarty->assign('list_id', $this->list_id);
		$this->smarty->assign('list', $this->list_info);
		$this->smarty->assign('share', User::isAdmin('CM') && $this->list_info['CL_C_ID'] == User::getContactId());
	}
}

?>