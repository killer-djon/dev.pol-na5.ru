<?php

class CMAjaxListsRenameAction extends UGAjaxAction
{

	protected $list_id;
	
	public function __construct()
	{
		parent::__construct();
		$this->list_id = Env::Post('id', Env::TYPE_INT);
		if ($this->list_id) {
			$this->rename();
		} else {
			throw new Exception('List is not found!');
		}
	}
	
	public function rename()
	{
		$name = Env::Post('newName');
		$lists_model = new ListsModel();
		$lists_model->save($this->list_id, $name);
		$this->response = $name;
	}
}


?>