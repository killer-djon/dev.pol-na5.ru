<?php

class CMConstructFormatAction extends UGViewAction
{
	protected $type_id;
	
	public function __construct()
	{
		parent::__construct();
		$this->title = _("Change full name format");
		$this->type_id = Env::Get('type_id', Env::TYPE_INT, 1);
	}

	public function prepareData()
	{
		$first = ContactType::getFieldByDbName('C_FIRSTNAME', User::getLang());
		$middle = ContactType::getFieldByDbName('C_MIDDLENAME', User::getLang());
		$last = ContactType::getFieldByDbName('C_LASTNAME', User::getLang());
		
		$formats = array(
			1 => str_replace(array(" name", " Name"), "", $first['name']." ".$middle['name']." ".$last['name']),
			2 => str_replace(array(" name", " Name"), "", $last['name']." ".$first['name']." ".$middle['name']),
			3 => str_replace(array(" name", " Name"), "", $last['name'].", ".$first['name']." ".$middle['name']),
		);
		
		$current = array(
			"!".$first['id']."! !".$middle['id']."! !".$last['id']."!" => 1,
			"!".$last['id']."! !".$first['id']."! !".$middle['id']."!" => 2,
			"!".$last['id']."!, !".$first['id']."! !".$middle['id']."!" => 3,
		);
		
		$contact_type = new ContactType($this->type_id);
		$type =$contact_type->getType();
		$current = isset($current[$type['fname_format'][0]]) ? $current[$type['fname_format'][0]] : 1;
		
		$this->smarty->assign('current', $current);
		$this->smarty->assign('type_id', $this->type_id);
		$this->smarty->assign('formats', $formats);
	}
}

?>