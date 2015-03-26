<?php

class CMAjaxContactsAddtolistAction extends UGAjaxAction
{
	
	public function __construct()
	{
		parent::__construct();

		$list_id = Env::Post('listId', Env::TYPE_INT, 0);
		$lists_model = new ListsModel();
		$list_info = array();
		if ($list_id) {
		    $list_info = $lists_model->get($list_id);
		    if ($list_info) {
		        $list_info = array(
		            'id' => $list_info['CL_ID'],
		        	'name' => htmlspecialchars($list_info['CL_NAME'])
		        );
		    }
		}
		// Add to new list
		if (!$list_id || !$list_info) {
		    $name = Env::Post('listName', Env::TYPE_STRING_TRIM, '');
		    if (!$name) $name = _s('New list');
		    $list_id = $lists_model->add($name);
		    $list_info = array(
		        'id' => $list_id,
		        'name' => htmlspecialchars($name)
		    );
		    $this->response['add'] = 1;
		}
		
		$contacts = (array)Env::Post('contacts', Env::TYPE_STRING);

		$this->response['list'] = $list_info;

		$clist_model = new ContactListModel();
		$clist_model->add($list_id, $contacts);
		
	}
	
}

?> 