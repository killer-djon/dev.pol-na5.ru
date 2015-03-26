<?php
class CMConstructAddFieldAction extends UGViewAction
{
    protected $type_id;
	public function __construct()
	{
		parent::__construct();
		$this->title = _('Add field');
		$this->template = 'ConstructEditField.html';
		$this->type_id = Env::Get('type_id', Env::TYPE_INT, 1);
	}
	
	public function prepareData()
	{
		$langs = Wbs::getDbkeyObj()->getLanguages();
		
		$user_lang_title = $langs[User::getLang()];
		
		$contact_type = new ContactType($this->type_id);
        $type = $contact_type->getType(User::getLang(), true);
        	
		$this->smarty->assign('langs', $langs);
		$this->smarty->assign('user_lang', User::getLang());
		$this->smarty->assign('user_lang_title', $user_lang_title);
		$this->smarty->assign('main_section', ContactType::getMainSection());

		$this->smarty->assign('fields', $type['fields']);
		$this->smarty->assign('type_id', $this->type_id);
		$this->smarty->assign('act', 'add');	

		$types = ContactType::getDbTypeNames();
		unset($types['EMAIL']);
		$this->smarty->assign('dbtypes', $types);
	}
}
?>