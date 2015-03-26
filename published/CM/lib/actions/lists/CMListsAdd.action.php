<?php 

class CMListsAddAction extends UGViewAction
{
	public function __construct()
	{
		parent::__construct();
		$this->template = 'ListsEdit.html';
	}
	
	public function prepareData()
	{
		$this->smarty->assign('folders', Contact::getFolders());   
	}
}

?>