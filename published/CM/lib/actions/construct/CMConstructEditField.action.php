<?php

class CMConstructEditFieldAction extends UGViewAction
{
	protected $field_id;
	protected $field_info = array();
	public function __construct()
	{
		parent::__construct();
		$this->title = _("Edit field");
		$this->field_id = Env::Get('field_id', Env::TYPE_INT, 0);
		$this->field_info = ContactType::getField($this->field_id);
        $this->type_id = Env::Get('type_id', Env::TYPE_INT, 1);
    	$this->title .= " &quot;".ContactType::getName($this->field_info['name'], User::getLang())."&quot;";        		
	}
	
	public function prepareData()
	{	
		$langs = Wbs::getDbkeyObj()->getLanguages();
		$name = $this->field_info['name'];

		$this->field_info['name'] = array();
		foreach ($langs as $lang => $lang_title) {
			$this->field_info['name'][$lang] = ContactType::getName($name, $lang, false);
		}
		$user_lang_title = $langs[User::getLang()];
		$this->field_info['dbname'] = substr($this->field_info['dbname'], 2); 
		$this->smarty->assign('field', $this->field_info);
	
		$this->smarty->assign('langs', $langs);

        $this->smarty->assign('main_section', ContactType::getMainSection());	
	
		$this->smarty->assign('type_id', Env::Get('type_id', Env::TYPE_INT, 1));
		$this->smarty->assign('act', 'edit');		
		
		$this->smarty->assign('user_lang', User::getLang());
		$this->smarty->assign('user_lang_title', $user_lang_title);
		
		$types = ContactType::getDbTypeNames();
		unset($types['EMAIL']);
		$this->smarty->assign('dbtypes', $types);
	}	
}
?>