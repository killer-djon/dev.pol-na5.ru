<?php

class CMAjaxListsAddAction extends UGAjaxAction 
{

	public function __construct()
	{
		if (Env::Post('add')) {
			$this->create();
		}
	}
	
	public function create()
	{
		$name = Env::Post('name');
		$contacts = Env::Post('contacts', Env::TYPE_ARRAY_INT, array());
		$lists_model = new ListsModel();
		$list_id = $lists_model->add($name);
		if (Env::Post('share')) {
			$lists_model->share($list_id);
		}
		User::addMetric('ADDSTATLIST');
		$contact_list_model = new ContactListModel();
		$contact_list_model->add($list_id, $contacts);
		$this->response['list'] = array(
			'id' => $list_id,
			'name' => $name
		);
	}
	
	public function prepareData()
	{
		
	}
}

?>