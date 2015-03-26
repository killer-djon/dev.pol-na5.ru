<?php
class CMConstructAddSectionAction extends UGViewAction
{
	public function __construct()
	{
		parent::__construct();
		$this->title = _("Add section");
		$this->template = 'ConstructEditSection.html';
	}
	
	public function prepareData()
	{
		$langs = Wbs::getDbkeyObj()->getLanguages();
		
		$user_lang_title = $langs[User::getLang()];
		
		$sections = ContactType::getAllFields(User::getLang(), ContactType::TYPE_SECTION);
		
		$after_id = end($sections);
		$after_id = $after_id['id'];
		
		$this->smarty->assign('after_id', $after_id);
		$this->smarty->assign('langs', $langs);
		$this->smarty->assign('user_lang', User::getLang());
		$this->smarty->assign('user_lang_title', $user_lang_title);
		$this->smarty->assign('sections', $sections);
		$this->smarty->assign('type_id', Env::Get('type_id', Env::TYPE_INT, 1));
		$this->smarty->assign('act', 'add');		
	}
}
?>