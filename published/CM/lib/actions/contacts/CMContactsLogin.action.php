<?php

class CMContactsLoginAction extends UGViewAction
{
	
	protected $contact_id;
	protected $contact_info;
	protected $error = "";
	
	public function __construct()
	{
		parent::__construct();
		User::checkLimits();
		$this->contact_id = Env::Get('id', Env::TYPE_BASE64_INT, 0);
		$this->contact_info = Contact::getInfo($this->contact_id);
		if ($this->contact_info['U_ID']) {
			$this->error = _("This contact already has a user account.");
		}
	}
	
	public function prepareData()
	{
		$this->smarty->assign('error', $this->error);
		$this->smarty->assign('contact_id', base64_encode($this->contact_id)); 	
	}
}

?>