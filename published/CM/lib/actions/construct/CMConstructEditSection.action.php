<?php
class CMConstructEditSectionAction extends UGViewAction
{
    protected $section_id;
    protected $section_info = array();
    
	public function __construct()
	{
		parent::__construct();
		$this->title = _('Edit section');
	    $this->section_id = Env::Get('section_id', Env::TYPE_INT, 0);
	    $this->section_info = ContactType::getField($this->section_id);
        $this->title .= " &quot;".ContactType::getName($this->section_info['name'], User::getLang())."&quot;";	    
	}
	
	public function prepareData()
	{
		$langs = Wbs::getDbkeyObj()->getLanguages();

		$name = $this->section_info['name'];
		$this->section_info['name'] = array();
		foreach ($langs as $lang => $lang_title) {
			$this->section_info['name'][$lang] = ContactType::getName($name, $lang, false); 
		}
    
		$this->smarty->assign('section', $this->section_info);
		
		$user_lang_title = $langs[User::getLang()];
		
		$this->smarty->assign('langs', $langs);
		$this->smarty->assign('user_lang', User::getLang());
		$this->smarty->assign('user_lang_title', $user_lang_title);
		
		$sections = ContactType::getAllFields(User::getLang(), ContactType::TYPE_SECTION);
		$after_id = 0;
		foreach ($sections as $s) {
		    if ($s['id'] == $this->section_id) {
		        break;
		    }
		    $after_id = $s['id'];
		}
		$this->smarty->assign('after_id', $after_id);
		$this->smarty->assign('sections', $sections);
		$this->smarty->assign('type_id', Env::Get('type_id', Env::TYPE_INT, 1));
		$this->smarty->assign('act', 'edit');		
	}
}
?>