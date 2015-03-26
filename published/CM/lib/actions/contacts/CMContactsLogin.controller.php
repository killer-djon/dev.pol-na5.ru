<?php 

class CMContactsLoginController extends UGController
{
	public function exec() 
	{
        $this->title = _("Create user account");
		$this->layout = 'Popup';
		$this->actions[] = new CMContactsLoginAction();	
	}
}
?>