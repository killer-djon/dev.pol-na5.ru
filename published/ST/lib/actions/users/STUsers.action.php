<?php

class STUsersAction extends Action
{
	
	public function prepare()
	{ 
		$this->view->assign('template', User::getSetting('assign_template', 'ST', ''));
	}
}

?>