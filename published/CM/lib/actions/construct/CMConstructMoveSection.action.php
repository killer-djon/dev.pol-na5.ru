<?php
class CMConstructMoveSectionAction extends UGViewAction
{
    protected $section_id;
    protected $section_info = array();
    
	public function __construct()
	{
		parent::__construct();
		$this->title = _('Move section');
	    $this->section_id = Env::Get('section_id', Env::TYPE_INT, 0);
	    $this->section_info = ContactType::getField($this->section_id);
        $this->title .= " &quot;".ContactType::getName($this->section_info['name'], User::getLang())."&quot;";	    
	}
	
	public function prepareData()
	{
		$this->smarty->assign('section', $this->section_info);
		
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
		$this->smarty->assign('act', 'move');		
	}
}
?>