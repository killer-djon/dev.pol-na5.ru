<?php

class CMConstructDeleteSectionAction extends UGViewAction 
{

    protected $type_id;
    protected $section_id;
    protected $section_info;
    
    public function __construct()
    {
        parent::__construct();
        $this->title = _("Delete section");
        $this->type_id = Env::Get('type_id', Env::TYPE_INT, 0);   
		$this->section_id = Env::Get('section_id', Env::TYPE_INT, 0);
		$this->section_info = ContactType::getField($this->section_id);
		$this->title .= " &quot;".ContactType::getName($this->section_info['name'], User::getLang())."&quot;";
    }
    
    public function prepareData()
    {
        $contact_type_model = new ContactTypeModel();
	    if ($contact_type_model->getFields($this->section_id)) {
            $this->smarty->assign('not_empty', true);	                
	    } else {
            $this->smarty->assign('section_id', $this->section_id);
            $this->smarty->assign('type_id', $this->type_id);
	    }
    }
    
}
?>