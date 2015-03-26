<?php

class CMConstructMoveFieldAction extends UGViewAction
{
	protected $field_id;
	protected $field_info = array();
	public function __construct()
	{
		parent::__construct();
		$this->title = _("Move field");
		$this->field_id = Env::Get('field_id', Env::TYPE_INT, 0);
		$this->field_info = ContactType::getField($this->field_id);
        $this->type_id = Env::Get('type_id', Env::TYPE_INT, 1);
    	$this->title .= " &quot;".ContactType::getName($this->field_info['name'], User::getLang())."&quot;";        		
	}
    
    public function prepareData()
    {
        
        $this->smarty->assign('field', $this->field_info);

        $contact_type_model = new ContactTypeModel();
        $after_id = $contact_type_model->getPreviousField($this->field_info['sorting'], $this->field_info['section']);
        $this->smarty->assign('after_id', $after_id);
        
        $this->smarty->assign('main_section', ContactType::getMainSection());

		$contact_type = new ContactType($this->type_id);
        $type = $contact_type->getType(User::getLang(), true);
        $this->smarty->assign('fields', $type['fields']);      

   		$this->smarty->assign('type_id', Env::Get('type_id', Env::TYPE_INT, 1));
    }
}