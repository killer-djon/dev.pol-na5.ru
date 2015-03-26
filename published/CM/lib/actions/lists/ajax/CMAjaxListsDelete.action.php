<?php

class CMAjaxListsDeleteAction extends UGAjaxAction
{
	
	protected $list_id;
	public function __construct()
	{
		parent::__construct();
		$this->list_id = Env::Post('id', Env::TYPE_INT);
		if ($this->list_id) {
			$this->delete();
		}
	}
	
	public function delete()
	{
		$lists_model = new ListsModel();
		$lists_model->delete($this->list_id);
	}
}

?>